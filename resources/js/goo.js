// Interactive goo & jelly: spring physics driven by the pointer.
//
// - Strands: leaving a gooey element slowly pulls a blob of goo out of its edge
//   that sticks to the pointer. Pull too far or yank it and the strand snaps,
//   the stub retracts, and the drop falls and splashes at the bottom of the screen.
// - Screen edges: goo thrown at the top of the screen splots and slowly drips
//   back down; goo that hits the sides dribbles or tumbles down like a wall-walker toy.
// - Globs that touch pull together and merge; scrolling jostles everything.
// - Flicks: moving fast across an element flings droplets off the edge you're heading for.
// - Wobble: elements squash and stretch with pointer velocity, then wobble back.
// - Reach: the hero blob grows an arm of goo towards the pointer.

const NS = 'http://www.w3.org/2000/svg';
const INK = '#2a1810';
const ORANGE = '#ff6a1f';
const GRAVITY = 2000;

const WOBBLE = [
    '.jelly-btn', '.jelly-video', '.jelly-talk-stack', '.jelly-social', '.jelly-nav-links a',
    '.jelly-logo-blob', '.jelly-chip--link', '.jelly-pager-card', '.jelly-sticker',
    '.jelly-author', '.jelly-row', '.jelly-tag', '[data-jelly]',
].join(',');

const GOO = [
    '.jelly-btn', '.jelly-nav-links a', '.jelly-social', '.jelly-logo-blob', '.jelly-chip--link',
    '.jelly-sticker', '.jelly-video', '.jelly-talk', '.jelly-talk-drip', '.jelly-pager-card', '.jelly-tag', '[data-goo]',
].join(',');

const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
const rand = (min, max) => min + Math.random() * (max - min);
const randInt = (min, max) => Math.floor(rand(min, max + 1));
const lerp = (a, b, t) => a + (b - a) * t;

const pointer = { x: -1e4, y: -1e4, vx: 0, vy: 0, t: 0 };
const pointerSpeed = () => Math.hypot(pointer.vx, pointer.vy);

let svg, defs, world, running = false, last = 0, frame = 0, clipId = 0;
let lastScroll = scrollY, scrollDelta = 0, scrollSpeed = 0;
let held = null;
let reach = null;
const splashes = new Set();
const edgeGoo = new Set();
const viewportWidth = () => document.documentElement.clientWidth;
const wobbles = new Map();

export default function goo() {
    if (!document.querySelector('.dir-jelly')) return;
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    createOverlay();

    const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;

    window.addEventListener('pointermove', onPointerMove, { passive: true });
    window.addEventListener('scroll', wake, { passive: true });

    if (finePointer) {
        for (const el of document.querySelectorAll(WOBBLE)) {
            el.addEventListener('pointerenter', () => wobbleState(el).hover = true);
            el.addEventListener('pointerleave', () => onWobbleLeave(el));
        }

        for (const el of document.querySelectorAll(GOO)) {
            el.addEventListener('pointerleave', (e) => onGooLeave(el, e));
            el.addEventListener('pointerenter', () => {
                // Back into the source: the goo gets sucked back in. Into anything else: it snaps.
                if (held?.src === el) held.retract();
                else held?.snap();
            });
            el.addEventListener('pointermove', () => flick(el));
        }

        document.addEventListener('pointerdown', () => held?.snap());
        document.documentElement.addEventListener('pointerleave', () => held?.snap());

        setupReach();
    }

    // Touch has no hover, so a tap makes a little goo sag off the top edge and drip
    document.addEventListener('pointerdown', (e) => {
        if (e.pointerType === 'mouse') return;
        const el = e.target.closest?.(GOO);
        if (!el) return;
        const rect = el.getBoundingClientRect();
        new Splash({ color: colorOf(el), src: el }).pull(e.clientX, rect.top, 'hang', rand(-60, 60), rand(-220, -120));
    });
}

// ---------------------------------------------------------------------------
// Pointer tracking & loop

function onPointerMove(e) {
    const now = performance.now();
    const dt = Math.max(4, now - pointer.t) / 1000;

    if (pointer.t && now - pointer.t < 100) {
        pointer.vx += ((e.clientX - pointer.x) / dt - pointer.vx) * 0.4;
        pointer.vy += ((e.clientY - pointer.y) / dt - pointer.vy) * 0.4;
    }

    pointer.x = e.clientX;
    pointer.y = e.clientY;
    pointer.t = now;
    wake();
}

function wake() {
    if (running) return;
    running = true;
    last = performance.now();
    lastScroll = scrollY;
    requestAnimationFrame(tick);
}

function tick(now) {
    const dt = Math.min(0.033, (now - last) / 1000);
    last = now;
    frame++;

    // Velocity fades once the pointer stops moving
    if (now - pointer.t > 40) {
        pointer.vx *= 0.8;
        pointer.vy *= 0.8;
    }

    world.setAttribute('transform', `translate(${-scrollX} ${-scrollY})`);

    scrollDelta = scrollY - lastScroll;
    lastScroll = scrollY;
    scrollSpeed = lerp(scrollSpeed, dt ? scrollDelta / dt : 0, 0.3);

    for (const splash of splashes) {
        if (!splash.step(dt)) splash.remove();
    }

    mergeDrops(dt);
    mergeEdges();

    for (const [el, state] of wobbles) {
        if (!stepWobble(el, state, dt)) wobbles.delete(el);
    }

    const reaching = reach ? stepReach(dt, now) : false;

    if (splashes.size || wobbles.size || reaching || now - pointer.t < 200) {
        requestAnimationFrame(tick);
    } else {
        running = false;
    }
}

// ---------------------------------------------------------------------------
// Overlay: an SVG above the page where the goo lives, with a goo filter that
// also draws the ink outline and hard offset shadow so it matches the UI.

function createOverlay() {
    svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:60;overflow:visible';
    svg.innerHTML = `
        <defs>
            <filter id="goo-ink" x="-100%" y="-100%" width="300%" height="300%" color-interpolation-filters="sRGB">
                <feGaussianBlur in="SourceGraphic" stdDeviation="5" result="blur"/>
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 24 -10" result="goo"/>
                <feMorphology in="goo" operator="dilate" radius="2.5" result="fat"/>
                <feFlood flood-color="${INK}"/>
                <feComposite in2="fat" operator="in" result="outline"/>
                <feOffset in="outline" dx="3" dy="3" result="shadow"/>
                <feMerge><feMergeNode in="shadow"/><feMergeNode in="outline"/><feMergeNode in="goo"/></feMerge>
            </filter>
            <filter id="goo-flat" x="-100%" y="-100%" width="300%" height="300%" color-interpolation-filters="sRGB">
                <feGaussianBlur in="SourceGraphic" stdDeviation="8" result="blur"/>
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 24 -10"/>
            </filter>
        </defs>`;
    defs = svg.querySelector('defs');
    world = document.createElementNS(NS, 'g');
    svg.appendChild(world);
    document.body.appendChild(svg);
}

