// Click-and-drag horizontal scrolling for mouse users, with a little momentum.

export default function dragScroll(selector = '[data-drag-scroll]') {
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

    for (const track of document.querySelectorAll(selector)) {
        let startX = 0, startScroll = 0, lastX = 0, lastT = 0, velocity = 0;
        let pointerId = null, dragged = false, momentum = 0;

        const scrollable = () => track.scrollWidth > track.clientWidth + 1;
        const refresh = () => track.classList.toggle('is-draggable', scrollable());
        refresh();
        new ResizeObserver(refresh).observe(track);

        track.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'mouse' || e.button !== 0 || !scrollable()) return;
            cancelAnimationFrame(momentum);
            pointerId = e.pointerId;
            startX = lastX = e.clientX;
            startScroll = track.scrollLeft;
            lastT = performance.now();
            velocity = 0;
            dragged = false;
        });

        track.addEventListener('pointermove', (e) => {
            if (e.pointerId !== pointerId) return;

            if (!dragged && Math.abs(e.clientX - startX) > 5) {
                dragged = true;
                track.setPointerCapture(pointerId);
                track.classList.add('is-dragging');
            }
            if (!dragged) return;

            const now = performance.now();
            velocity = (lastX - e.clientX) / Math.max(1, now - lastT);
            lastX = e.clientX;
            lastT = now;
            track.scrollLeft = startScroll - (e.clientX - startX);
        });

        const release = (e) => {
            if (e.pointerId !== pointerId) return;
            pointerId = null;
            if (!dragged) return;

            const settle = () => track.classList.remove('is-dragging');

            if (reduceMotion || Math.abs(velocity) < 0.05) return settle();

            // Coast to a stop, then let scroll-snap take over
            let v = velocity * 16, prev = performance.now();
            const coast = (now) => {
                const dt = now - prev;
                prev = now;
                track.scrollLeft += v * (dt / 16);
                v *= Math.pow(0.92, dt / 16);
                if (Math.abs(v) > 0.4) momentum = requestAnimationFrame(coast);
                else settle();
            };
            momentum = requestAnimationFrame(coast);
        };

        track.addEventListener('pointerup', release);
        track.addEventListener('pointercancel', release);

        // A drag shouldn't count as a click on the card underneath
        track.addEventListener('click', (e) => {
            if (dragged) {
                e.preventDefault();
                e.stopPropagation();
                dragged = false;
            }
        }, true);

        track.addEventListener('dragstart', (e) => e.preventDefault());
    }
}
