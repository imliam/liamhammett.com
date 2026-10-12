---
title: Press Me
alternate_title:
slug: press-me
published_at: 2026-10-12
updated_at:
strapline: "Small micro-interactions that make buttons feel alive"
synopsis: Most buttons do their job and nothing else. A few tiny details make them feel alive, and none of them change how the button looks.
previous_article: copy-their-homework
next_article:
opengraph_image: /images/opengraph/press-me.png
tags:
    - CSS
    - JavaScript
---

<style>
    .pm-demo { display: flex; flex-wrap: wrap; gap: 2rem 3rem; justify-content: center; align-items: stretch; margin: 1.75rem 0; padding: 2rem 1.5rem; background: var(--j-cream); border: 2.5px solid var(--j-ink); border-radius: 1.25rem; box-shadow: .3rem .3rem 0 var(--j-ink); }
    .pm-col { display: flex; flex-direction: column; align-items: center; gap: .6rem; }
    .pm-col > .pm-btn { margin-block: auto; }
    .pm-label { font-family: var(--j-mono); font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: var(--j-ink-soft); }
    .pm-note { font-size: .85rem; color: var(--j-ink-soft); text-align: center; flex-basis: 100%; margin: -.75rem 0 0; }
    .pm-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .7rem 1.15rem; border: 0; border-radius: .6rem; background: var(--j-ink); color: var(--j-cream); font: 700 1rem/1 var(--j-body); cursor: default; user-select: none; -webkit-tap-highlight-color: transparent; white-space: nowrap; }
    .pm-ico { width: 1.15em; height: 1.15em; flex: none; fill: none; stroke: currentColor; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round; }

    /* Cursor */
    .pm-pointer { cursor: pointer; }

    /* Press */
    .pm-press { transition: scale 100ms ease-out; }
    .pm-press:active { scale: .97; }

    /* Hover */
    .pm-btn .pm-ico { transition: translate 100ms ease-out; }
    .pm-deploy:hover .pm-ico { translate: 2px 0; }
    .pm-docs:hover .pm-ico { translate: 1px -1px; }
    .pm-like:hover .pm-ico { animation: pm-beat 300ms ease-out; }
    .pm-chat .pm-ico { transform-origin: 50% 85%; }
    .pm-chat:hover .pm-ico { animation: pm-wiggle 400ms ease-out; }
    @keyframes pm-beat { 50% { scale: 1.3; } }
    @keyframes pm-wiggle { 25% { rotate: -12deg; } 50% { rotate: 10deg; } 75% { rotate: -5deg; } }

    /* Hit area */
    .pm-icon-btn { padding: .3rem; border-radius: .4rem; }
    .pm-icon-btn .pm-ico { width: .9rem; height: .9rem; }
    .pm-hit { position: relative; }
    .pm-hit::before { content: ''; position: absolute; top: 50%; left: 50%; translate: -50% -50%; width: 100%; height: 100%; min-width: 44px; min-height: 44px; border-radius: .6rem; }
    .pm-show-hits .pm-hit::before { outline: 2px dashed var(--j-orange); background: rgb(255 106 31 / .15); }
    .pm-count { font-family: var(--j-mono); font-size: .85rem; color: var(--j-ink); }

    /* State */
    .pm-state { overflow: hidden; }
    .pm-state.pm-spinning .pm-ico { animation: pm-spin 700ms linear infinite; }
    @keyframes pm-spin { to { rotate: 1turn; } }
</style>

<script>
    // Sounds for the demos: the same click and success chime as the video,
    // loaded up front so they play the instant a button is pressed.
    window.pmSound = (() => {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const load = (url) => fetch(url).then((res) => res.arrayBuffer()).then((data) => ctx.decodeAudioData(data));
        const sounds = { click: load('/audio/click.mp3'), success: load('/audio/success.mp3') };
        const play = async (name) => {
            if (ctx.state === 'suspended') ctx.resume();
            const source = ctx.createBufferSource();
            source.buffer = await sounds[name];
            source.connect(ctx.destination);
            source.start();
        };
        const buzz = () => {
            if ('vibrate' in navigator) navigator.vibrate(10);
        };
        return { click: () => play('click'), success: () => play('success'), buzz };
    })();
</script>

Click this button.

<div class="pm-demo">
    <button class="pm-btn">
        Submit
        <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg>
    </button>
</div>

It works, probably, and something might have happened somewhere. But it doesn't _feel_ like anything happened. That's the problem with a lot of buttons on the web. They do their job and nothing else.

You don't need a redesign to fix that. A handful of tiny details, most of them a few lines of CSS, make a button feel alive. ==None of them change how it looks==, so they work just as well on a flat, minimal button as they do on a gold-trimmed "Play Game" button in a fantasy game.