function svgEl(name, attrs, parent) {
    const el = document.createElementNS(NS, name);
    for (const key in attrs) el.setAttribute(key, attrs[key]);
    parent?.appendChild(el);
    return el;
}

function setCircle(el, x, y, r) {
    el.setAttribute('cx', x.toFixed(1));
    el.setAttribute('cy', y.toFixed(1));
    el.setAttribute('r', Math.max(0, r).toFixed(2));
}

// ---------------------------------------------------------------------------
// Element geometry. Elements are often mid-transform (CSS hover springs, the
// wobble below), so work out the real transformed rounded rectangle rather
// than using the bounding box — otherwise the goo drifts away from the edge.

function shapeOf(el) {
    const rect = el.getBoundingClientRect();
    const style = getComputedStyle(el);
    let a = 1, b = 0, c = 0, d = 1;

    if (style.transform && style.transform !== 'none') {
        const m = new DOMMatrixReadOnly(style.transform);
        ({ a, b, c, d } = m);
    }

    if (style.scale && style.scale !== 'none') {
        const [sx, sy = sx] = style.scale.split(' ').map(parseFloat);
        a *= sx; c *= sx;
        b *= sy; d *= sy;
    }

    const w = el.offsetWidth, h = el.offsetHeight;
    const radius = style.borderTopLeftRadius.includes('%')
        ? Math.min(w, h) / 2
        : Math.min(parseFloat(style.borderTopLeftRadius) || 0, w / 2, h / 2);

    return {
        // The centre of a transformed box is the centre of its bounding box
        cx: rect.left + rect.width / 2 + scrollX,
        cy: rect.top + rect.height / 2 + scrollY,
        w, h, radius, a, b, c, d,
    };
}

const toPage = (s, x, y) => ({ x: s.cx + s.a * x + s.c * y, y: s.cy + s.b * x + s.d * y });

function toLocal(s, x, y) {
    const det = s.a * s.d - s.b * s.c || 1;
    const px = x - s.cx, py = y - s.cy;
    return { x: (s.d * px - s.c * py) / det, y: (-s.b * px + s.a * py) / det };
}

// Nearest point on the rounded-rectangle edge (local coordinates), and its outward normal
function edgePoint(s, lx, ly) {
    const hw = s.w / 2, hh = s.h / 2, r = s.radius;
    let x = clamp(lx, -hw, hw), y = clamp(ly, -hh, hh);
    let nx = 0, ny = 0;

    if (hw - Math.abs(x) < hh - Math.abs(y)) {
        x = Math.sign(x || 1) * hw;
        nx = Math.sign(x);
    } else {
        y = Math.sign(y || 1) * hh;
        ny = Math.sign(y);
    }

    const kx = clamp(x, -hw + r, hw - r), ky = clamp(y, -hh + r, hh - r);
    if (x !== kx && y !== ky) {
        const len = Math.hypot(x - kx, y - ky) || 1;
        nx = (x - kx) / len;
        ny = (y - ky) / len;
        x = kx + nx * r;
        y = ky + ny * r;
    }

    return { x, y, nx, ny };
}

// Everything except the element's rounded rectangle, in local coordinates
function outsidePath(s) {
    const hw = s.w / 2, hh = s.h / 2, r = s.radius, big = 1e5;
    return `M${-big} ${-big}H${big}V${big}H${-big}Z`
        + `M${-hw + r} ${-hh}H${hw - r}A${r} ${r} 0 0 1 ${hw} ${-hh + r}V${hh - r}A${r} ${r} 0 0 1 ${hw - r} ${hh}`
        + `H${-hw + r}A${r} ${r} 0 0 1 ${-hw} ${hh - r}V${-hh + r}A${r} ${r} 0 0 1 ${-hw + r} ${-hh}Z`;
}

function radiusFor(w, h) {
    return clamp(Math.min(w, h) * 0.26, 9, 18);
}

// Use the element's own fill if it's colourful, otherwise the goo is orange
function colorOf(el) {
    const match = getComputedStyle(el).backgroundColor.match(/[\d.]+/g);
    if (!match) return ORANGE;
    const [r, g, b, a = 1] = match.map(Number);
    if (a < 0.5 || Math.max(r, g, b) - Math.min(r, g, b) < 60) return ORANGE;
    return `rgb(${r} ${g} ${b})`;
}

// ---------------------------------------------------------------------------
// Splash: one gooey group. Optionally a strand connecting a drop to the edge
// of a source element, plus any number of free-flying drops.

class Splash {
    constructor({ color, src = null, flat = false }) {
        this.color = color;
        this.src = src;
        this.flat = flat;
        this.age = 0;
        this.drops = [];
        this.edges = [];
        this.strand = null;
        this.g = svgEl('g', { filter: `url(#${flat ? 'goo-flat' : 'goo-ink'})` }, world);

        if (src) {
            // Hide goo overlapping its source, so it looks like it oozes out from under the edge
            this.shape = shapeOf(src);
            this.clip = svgEl('clipPath', { id: `goo-clip-${++clipId}` }, defs);
            this.clipPath = svgEl('path', { 'clip-rule': 'evenodd', d: outsidePath(this.shape) }, this.clip);
            this.g.setAttribute('clip-path', `url(#${this.clip.id})`);
        }

        if (splashes.size > 30) [...splashes].find((splash) => splash !== held)?.remove();
        splashes.add(this);
        wake();
    }

    remove() {
        this.g.remove();
        this.clip?.remove();
        splashes.delete(this);
        if (held === this) held = null;
    }

    addDrop(props) {
        const drop = {
            vx: 0, vy: 0, s: 0, vs: 0, state: 'free', canSplash: true,
            ...props,
            el: svgEl('ellipse', { fill: this.color }, this.g),
        };
        this.drops.push(drop);
        return drop;
    }

    // Where the strand meets the element this frame, in page coordinates
    anchor() {
        if (this.anchorFrame !== frame) {
            this.anchorFrame = frame;
            const s = this.shape = shapeOf(this.src);
            const { p, n } = this.strand;
            const point = toPage(s, p.x, p.y);
            const nx = s.a * n.x + s.c * n.y, ny = s.b * n.x + s.d * n.y;
            const len = Math.hypot(nx, ny) || 1;
            this.anchorPoint = { x: point.x, y: point.y, nx: nx / len, ny: ny / len };
            this.clipPath.setAttribute('transform', `matrix(${s.a} ${s.b} ${s.c} ${s.d} ${s.cx} ${s.cy})`);
        }
        return this.anchorPoint;
    }

