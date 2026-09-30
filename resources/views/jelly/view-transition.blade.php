{{--
    Gooey page transitions. The new page is revealed by a wobbly blob of goo that
    bursts out from wherever you clicked, with a rim of orange goo and an ink outline.

    Inline in the <head> because `pagereveal` fires before the first render, which
    is too early for the deferred app bundle.
--}}
<script>
    (() => {
        if (!('onpagereveal' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const KEY = 'goo-origin';

        // Remember where the click happened, so the next page can burst out from there
        addEventListener('pointerdown', (e) => {
            sessionStorage.setItem(KEY, JSON.stringify({ x: e.clientX, y: e.clientY, t: Date.now() }));
        }, { capture: true });

        // Left mid-transition and came back via the back/forward cache: don't leave the goo layers showing
        addEventListener('pageshow', (e) => {
            if (e.persisted) document.documentElement.classList.remove('goo-vt-on');
        });

        addEventListener('pagereveal', (e) => {
            const transition = e.viewTransition;
            if (!transition) return;

            const w = innerWidth, h = innerHeight;
            let origin;
            try {
                origin = JSON.parse(sessionStorage.getItem(KEY));
            } catch {}
            if (!origin || Date.now() - origin.t > 10000) origin = { x: w / 2, y: h * 0.6 };
            sessionStorage.removeItem(KEY);
            const { x, y } = origin;

            const root = document.documentElement;
            root.classList.add('goo-vt-on');
            transition.finished.finally(() => {
                root.classList.remove('goo-vt-on');
                window.jellyGoo?.residue();
            });

            const rand = (min, max) => min + Math.random() * (max - min);
            const ease = (t) => (t < 0.5 ? 4 * t ** 3 : 1 - (-2 * t + 2) ** 3 / 2);
            const easeOutBack = (t) => 1 + 2.7 * (t - 1) ** 3 + 1.7 * (t - 1) ** 2;

            // A lumpy circle: every point wobbles in and out on its own rhythm
            const points = Array.from({ length: 11 }, () => ({
                jitter: rand(-0.15, 0.15),
                amp: rand(0.06, 0.16),
                freq: rand(6, 14),
                phase: rand(0, Math.PI * 2),
            }));

            function blob(cx, cy, r, t, extra = 0) {
                const n = points.length;
                const p = points.map((pt, i) => {
                    const angle = (i / n) * Math.PI * 2 + pt.jitter;
                    const radius = Math.max(0.5, r * (1 + pt.amp * Math.sin(t * pt.freq + pt.phase)) + extra);
                    return [cx + Math.cos(angle) * radius, cy + Math.sin(angle) * radius];
                });
                // Smooth closed curve through the points (Catmull-Rom as cubic Béziers)
                let d = `M${p[0][0].toFixed(1)} ${p[0][1].toFixed(1)}`;
                for (let i = 0; i < n; i++) {
                    const p0 = p[(i - 1 + n) % n], p1 = p[i], p2 = p[(i + 1) % n], p3 = p[(i + 2) % n];
                    const c1 = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6];
                    const c2 = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
                    d += `C${c1[0].toFixed(1)} ${c1[1].toFixed(1)} ${c2[0].toFixed(1)} ${c2[1].toFixed(1)} ${p2[0].toFixed(1)} ${p2[1].toFixed(1)}`;
                }
                return d + 'Z';
            }

            // Big enough to cover the far corner, even with the lumps pulled in
            const far = Math.hypot(Math.max(x, w - x), Math.max(y, h - y));
            const max = far / 0.8 + 60;
            const radius = (t) => (t < 0.22 ? 70 * easeOutBack(t / 0.22) : 70 + (max - 70) * ease((t - 0.22) / 0.78));

            const frames = 36;
            const reveal = [], orange = [], ink = [];
            for (let i = 0; i <= frames; i++) {
                const t = i / frames;
                const r = radius(t);
                // The rim of goo gets chunkier as the blob grows
                const rim = Math.min(44, 16 + r * 0.03);
                reveal.push({ clipPath: `path("${blob(x, y, r, t)}")` });
                // Rings: the orange rim of goo around the edge, and an ink outline with a hard shadow
                orange.push({ clipPath: `path(evenodd, "${blob(x, y, r, t, rim)}${blob(x, y, r, t)}")` });
                ink.push({ clipPath: `path(evenodd, "${blob(x + 4, y + 5, r, t, rim + 4)}${blob(x, y, r, t, -3)}")` });
            }

            const duration = 1250;
            transition.ready.then(() => {
                const options = (pseudoElement) => ({ duration, easing: 'linear', fill: 'both', pseudoElement });
                root.animate(reveal, options('::view-transition-new(root)'));
                root.animate(orange, options('::view-transition-new(goo-orange)'));
                root.animate(ink, options('::view-transition-new(goo-ink)'));

                // The old page sinks back as the goo takes over
                root.animate([
                    { transform: 'scale(1)', filter: 'brightness(1)', transformOrigin: `${x}px ${y}px` },
                    { transform: 'scale(0.92)', filter: 'brightness(0.82) saturate(0.85)', transformOrigin: `${x}px ${y}px` },
                ], { duration, easing: 'cubic-bezier(0.5, 0, 0.75, 0)', fill: 'both', pseudoElement: '::view-transition-old(root)' });

                // ...and the new one lands with a little jelly wobble
                root.animate([
                    { transform: 'scale(1.06)', transformOrigin: `${x}px ${y}px` },
                    { transform: 'scale(0.985)', transformOrigin: `${x}px ${y}px`, offset: 0.8 },
                    { transform: 'scale(1.004)', transformOrigin: `${x}px ${y}px`, offset: 0.92 },
                    { transform: 'scale(1)', transformOrigin: `${x}px ${y}px` },
                ], { duration: duration + 250, easing: 'ease-out', fill: 'both', pseudoElement: '::view-transition-new(root)' });
            });
        });
    })();
</script>