I made [a video walking through all of these](link to video), but here they all are with buttons you can actually press.

## Cursor

By default, your cursor stays as an arrow, even when it's over a button. Set the cursor to pointer and it turns into a little hand.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">Before</span>
        <button class="pm-btn">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">After</span>
        <button class="pm-btn pm-pointer">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
</div>

```css
button {
    cursor: pointer;
}
```

Not every website does this, but it's a clear sign that what you're hovering over is clickable. One line of CSS for that is a bargain.

## Sound

Play a quiet little click when a button's pressed, and the action feels real. Load the sound up front with the Web Audio API, so it's decoded and ready, and it plays the instant you tap rather than a moment later.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">Before</span>
        <button class="pm-btn pm-pointer">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">After</span>
        <button class="pm-btn pm-pointer" onpointerdown="pmSound.click()">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <p class="pm-note">Turn your sound on for this one.</p>
</div>

```js
const ctx = new AudioContext();
const res = await fetch('/click.mp3');
const click = await ctx.decodeAudioData(await res.arrayBuffer());

button.addEventListener('pointerdown', () => {
    const source = ctx.createBufferSource();
    source.buffer = click;
    source.connect(ctx.destination);
    source.start();
});
```

Keep it short, and keep it subtle. Nobody wants a button that sounds like a slot machine.

## Press

Make it react when you press it. Scale it down a little bit and put a quick transition on it, so it doesn't jump straight to the new size.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">Before</span>
        <button class="pm-btn pm-pointer">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">After</span>
        <button class="pm-btn pm-pointer pm-press">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <p class="pm-note">Hold the mouse button down on each.</p>
</div>

```css
button {
    transition: scale 100ms ease-out;
}

button:active {
    scale: 0.97;
}
```

Three percent doesn't sound like much. But now it feels like something you're physically pushing in, and that's the difference between a picture of a button and a button.

## Hover

Most people settle for making the background a little lighter on hover. That's fine, but think about how you can make it more interesting.

If a button has an icon, that icon can come alive a little when you hover over it. An arrow can nudge a couple of pixels in the direction it's pointing, so it feels like you're already acting on it before you've even pressed. A link that leaves your site can move its arrow up and to the right. A like button's heart can beat once. A chat bubble can give a little wiggle, like it's talking.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">Nudge</span>
        <button class="pm-btn pm-pointer pm-deploy">Deploy <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">Up and out</span>
        <button class="pm-btn pm-pointer pm-docs">View docs <svg class="pm-ico" viewBox="0 0 24 24"><path d="M6.5 17.5 17.5 6.5M8.5 6.5h9v9" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">Beat</span>
        <button class="pm-btn pm-pointer pm-like">Like <svg class="pm-ico" viewBox="0 0 24 24"><path d="M12 20.2S3.2 14.8 3.2 8.9a4.6 4.6 0 0 1 8.8-2.2 4.6 4.6 0 0 1 8.8 2.2c0 5.9-8.8 11.3-8.8 11.3Z" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">Wiggle</span>
        <button class="pm-btn pm-pointer pm-chat">Chat <svg class="pm-ico" viewBox="0 0 24 24"><path d="M7 4h10a4 4 0 0 1 4 4v5a4 4 0 0 1-4 4h-5.5l-5 4 .5-4a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4Z" /></svg></button>
    </div>
</div>

```css
.icon {
    transition: translate 100ms ease-out;
}

.deploy:hover .icon {
    translate: 2px 0;
}

.docs:hover .icon {
    translate: 1px -1px;
}

.like:hover .icon {
    animation: beat 300ms ease-out;
}

.chat:hover .icon {
    transform-origin: 50% 85%;
    animation: wiggle 400ms ease-out;
}

@keyframes beat {
    50% { scale: 1.3; }
}

@keyframes wiggle {
    25% { rotate: -12deg; }
    50% { rotate: 10deg; }
    75% { rotate: -5deg; }
}
```

It's a micro-interaction, not the main show. Keep it small and subtle. If anyone consciously notices the arrow moving, it's moving too far.

## Hit area

On phones and tablets, you press things with your finger instead of a mouse pointer, and that makes really small buttons hard to tap. Even if _you_ can hit them consistently, fingers come in all shapes and sizes, and you don't know whether someone else can just because you can.

If someone fat-fingers it and misses by a few pixels, nothing happens. The app just feels unresponsive.

Apple's [Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/accessibility) recommend that anything you can tap is at least 44 by 44 points. So does that mean making all your buttons bigger, and breaking your design? Not necessarily!