    // Pull goo out of the source's edge nearest (clientX, clientY)
    pull(clientX, clientY, mode, vx = 0, vy = 0) {
        const s = this.shape;
        const local = toLocal(s, clientX + scrollX, clientY + scrollY);
        const edge = edgePoint(s, local.x, local.y);
        // Every strand gets its own personality, so no two drips behave the same
        const r = radiusFor(s.w, s.h) * rand(0.75, 1.3);

        this.strand = {
            p: { x: edge.x - edge.nx, y: edge.y - edge.ny },
            n: { x: edge.nx, y: edge.ny },
            r,
            anchorR: 0,
            connected: true,
            maxLen: r * rand(8, 14),
            snapSpeed: rand(1100, 1900),
            follow: rand(110, 260),
            sag: rand(0.1, 0.32),
            thin: rand(3.4, 4.8),
            noise: rand(0.05, 0.2),
            noiseFreq: rand(8, 16),
            phase: rand(0, 100),
            hangFor: rand(0.35, 1.2),
            circles: [],
            stub: null,
        };

        const a = this.anchor();
        this.ctrl = { x: a.x, y: a.y, vx: 0, vy: 0 };
        this.main = this.addDrop({ x: a.x, y: a.y, r: r * 0.45, targetR: r, state: mode, vx, vy });

        if (mode === 'held') held = this;
        return this;
    }

    snap() {
        const st = this.strand;
        if (!st?.connected) return;
        st.connected = false;
        if (held === this) held = null;

        const main = this.main;
        const a = this.anchor();
        const curve = this.curve(a, main);

        // Break somewhere along the strand: the stub springs back, the rest recoils into the drop
        const tb = rand(0.25, 0.6);
        const bp = bezier(curve, tb);
        st.stub = { x: bp.x, y: bp.y, vx: 0, vy: 0, r: this.thickness(tb, curve.len) };

        let leader = main;
        for (const t of [0.85, 0.72, 0.6].filter((t) => t > tb)) {
            const p = bezier(curve, t);
            leader = this.addDrop({ state: 'tail', leader, x: p.x, y: p.y, r: this.thickness(t, curve.len) * 1.2 });
        }

        main.state = 'free';
        // Yanking it throws the drop, so goo can be flung at the edges of the screen
        main.vx += clamp(pointer.vx * 0.3, -1500, 1500);
        main.vy += clamp(pointer.vy * 0.3, -1500, 1500);
        main.vs += rand(3, 8) * (Math.random() < 0.5 ? -1 : 1);

        for (let i = randInt(0, 3); i > 0; i--) {
            const p = bezier(curve, rand(tb, 1));
            this.addDrop({
                x: p.x, y: p.y, r: st.r * rand(0.15, 0.32),
                vx: main.vx * rand(0.4, 1.1) + rand(-160, 160),
                vy: main.vy * rand(0.4, 1.1) + rand(-200, 60),
            });
        }
    }

    retract() {
        if (!this.strand?.connected) return;
        this.main.state = 'retract';
        if (held === this) held = null;
    }

    // Cubic curve from the element edge to the drop: leaves the edge along its normal and sags
    curve(a, main) {
        const len = Math.hypot(main.x - a.x, main.y - a.y);
        const out = Math.min(len * 0.35, this.strand.r * 2.5);
        return {
            len,
            p0: a,
            p1: { x: a.x + a.nx * out, y: a.y + a.ny * out },
            p2: this.ctrl,
            p3: main,
        };
    }

    // Strand radius along its length: thick at both ends, thinning in the middle as it stretches
    thickness(t, len) {
        const st = this.strand;
        const thin = Math.max(st.thin, st.r * 0.6 * Math.sqrt((st.r * 2.5) / Math.max(len, st.r * 2.5)));
        const endA = Math.max(thin, st.anchorR * 0.85);
        const endB = Math.max(thin, this.main.r * 0.7);
        const base = thin + (endA - thin) * (1 - t) ** 2.4 + (endB - thin) * t ** 2.6;
        return base * (1 + st.noise * Math.sin(t * st.noiseFreq + this.age * 7 + st.phase));
    }

    step(dt) {
        this.age += dt;
        const st = this.strand;
        const floor = scrollY + innerHeight;
        let alive = false;

        if (st && !st.done) {
            const a = this.anchor();
            const main = this.main;
            const len = Math.hypot(main.x - a.x, main.y - a.y);

            if (st.connected) {
                if (main.state === 'held') {
                    // Stuck to the pointer, lagging a little like something viscous
                    const tx = pointer.x + scrollX, ty = pointer.y + scrollY + main.r * 0.25;
                    const damp = 2 * Math.sqrt(st.follow) * 0.6;
                    main.vx += (st.follow * (tx - main.x) - damp * main.vx) * dt;
                    main.vy += (st.follow * (ty - main.y) - damp * main.vy) * dt;
                    if (len > st.maxLen || (pointerSpeed() > st.snapSpeed && len > st.r * 2)) this.snap();
                } else if (main.state === 'hang') {
                    // Sticky: pulled back towards the edge while gravity makes it sag, until it lets go
                    main.vx += (-28 * (main.x - a.x) - 3 * main.vx) * dt;
                    main.vy += (-28 * (main.y - a.y) - 3 * main.vy + 900) * dt;
                    if (this.age > st.hangFor || len > st.maxLen * 0.8) this.snap();
                } else if (main.state === 'retract') {
                    main.vx += (260 * (a.x - main.x) - 28 * main.vx) * dt;
                    main.vy += (260 * (a.y - main.y) - 28 * main.vy) * dt;
                    if (len < st.r) main.targetR = 0;
                    if (main.r < 0.8) {
                        st.connected = false;
                        main.state = 'dead';
                    }
                }

                // Mid-strand mass, so it swings and sags instead of staying straight
                const mx = (a.x + main.x) / 2, my = (a.y + main.y) / 2 + st.sag * len;
                this.ctrl.vx += (120 * (mx - this.ctrl.x) - 11 * this.ctrl.vx) * dt;
                this.ctrl.vy += (120 * (my - this.ctrl.y) - 11 * this.ctrl.vy) * dt;
                this.ctrl.x += this.ctrl.vx * dt;
                this.ctrl.y += this.ctrl.vy * dt;
            }

            // Edge blob swells as goo is pulled out, and shrinks back in once it lets go
            const anchorTarget = st.connected && main.state !== 'retract' ? st.r * clamp(0.6 + len / (st.maxLen * 2), 0.6, 1) : 0;
            st.anchorR = lerp(st.anchorR, anchorTarget, 1 - Math.exp(-dt * (st.connected ? 10 : 7)));

            this.drawStrand(a, dt);

            if (st.connected || st.stub || st.anchorR > 0.5) {
                alive = true;
            } else {
                // Nothing is attached any more, so falling goo can pass in front of the element
                st.done = true;
                st.circles.forEach((c) => c.remove());
                this.g.removeAttribute('clip-path');
                this.clip.remove();
                this.clip = null;
            }
        }

        for (const d of this.drops) {
            if (d.state === 'dead') continue;
            this.stepDrop(d, dt, floor);
            if (d.state !== 'dead') alive = true;
        }

        for (const edge of this.edges) {
            if (edge.dead) continue;
            if (edge.step(dt)) alive = true;
            else edge.remove();
        }

        return alive && this.age < 90;
    }

    drawStrand(a, dt) {
        const st = this.strand;
        const main = this.main;
        let points = [];

        if (st.connected) {
            const curve = this.curve(a, main);
            const count = clamp(Math.ceil(curve.len / (st.thin * 1.4)), 3, 18);
            for (let i = 1; i < count; i++) {
                const t = i / count;
                const p = bezier(curve, t);
                points.push([p.x, p.y, this.thickness(t, curve.len)]);
            }
        } else if (st.stub) {
            // The broken stub springs back into the element and thins out
            const stub = st.stub;
            stub.vx += (200 * (a.x - stub.x) - 18 * stub.vx) * dt;
            stub.vy += (200 * (a.y - stub.y) - 18 * stub.vy) * dt;
            stub.x += stub.vx * dt;
            stub.y += stub.vy * dt;
            stub.r *= 1 - 3.5 * dt;

            const len = Math.hypot(stub.x - a.x, stub.y - a.y);
            const count = clamp(Math.ceil(len / 5), 1, 10);
            for (let i = 1; i <= count; i++) {
                const t = i / count;
                points.push([lerp(a.x, stub.x, t), lerp(a.y, stub.y, t), lerp(st.anchorR * 0.8, stub.r, t)]);
            }
            if (stub.r < 0.8 || len < 2) st.stub = null;
        }

        points.unshift([a.x, a.y, st.anchorR]);

        while (st.circles.length < points.length) st.circles.push(svgEl('circle', { fill: this.color }, this.g));
        st.circles.forEach((c, i) => {
            const p = points[i];
            p ? setCircle(c, p[0], p[1], p[2]) : c.setAttribute('r', 0);
        });
    }

    stepDrop(d, dt, floor) {
        if (d.targetR !== undefined) d.r = lerp(d.r, d.targetR, 1 - Math.exp(-dt * 9));

        let angle = Math.atan2(d.vy, d.vx);
        let stretchTarget = clamp(Math.hypot(d.vx, d.vy) / 2600, 0, 0.5);

        if (d.state === 'free') {
            d.vx *= 1 - 0.5 * dt;
            d.vy += GRAVITY * dt;

            // Falling goo hangs in the air in front of the page, so it doesn't jump when the page
            // scrolls, but gets dragged along a little and wobbles
            if (scrollDelta) {
                d.y += scrollDelta * 0.8;
                d.vy -= clamp(scrollDelta / Math.max(dt, 0.001), -3000, 3000) * 0.04;
                d.vs += clamp(Math.abs(scrollDelta) * 0.03, 0, 2);
            }

            // The screen is sticky: goo that hits the top or sides splots against it
            if (d.canSplash && d.r > 3.5) {
                const sx = d.x - scrollX, sy = d.y - scrollY;
                if (sy - d.r * 0.5 < 0 && d.vy < -120) return this.stick(d, new TopGoo(d, sx));
                if (sx - d.r * 0.5 < 0 && d.vx < -120) return this.stick(d, new SideGoo(d, -1, sy));
                if (sx + d.r * 0.5 > viewportWidth() && d.vx > 120) return this.stick(d, new SideGoo(d, 1, sy));
            }

            if (d.vy > 0 && d.y + d.r * 0.8 >= floor) {
                if (d.canSplash && d.r > 2.5 && d.x > scrollX && d.x < scrollX + innerWidth) this.splash(d, floor);
                else d.state = 'dead';
            }
            if (d.y - d.r > floor + 40 || Math.abs(d.x - scrollX - innerWidth / 2) > innerWidth) d.state = 'dead';
        } else if (d.state === 'tail') {
            // Leftover strand recoils into the drop above it
            d.vx += (380 * (d.leader.x - d.x) - 24 * d.vx) * dt;
            d.vy += (380 * (d.leader.y - d.y) - 24 * d.vy) * dt;
            d.r *= 1 - 5 * dt;
            if (d.r < 0.5) d.state = 'dead';
        } else if (d.state === 'puddle') {
            // Spreads out along the bottom edge, then soaks away
            d.age = (d.age ?? 0) + dt;
            d.rx = lerp(d.rx, d.age < 0.2 ? d.spread : 0, 1 - Math.exp(-dt * (d.age < 0.2 ? 20 : 3)));
            d.ry = lerp(d.ry, d.age < 0.2 ? d.r * 0.45 : 0, 1 - Math.exp(-dt * (d.age < 0.2 ? 20 : 3)));
            d.el.setAttribute('cx', d.x.toFixed(1));
            d.el.setAttribute('cy', (floor + d.ry * 0.15).toFixed(1));
            d.el.setAttribute('rx', Math.max(0, d.rx).toFixed(2));
            d.el.setAttribute('ry', Math.max(0, d.ry).toFixed(2));
            d.el.removeAttribute('transform');
            if (d.ry < 0.5 && d.age > 0.2) d.state = 'dead';
            return;
        } else if (d.state === 'dead') {
            return;
        } else if (this.strand) {
            // Still attached: stretch along the strand like a teardrop
            const a = this.anchor();
            const len = Math.hypot(d.x - a.x, d.y - a.y);
            angle = Math.atan2(d.y - a.y, d.x - a.x);
            stretchTarget = clamp(len / this.strand.maxLen, 0, 1) * 0.4;
        }

        d.x += d.vx * dt;
        d.y += d.vy * dt;

        // A springy shape, so drops jiggle when they snap free
        d.vs += (-240 * (d.s - stretchTarget) - 6 * d.vs) * dt;
        d.s = clamp(d.s + d.vs * dt, -0.35, 0.8);

        const rx = d.r * (1 + d.s), ry = d.r / Math.sqrt(1 + Math.max(d.s, -0.3) * 1.6);
        if (d.state === 'dead') {
            d.el.setAttribute('rx', 0);
            return;
        }
        d.el.setAttribute('cx', d.x.toFixed(1));
        d.el.setAttribute('cy', d.y.toFixed(1));
        d.el.setAttribute('rx', Math.max(0, rx).toFixed(2));
        d.el.setAttribute('ry', Math.max(0, ry).toFixed(2));
        d.el.setAttribute('transform', `rotate(${(angle * 180 / Math.PI).toFixed(1)} ${d.x.toFixed(1)} ${d.y.toFixed(1)})`);
    }