You can add a pseudo-element that makes the hit area stick out past the edges of the button. Give it a minimum width and height of 44 pixels and centre it, and small buttons get a comfortable target while bigger buttons stay exactly as they are.

<div class="pm-demo" x-data="{ show: true, small: 0, big: 0 }" :class="show && 'pm-show-hits'">
    <div class="pm-col">
        <span class="pm-label">Tiny button</span>
        <button class="pm-btn pm-pointer pm-press pm-icon-btn pm-hit" @click="small++" aria-label="Close">
            <svg class="pm-ico" viewBox="0 0 24 24"><path d="M6.5 6.5l11 11M17.5 6.5l-11 11" /></svg>
        </button>
        <span class="pm-count" x-text="`${small} taps`">0 taps</span>
    </div>
    <div class="pm-col">
        <span class="pm-label">Normal button</span>
        <button class="pm-btn pm-pointer pm-press pm-hit" @click="big++">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
        <span class="pm-count" x-text="`${big} taps`">0 taps</span>
    </div>
    <label class="pm-note" style="cursor: pointer">
        <input type="checkbox" x-model="show"> Show the hit areas
    </label>
</div>

Try clicking just outside the tiny button. It still counts. The normal button's hit area is already bigger than 44 pixels, so it doesn't change at all.

```css
@media (pointer: coarse) {
    button {
        position: relative;
    }

    button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        translate: -50% -50%;
        width: 100%;
        height: 100%;
        min-width: 44px;
        min-height: 44px;
    }
}
```

The `pointer: coarse` media query means it only kicks in when someone's using something imprecise, like a finger on a touchscreen, so it doesn't affect desktop users with a mouse. I've left it on in the demo above so you can see it with one.

## Haptics

Phones can do something else, too. Haptics! One call to `navigator.vibrate()` gives a tiny vibration you can physically feel, which tricks your brain into thinking you pressed a real, physical button.

<div class="pm-demo" x-data="{ supported: 'vibrate' in navigator }">
    <div class="pm-col">
        <span class="pm-label">With haptics</span>
        <button class="pm-btn pm-pointer pm-press" onpointerdown="pmSound.buzz()">Submit <svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <p class="pm-note" x-text="supported ? 'Your browser supports this. Tap it on a phone to feel it.' : 'Your browser ignores this one. Try it on an Android phone.'">Try this one on an Android phone.</p>
</div>

```js
button.addEventListener('pointerdown', () => {
    if ('vibrate' in navigator) {
        navigator.vibrate(10);
    }
});
```

This works great on Android. Apple doesn't want us to have any fun on the web though, so it doesn't work on an iPhone.

## State

When a button's clicked, show what it's doing instead of giving people zero feedback. While the work happens in the background, change the label to "Submitting", and when it comes back, change it to "Submitted".

If the new text changes the button's size, transition the width too, so it doesn't just jump and shift everything around it.