    // Hand a drop over to goo stuck on the edge of the screen, in its own group
    stick(d, edge) {
        d.state = 'dead';
        d.el.setAttribute('rx', 0);

        const splash = new Splash({ color: this.color, flat: this.flat });
        edge.attach(splash);
        splash.edges.push(edge);

        // Splot: a couple of flecks spatter off on impact
        for (let i = randInt(1, 3); i > 0; i--) {
            splash.addDrop({
                x: d.x, y: d.y, r: d.r * rand(0.15, 0.3), canSplash: false,
                vx: -d.vx * rand(0.05, 0.25) + rand(-120, 120),
                vy: Math.abs(d.vy) * rand(0.05, 0.2) + rand(40, 160),
            });
        }
    }

    // Hitting the bottom of the screen: a puddle plus a spray of droplets
    splash(d, floor) {
        const impact = Math.min(d.vy, 2400);
        d.state = 'puddle';
        d.rx = d.r;
        d.ry = d.r;
        d.spread = d.r * rand(1.8, 3);

        for (let i = randInt(2, 3) + Math.round(d.r / 5); i > 0; i--) {
            this.addDrop({
                x: d.x + rand(-d.r, d.r), y: floor - 2,
                r: d.r * rand(0.14, 0.36),
                vx: rand(-1, 1) * rand(60, 320) + d.vx * 0.2,
                vy: -Math.max(impact, 900) * rand(0.22, 0.55),
                canSplash: false,
            });
        }
    }
}

// ---------------------------------------------------------------------------
// Goo stuck to the edges of the screen. It lives in screen coordinates, so it
// stays put while the page scrolls underneath: it's stuck to the glass.

class EdgeGoo {
    attach(splash) {
        this.splash = splash;
        this.g = splash.g;
        this.color = splash.color;
        this.els = [];

        // Don't let the edges get too crowded
        edgeGoo.add(this);
        if (edgeGoo.size > 8) [...edgeGoo][0].remove();
    }

    remove() {
        this.dead = true;
        this.els.forEach((el) => el.remove());
        edgeGoo.delete(this);
    }

    // Draw a list of [x, y, rx, ry] blobs (screen coordinates), reusing elements
    draw(blobs) {
        while (this.els.length < blobs.length) this.els.push(svgEl('ellipse', { fill: this.color }, this.g));
        this.els.forEach((el, i) => {
            const b = blobs[i];
            if (!b || b[2] < 0.3 || b[3] < 0.3) return el.setAttribute('rx', 0);
            el.setAttribute('cx', (b[0] + scrollX).toFixed(1));
            el.setAttribute('cy', (b[1] + scrollY).toFixed(1));
            el.setAttribute('rx', b[2].toFixed(2));
            el.setAttribute('ry', b[3].toFixed(2));
        });
    }

    // Let go of the screen: becomes a normal falling drop again
    fall(x, y, r, vx = 0, vy = 0) {
        this.splash.addDrop({ x: x + scrollX, y: y + scrollY, r, vx, vy, vs: rand(2, 5) });
    }
}

// Splotted against the top: the goo slowly gathers into a bulb that sags and
// stretches on a thinning neck until it pinches off and drips.
class TopGoo extends EdgeGoo {
    constructor(d, x) {
        super();
        this.x = clamp(x, 0, viewportWidth());
        this.volume = d.r * rand(1.1, 1.35);
        this.drift = clamp(d.vx * 0.08, -60, 60);
        this.rx = d.r * 0.6;
        this.ry = d.r * 1.2;
        this.spread = rand(1.5, 2.5);
        this.nextDrip();
    }

    nextDrip() {
        this.drip = {
            wait: rand(0.3, 1.6),
            r: 0,
            size: this.volume * rand(0.55, 0.8),
            y: 0,
            vy: 0,
            pinch: rand(2.6, 4.2),
            sway: rand(0, Math.PI * 2),
        };
    }

    step(dt) {
        const d = this.drip;

        // Splot flat, then shrink as goo gathers into the drip (or soaks away)
        const left = this.volume - (d ? d.r * 0.6 : 0);
        this.rx = lerp(this.rx, Math.max(0, left) * this.spread, 1 - Math.exp(-dt * 14));
        this.ry = lerp(this.ry, Math.max(0, left) * 0.8, 1 - Math.exp(-dt * 14));
        this.x += this.drift * dt;
        this.drift *= 1 - 3 * dt;

        // Scrolling jolts it: the splot jiggles and any drip swings and stretches
        if (scrollDelta) {
            this.rx *= 1 + clamp(Math.abs(scrollDelta) * 0.004, 0, 0.06);
            if (d && d.wait <= 0) d.vy += clamp(Math.abs(scrollDelta) * 4, 0, 400);
            else if (d) d.wait -= Math.abs(scrollDelta) * 0.004;
        }

        const blobs = [[this.x, this.ry * 0.2, this.rx, this.ry]];

        if (d && (d.wait -= dt) < 0) {
            // Goo slowly gathers, and the heavier the bulb the faster it sags
            d.r = lerp(d.r, d.size, 1 - Math.exp(-dt * 1.4));
            d.vy += (70 * (d.r / 9) ** 2 - 1.6 * d.vy) * dt;
            d.y += d.vy * dt;

            const len = d.y;
            const stretch = clamp(len / (d.r * d.pinch), 0, 1);
            const bx = this.x + Math.sin(this.splash.age * 2.2 + d.sway) * stretch * 3;
            const neck = Math.max(3.6, d.r * 0.6 * Math.sqrt(d.r * 1.4 / Math.max(len, d.r * 1.4)));
            const count = clamp(Math.ceil(len / 4), 1, 16);

            for (let i = 1; i < count; i++) {
                const t = i / count;
                const r = lerp(Math.max(neck, this.ry * 0.8), neck, Math.min(1, t * 2)) + (t > 0.7 ? (t - 0.7) * d.r : 0);
                blobs.push([lerp(this.x, bx, t), len * t, r, r]);
            }
            blobs.push([bx, len, d.r * (1 - stretch * 0.15), d.r * (1 + stretch * 0.35)]);

            if (len > d.r * d.pinch) {
                // Drip!
                this.fall(bx, len + d.r * 0.3, d.r, 0, d.vy + 80);
                this.volume -= d.r * 0.6;
                if (this.volume > 5 && Math.random() < 0.55) this.nextDrip();
                else this.drip = null;
            }
        } else if (!d) {
            this.volume *= 1 - 0.45 * dt;
        }

        this.draw(blobs);
        return this.volume > 1.2 && this.x > -40 && this.x < viewportWidth() + 40;
    }
}

// Splotted against a side: it either dribbles down in stick-slip lurches,
// leaving a trail, or tumbles down end over end like a wall-walker toy.
class SideGoo extends EdgeGoo {
    constructor(d, side, y) {
        super();
        this.side = side;
        this.y = clamp(y, 0, innerHeight);
        this.r = d.r * rand(1.1, 1.4);
        this.squash = 1;
        this.trail = [];
        this.walking = Math.random() < 0.5;
        this.timer = rand(0.3, 0.9);

        if (this.walking) {
            this.lr = this.r * 0.8;
            this.sep = this.lr * rand(1.6, 2.2);
            this.pivot = this.y + this.sep / 2;
            this.flip = null;
        } else {
            this.speed = 0;
            this.moving = false;
            this.travelled = 0;
        }
    }

    get size() {
        return this.walking ? this.lr : this.r;
    }

    set size(value) {
        if (this.walking) this.lr = value;
        else this.r = value;
    }

    get top() {
        return this.walking ? this.pivot - this.sep : this.y;
    }

    // x for something hugging the wall, `inset` px in from the edge
    wallX(inset) {
        return this.side < 0 ? inset : viewportWidth() - inset;
    }

    step(dt) {
        this.squash = lerp(this.squash, 0, 1 - Math.exp(-dt * 6));

        if (scrollDelta && !this.gone) {
            // The page sliding underneath drags it along a little and squishes it
            const drag = clamp(-scrollDelta * 0.12, -12, 12);
            if (this.walking) this.pivot += drag;
            else this.y += drag;
            this.squash = Math.min(1, this.squash + Math.abs(scrollDelta) * 0.02);

            // Really fling the page and it gets shaken loose
            if (Math.abs(scrollSpeed) > 3500 && Math.random() < dt * 3) {
                this.gone = true;
                const y = this.walking ? this.pivot : this.y;
                this.fall(this.wallX(this.size * 0.6), y, this.size, -this.side * rand(40, 140), -scrollSpeed * 0.1);
            }
        }

        const blobs = this.walking ? this.walk(dt) : this.dribble(dt);

        // Trail left behind, drying up
        for (const t of this.trail) t.r *= 1 - 0.7 * dt;
        this.trail = this.trail.filter((t) => t.r > 1.5);
        blobs.push(...this.trail.map((t) => [this.wallX(t.inset), t.y, t.r, t.r * 1.3]));

        this.draw(blobs);
        return !this.gone || this.trail.length > 0;
    }

    dribble(dt) {
        if (this.gone) return [];

        // Stick-slip: it lurches, sticks for a moment, then gives way again
        if ((this.timer -= dt) < 0) {
            this.moving = !this.moving;
            this.timer = this.moving ? rand(0.25, 1.1) : rand(0.15, 1);
            this.target = this.moving ? rand(25, 120) * (this.r / 12) : 0;
        }
        this.speed = lerp(this.speed, this.target ?? 0, 1 - Math.exp(-dt * 5));

        const moved = this.speed * dt;
        this.y += moved;
        this.travelled += moved;
        this.r -= moved * 0.01;

        if (this.travelled > 5) {
            this.travelled = 0;
            this.trail.push({ y: this.y - this.r * 0.9, r: this.r * rand(0.3, 0.45), inset: this.r * 0.45 });
        }

        const r = this.r;
        const pace = clamp(this.speed / 100, 0, 1);
        const x = this.wallX(r * 0.6);

        if (this.y + r > innerHeight) {
            this.gone = true;
            this.fall(x, this.y, r, 0, this.speed + 100);
            return [];
        }
        if (r < 3.5) {
            this.gone = true;
            return [];
        }

        return [
            // Splotted flat first, then a teardrop with a heavier bead leading the way down
            [x, this.y, r * (0.75 + this.squash * 0.4), r * (1.1 + pace * 0.3 + this.squash * 0.5)],
            [x, this.y + r * (0.7 + pace * 0.3), r * 0.62, r * 0.62],
        ];
    }

    walk(dt) {
        if (this.gone) return [];

        const inset = this.lr * 0.75;
        const out = this.side < 0 ? 1 : -1;
        let top = { x: this.wallX(inset), y: this.pivot - this.sep };

        if (!this.flip && (this.timer -= dt) < 0) {
            this.flip = { t: 0, duration: rand(0.45, 0.85) };
        }

        if (this.flip) {
            // The top end peels off the glass and swings over the bottom end to land below it
            const f = this.flip;
            f.t = Math.min(1, f.t + dt / f.duration);
            const e = f.t < 0.5 ? 4 * f.t ** 3 : 1 - (-2 * f.t + 2) ** 3 / 2;
            const angle = e * Math.PI;
            top = {
                x: this.wallX(inset) + out * Math.sin(angle) * this.sep * 0.85,
                y: this.pivot - Math.cos(angle) * this.sep,
            };

            if (f.t >= 1) {
                // Landed: squish, leave a little mark where it peeled off, lose a bit of goo
                this.trail.push({ y: this.pivot - this.sep, r: this.lr * rand(0.3, 0.45), inset });
                this.pivot += this.sep;
                this.flip = null;
                this.squash = 1;
                this.timer = rand(0.1, 0.8);
                this.lr *= rand(0.95, 0.985);
                this.sep = this.lr * rand(1.6, 2.2);
                top = { x: this.wallX(inset), y: this.pivot - this.sep };
            }
        }

        const lr = this.lr;
        const bottom = { x: this.wallX(inset), y: this.pivot };

        if (bottom.y + lr > innerHeight) {
            this.gone = true;
            this.fall(bottom.x, bottom.y, lr * 1.2, 0, 200);
            return [];
        }
        if (lr < 4) {
            this.gone = true;
            return [];
        }

        // Neck between the two ends, thinner the further apart they are
        const blobs = [];
        const dist = Math.hypot(top.x - bottom.x, top.y - bottom.y);
        const neck = lr * clamp(0.75 - dist / (this.sep * 3), 0.35, 0.65);
        for (let i = 1; i < 4; i++) {
            const t = i / 4;
            blobs.push([lerp(bottom.x, top.x, t), lerp(bottom.y, top.y, t), neck, neck]);
        }

        const sq = this.squash;
        blobs.push([bottom.x, bottom.y, lr * (1 + sq * 0.25), lr * (1 - sq * 0.2)]);
        blobs.push([top.x, top.y, lr * 0.95, lr * 0.95]);
        return blobs;
    }
}


// ---------------------------------------------------------------------------
// Merging: globs that touch pull together and become one bigger glob