Even better, morph the text. Letters that stay the same slide into place, and only the new ones fade in. [Torph](https://torph.lochie.me) does exactly that for you. You can do the same with the icon: morph the arrow into a spinner while the work is happening, then into a tick when it comes back successfully. And you could even play a little success sound when it's done.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">Before</span>
        <button class="pm-btn pm-pointer pm-state" data-pm-state="before"><span class="pm-text">Submit</span><svg class="pm-ico" viewBox="0 0 24 24"><path d="M4 12h15.5M13.5 6l6 6-6 6" /></svg></button>
    </div>
    <div class="pm-col">
        <span class="pm-label">After</span>
        <button class="pm-btn pm-pointer pm-press pm-state" data-pm-state="after" onpointerdown="pmSound.click()"><span class="pm-text">Submit</span><svg class="pm-ico" viewBox="0 0 24 24"><path /><path /></svg></button>
    </div>
</div>

```js
import { TextMorph } from 'torph';

const morph = new TextMorph({ element: label });

const from = button.offsetWidth;
morph.update('Submitting');
const to = button.offsetWidth;

button.animate(
    { width: [`${from}px`, `${to}px`] },
    { duration: 200, easing: 'ease-out' },
);
```

For the icon, [flubber](https://github.com/veltman/flubber) interpolates between any two SVG shapes, so you can morph from one icon to another frame by frame:

```js
import { interpolate } from 'flubber';

const morph = interpolate(arrow, spinner);
const start = performance.now();

requestAnimationFrame(function frame(now) {
    const t = Math.min((now - start) / 300, 1);
    icon.setAttribute('d', morph(t));
    if (t < 1) requestAnimationFrame(frame);
});
```

## Not every button

You don't have to do all of this to every single button in your app. That would be too much, and a settings page where every toggle chirps and wiggles is exhausting.

Save it for the buttons people press less often but that have a big impact. The checkout button. The deploy button. The one that sends the email you've spent an hour writing. That's where a little extra care makes the moment feel satisfying instead of nerve-wracking.

<div class="pm-demo">
    <div class="pm-col">
        <span class="pm-label">All of it</span>
        <button class="pm-btn pm-pointer pm-press pm-deploy pm-hit pm-state" data-pm-state="after" data-pm-labels="Press me|Pressing me|Pressed me" onpointerdown="pmSound.click(); pmSound.buzz()"><span class="pm-text">Press me</span><svg class="pm-ico" viewBox="0 0 24 24"><path /><path /></svg></button>
    </div>
</div>

Pick the buttons that matter, give them a little life, and ==make people want to press them==.

<script type="module">
    // The state demos: label and width changes, with the text and icon morphing.
    let TextMorph = null;
    try {
        ({ TextMorph } = await import('https://esm.sh/torph'));
    } catch {}

    // Each icon is two strokes, sampled to the same number of points, so any
    // icon can morph into any other stroke by stroke.
    const N = 40;
    const resample = (poly) => {
        const lengths = poly.slice(1).map((p, i) => Math.hypot(p[0] - poly[i][0], p[1] - poly[i][1]));
        const total = lengths.reduce((a, b) => a + b, 0);
        return Array.from({ length: N }, (_, k) => {
            let d = (total * k) / (N - 1);
            let i = 0;
            while (i < lengths.length - 1 && d > lengths[i]) d -= lengths[i++];
            const f = lengths[i] ? Math.min(1, d / lengths[i]) : 0;
            return [poly[i][0] + (poly[i + 1][0] - poly[i][0]) * f, poly[i][1] + (poly[i + 1][1] - poly[i][1]) * f];
        });
    };
    const arc = (a0, a1) => Array.from({ length: 30 }, (_, i) => {
        const a = ((a0 + ((a1 - a0) * i) / 29) * Math.PI) / 180;
        return [12 + Math.cos(a) * 8, 12 + Math.sin(a) * 8];
    });
    const ICONS = {
        arrow: [[[4, 12], [19.5, 12]], [[13.5, 6], [19.5, 12], [13.5, 18]]],
        spinner: [arc(-90, 45), arc(90, 225)],
        check: [[[4.5, 12.5], [9.5, 17.5]], [[9.5, 17.5], [19.5, 6.5]]],
    };
    for (const k in ICONS) ICONS[k] = ICONS[k].map(resample);
    const toD = (points) => 'M' + points.map((p) => p.map((v) => v.toFixed(2)).join(' ')).join('L');
    const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);

    function morphIcon(paths, from, to, ms = 300) {
        const start = performance.now();
        requestAnimationFrame(function frame(now) {
            const t = ease(Math.min(1, (now - start) / ms));
            paths.forEach((path, s) => {
                path.setAttribute('d', toD(ICONS[from][s].map((p, i) => [p[0] + (ICONS[to][s][i][0] - p[0]) * t, p[1] + (ICONS[to][s][i][1] - p[1]) * t])));
            });
            if (t < 1) requestAnimationFrame(frame);
        });
    }

    for (const button of document.querySelectorAll('[data-pm-state]')) {
        const fancy = button.dataset.pmState === 'after';
        const [idle, busy, done] = (button.dataset.pmLabels || 'Submit|Submitting|Submitted').split('|');
        const label = button.querySelector('.pm-text');
        const paths = [...button.querySelectorAll('.pm-ico path')];
        let icon = 'arrow';
        if (fancy) paths.forEach((path, s) => path.setAttribute('d', toD(ICONS.arrow[s])));
        const morph = fancy && TextMorph ? new TextMorph({ element: label, duration: 400 }) : null;
        let working = false;

        const set = (text, nextIcon) => {
            const from = button.offsetWidth;
            if (morph) morph.update(text);
            else label.textContent = text;
            if (!fancy) return;
            const to = button.offsetWidth;
            button.animate({ width: [`${from}px`, `${to}px`] }, { duration: 200, easing: 'ease-out' });
            button.classList.toggle('pm-spinning', nextIcon === 'spinner');
            morphIcon(paths, icon, nextIcon);
            icon = nextIcon;
        };

        button.addEventListener('click', () => {
            if (working) return;
            working = true;
            set(busy, 'spinner');
            setTimeout(() => {
                set(done, 'check');
                if (fancy) window.pmSound.success();
            }, 1400);
            setTimeout(() => {
                set(idle, 'arrow');
                working = false;
            }, 3200);
        });
    }
</script>