function mergeDrops(dt) {
    const drops = [];
    for (const splash of splashes) {
        for (const d of splash.drops) {
            if (d.state === 'free' && d.r > 1.5) drops.push([d, splash]);
        }
    }

    for (let i = 0; i < drops.length; i++) {
        const [a, sa] = drops[i];
        if (a.state !== 'free') continue;

        for (let j = i + 1; j < drops.length; j++) {
            const [b, sb] = drops[j];
            if (b.state !== 'free') continue;

            const dx = b.x - a.x, dy = b.y - a.y;
            const dist = Math.hypot(dx, dy) || 0.01;
            const reach = (a.r + b.r) * 1.15;
            if (dist > reach) continue;

            const [big, small, bigSplash, smallSplash] = a.r >= b.r ? [a, b, sa, sb] : [b, a, sb, sa];

            // Touching: move into the same gooey group so the filter visibly joins them up
            if (smallSplash !== bigSplash) {
                smallSplash.drops.splice(smallSplash.drops.indexOf(small), 1);
                bigSplash.drops.push(small);
                bigSplash.g.appendChild(small.el);
                drops[a === small ? i : j][1] = bigSplash;
            }

            // Surface tension pulls them together
            const pull = 4000 * dt / dist;
            const ma = a.r * a.r, mb = b.r * b.r;
            a.vx += dx * pull * mb / (ma + mb);
            a.vy += dy * pull * mb / (ma + mb);
            b.vx -= dx * pull * ma / (ma + mb);
            b.vy -= dy * pull * ma / (ma + mb);

            if (dist < big.r * 0.6) {
                // One glob now: same amount of goo, momentum carried over, and a good jiggle
                const mBig = big.r * big.r, mSmall = small.r * small.r;
                big.vx = (big.vx * mBig + small.vx * mSmall) / (mBig + mSmall);
                big.vy = (big.vy * mBig + small.vy * mSmall) / (mBig + mSmall);
                big.x = (big.x * mBig + small.x * mSmall) / (mBig + mSmall);
                big.y = (big.y * mBig + small.y * mSmall) / (mBig + mSmall);
                big.r = Math.sqrt(mBig + mSmall);
                big.vs += rand(3, 6) * (small.r / big.r + 0.3);
                big.canSplash ||= small.canSplash && big.r > 3;
                small.state = 'dead';
                small.el.setAttribute('rx', 0);
            }
        }
    }
}

// Edge goo that meets other edge goo on the same edge joins up
function mergeEdges() {
    const edges = [...edgeGoo].filter((e) => !e.dead && !e.gone);

    for (let i = 0; i < edges.length; i++) {
        for (let j = i + 1; j < edges.length; j++) {
            const a = edges[i], b = edges[j];
            if (a.dead || b.dead) continue;

            if (a instanceof TopGoo && b instanceof TopGoo) {
                if (Math.abs(a.x - b.x) > (a.rx + b.rx) * 0.6) continue;
                const [big, small] = a.volume >= b.volume ? [a, b] : [b, a];
                big.x = (big.x * big.volume + small.x * small.volume) / (big.volume + small.volume);
                big.volume = Math.hypot(big.volume, small.volume);
                if (!big.drip) big.nextDrip();
                small.remove();
            } else if (a instanceof SideGoo && b instanceof SideGoo && a.side === b.side) {
                const gap = Math.abs((a.walking ? a.pivot : a.y) - (b.walking ? b.pivot : b.y));
                if (gap > (a.size + b.size) * 1.1) continue;
                const [big, small] = a.size >= b.size ? [a, b] : [b, a];
                big.size = Math.hypot(big.size, small.size) * 0.95;
                if (big.walking) big.sep = big.lr * rand(1.6, 2.2);
                big.squash = 1;
                big.trail.push(...small.trail);
                small.remove();
            }
        }
    }
}

function bezier({ p0, p1, p2, p3 }, t) {
    const u = 1 - t;
    const a = u * u * u, b = 3 * u * u * t, c = 3 * u * t * t, d = t * t * t;
    return { x: a * p0.x + b * p1.x + c * p2.x + d * p3.x, y: a * p0.y + b * p1.y + c * p2.y + d * p3.y };
}

function onGooLeave(el, e) {
    // Straight into a neighbour: no goo in the gap between them
    const over = document.elementFromPoint(e.clientX, e.clientY)?.closest(GOO);
    if (over && over !== el && !el.contains(over)) return;

    held?.snap();

    const speed = pointerSpeed();
    const splash = new Splash({ color: colorOf(el), src: el });

    if (speed > 1700) {
        // Yanked out too fast to stick: it's flung out, sags for a moment, and lets go
        const k = clamp(speed * 0.35, 0, 900) / speed;
        splash.pull(e.clientX, e.clientY, 'hang', pointer.vx * k, pointer.vy * k);
    } else {
        splash.pull(e.clientX, e.clientY, 'held');
    }
}

const lastFlick = new WeakMap();

function flick(el) {
    const speed = pointerSpeed();
    const now = performance.now();
    if (speed < 1500 || now - (lastFlick.get(el) ?? 0) < rand(110, 220)) return;
    lastFlick.set(el, now);

    // Fling droplets off whichever edge we're heading towards
    const rect = el.getBoundingClientRect();
    const dx = pointer.vx / speed, dy = pointer.vy / speed;
    const tx = dx > 0 ? (rect.right - pointer.x) / dx : (rect.left - pointer.x) / dx;
    const ty = dy > 0 ? (rect.bottom - pointer.y) / dy : (rect.top - pointer.y) / dy;
    const t = Math.min(Math.abs(tx), Math.abs(ty));
    const base = radiusFor(rect.width, rect.height);
    const x = pointer.x + dx * (t + base * 0.6) + scrollX;
    const y = pointer.y + dy * (t + base * 0.6) + scrollY;

    const splash = new Splash({ color: colorOf(el) });
    for (let i = randInt(1, 3); i > 0; i--) {
        const v = clamp(speed * 0.35, 300, 900) * rand(0.6, 1.2);
        const spread = rand(-0.45, 0.45);
        const cos = Math.cos(spread), sin = Math.sin(spread);
        splash.addDrop({
            x: x + rand(-3, 3), y: y + rand(-3, 3),
            r: base * rand(0.25, 0.6),
            vx: (dx * cos - dy * sin) * v,
            vy: (dx * sin + dy * cos) * v,
            vs: rand(-4, 4),
        });
    }
}

// ---------------------------------------------------------------------------
// Jelly wobble: velocity-driven squash & stretch on independent `translate`
// and `scale` properties, so it composes with the CSS hover transforms.

function wobbleState(el) {
    let state = wobbles.get(el);
    if (!state) {
        state = { x: 0, y: 0, vx: 0, vy: 0, sx: 1, sy: 1, vsx: 0, vsy: 0, hover: false };
        wobbles.set(el, state);
        wake();
    }
    return state;
}

function onWobbleLeave(el) {
    const state = wobbleState(el);
    state.hover = false;
    // Give it a shove in the direction the pointer left
    state.vx += clamp(pointer.vx * 0.06, -120, 120);
    state.vy += clamp(pointer.vy * 0.06, -120, 120);
    state.vsx += clamp(Math.abs(pointer.vx) / 2500, 0, 1.2);
    state.vsy -= clamp(Math.abs(pointer.vx) / 4000, 0, 0.8);
    wake();
}

function stepWobble(el, s, dt) {
    const rect = el.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const amp = clamp(70 / size, 0.015, 0.12);

    let tx = 0, ty = 0, tsx = 1, tsy = 1;

    if (s.hover) {
        // Lean towards the pointer, and stretch along the direction of travel
        const maxShift = clamp(size * 0.04, 2, 7);
        tx = clamp((pointer.x - (rect.left + rect.width / 2)) * 0.06, -maxShift, maxShift);
        ty = clamp((pointer.y - (rect.top + rect.height / 2)) * 0.06, -maxShift, maxShift);
        const ax = clamp(Math.abs(pointer.vx) / 2500, 0, 1) * amp;
        const ay = clamp(Math.abs(pointer.vy) / 2500, 0, 1) * amp;
        tsx = 1 + ax - ay * 0.6;
        tsy = 1 + ay - ax * 0.6;
    }

    // Underdamped springs, so it overshoots and jiggles
    s.vx += (-240 * (s.x - tx) - 12 * s.vx) * dt;
    s.vy += (-240 * (s.y - ty) - 12 * s.vy) * dt;
    s.vsx += (-420 * (s.sx - tsx) - 9 * s.vsx) * dt;
    s.vsy += (-420 * (s.sy - tsy) - 9 * s.vsy) * dt;
    s.x += s.vx * dt;
    s.y += s.vy * dt;
    s.sx += s.vsx * dt;
    s.sy += s.vsy * dt;

    const settled = !s.hover
        && Math.abs(s.x) + Math.abs(s.y) < 0.05
        && Math.abs(s.sx - 1) + Math.abs(s.sy - 1) < 0.0005
        && Math.abs(s.vx) + Math.abs(s.vy) + Math.abs(s.vsx) + Math.abs(s.vsy) < 0.05;

    if (settled) {
        el.style.translate = '';
        el.style.scale = '';
        return false;
    }

    el.style.translate = `${s.x.toFixed(2)}px ${s.y.toFixed(2)}px`;
    el.style.scale = `${s.sx.toFixed(4)} ${s.sy.toFixed(4)}`;
    return true;
}

// ---------------------------------------------------------------------------
// Hero blob reaches for the pointer

function setupReach() {
    const container = document.querySelector('.dir-jelly .jelly-goo');
    if (!container) return;

    const sizes = Array.from({ length: 16 }, (_, i) => 0.25 - i * 0.008);
    const arm = sizes.map((size, i) => {
        const el = document.createElement('span');
        el.className = 'jelly-goo-b';
        el.style.cssText = `width:${size * 100}%;height:${size * 100}%;left:50%;top:50%;margin:${-size * 50}%;will-change:transform`;
        container.appendChild(el);
        // Segments further out are looser, so the arm trails like a noodle, and each wriggles on its own
        return {
            el, x: 0, y: 0, vx: 0, vy: 0,
            k: (150 - i * 6.5) * rand(0.85, 1.15),
            t: 0.2 + i * (0.8 / (sizes.length - 1)),
            wriggle: rand(0.5, 1) * (i + 1) / sizes.length,
            freq: rand(2.5, 5),
            phase: rand(0, Math.PI * 2),
        };
    });

    reach = { container, arm };

    const art = container.closest('.jelly-hero-art');
    art?.addEventListener('click', () => {
        // Splat: goo flies off the blob in every direction
        const rect = container.getBoundingClientRect();
        const cx = rect.left + rect.width / 2 + scrollX, cy = rect.top + rect.height / 2 + scrollY;
        const splash = new Splash({ color: ORANGE });

        for (let i = randInt(6, 10); i > 0; i--) {
            const angle = rand(0, Math.PI * 2);
            const dx = Math.cos(angle), dy = Math.sin(angle);
            const v = rand(350, 1100);
            splash.addDrop({
                x: cx + dx * rect.width * 0.36, y: cy + dy * rect.height * 0.36,
                r: rand(7, 18), vx: dx * v, vy: dy * v - 150, vs: rand(-5, 5),
            });
        }

        const avatar = art.querySelector('.jelly-hero-avatar');
        if (avatar) {
            const s = wobbleState(avatar);
            s.vsx += rand(1.6, 2.6);
            s.vsy -= rand(1.4, 2.2);
        }
    });
}

function stepReach(dt, now) {
    const { container, arm } = reach;
    const rect = container.getBoundingClientRect();
    const w = rect.width;
    const cx = rect.left + w / 2, cy = rect.top + rect.height / 2;
    const dx = pointer.x - cx, dy = pointer.y - cy;
    const dist = Math.hypot(dx, dy);

    // Only reach when the pointer is close, but not over the blob itself
    const inRange = dist > w * 0.32 && dist < w * 2.4 && pointer.t && now - pointer.t < 4000;
    const len = inRange ? Math.min(dist * 0.92, w * 1.55) : 0;
    const ux = dist ? dx / dist : 0, uy = dist ? dy / dist : 0;

    const tip = arm[arm.length - 1];
    const reachNow = Math.hypot(tip.x, tip.y);
    const wasStretched = reachNow > w * 0.7;

    // Only pay for the big filter region while the arm is actually out
    const wide = reachNow > w * 0.2;
    if (wide !== reach.wide) {
        reach.wide = wide;
        container.style.filter = wide ? 'url(#jelly-goo-wide)' : '';
    }

    let moving = false;
    for (const seg of arm) {
        // Wriggle sideways a little so it never moves in a perfectly straight line
        const wiggle = Math.sin(now / 1000 * seg.freq + seg.phase) * seg.wriggle * len * 0.14;
        const tx = ux * len * seg.t - uy * wiggle;
        const ty = uy * len * seg.t + ux * wiggle;
        seg.vx += (-seg.k * (seg.x - tx) - 10 * seg.vx) * dt;
        seg.vy += (-seg.k * (seg.y - ty) - 10 * seg.vy) * dt;
        seg.x += seg.vx * dt;
        seg.y += seg.vy * dt;
        seg.el.style.transform = `translate(${seg.x.toFixed(1)}px, ${seg.y.toFixed(1)}px)`;
        if (len || Math.abs(seg.vx) + Math.abs(seg.vy) > 1 || Math.abs(seg.x) + Math.abs(seg.y) > 0.5) moving = true;
    }

    // Yanked away while fully stretched: the tip snaps off as a droplet
    if (wasStretched && !inRange && !reach.snapped) {
        reach.snapped = true;
        const tl = Math.hypot(tip.x, tip.y) || 1;
        new Splash({ color: ORANGE }).addDrop({
            x: cx + tip.x + scrollX, y: cy + tip.y + scrollY, r: w * rand(0.035, 0.055),
            vx: (tip.x / tl) * rand(150, 350), vy: (tip.y / tl) * rand(150, 350), vs: rand(3, 6),
        });
    }
    if (inRange) reach.snapped = false;

    return moving;
}
