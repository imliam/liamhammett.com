---
title: OmARCase
alternate_title:
slug: omarcase
published_at: 2025-07-09
updated_at:
strapline: Why Arc's "Omakase" Philosophy Worked
synopsis:
previous_article:
next_article:
tags:
    - Opinion
---

Recently, the company behind the [Arc browser](https://arc.net/) announced they're no longer working on Arc, shifting focus to a [new browser, Dia](https://www.diabrowser.com/) - and that understandably ruffled some feathers.

But forget the drama - let's talk about what Arc got _absolutely right_: ==Omakase==

<https://www.youtube.com/watch?v=D-QjPYBRnno>

## What Is Omakase?

"Omakase" is a Japanese phrase that roughly translates to "I'll leave it up to you." You hear it in restaurants - it's the chef's choice. You trust them to serve you something great without picking from the menu yourself.

And oddly enough, that idea translates **beautifully** to software.

David Heinemeier Hansson often talks about building software this way, to the point where he's named [a tool directly after it](https://omakub.org/), but it's evident in all the products they release.

<x-quote name="David Heinemeir Hansson" title="Rails is omakase" source-url="https://dhh.dk/2012/rails-is-omakase.html">Rails is omakase. A team of chefs picked out the ingredients, designed the APIs, and arranged the order of consumption on your behalf according to their idea of what would make for a tasty full-stack framework. The menu can be both personal and quirky. It isn't designed to appeal to the taste of everyone, everywhere.</x-quote>

The idea is simple: as the developer, **_you_ are the expert**. Be opinionated, **make smart decisions _for_ your users**, and don't overwhelm them with choices. Give them a curated experience that feels cohesive and intentional.

<x-thought>The best products don't do everything - they do one thing **beautifully**</x-thought>

## Arc's Omakase Philosophy in Action

Arc didn't try to be everything for everyone. It made deliberate design decisions and stuck to them - even when they were controversial.

1. **Sidebar Tabs** - Tabs lived in a sidebar. That was it. No switching to a horizontal tab strip like every other browser. No "classic layout" toggle. It was sidebar or bust. Sure, people grumbled, some people didn't even give Arc a chance because of it, but it was a bold choice that defined the product.

2. **No Empty Tabs** - Arc didn't give you a blank "new tab" landing page. If you pressed <kbd><x-keyboard-cmd />+T</kbd> to open a new tab and didn't immediately type a URL or perform a search, it didn't really open a new tab for you. No clutter, no zombie tabs left open when you changed your mind - it _forced_ you to do something in-the-moment.

3. **Tabs Auto-Archived** - Arc didn't let your tabs live forever. If you didn't interact with one after a certain time, it was "archived". You could tweak the timing (e.g. from 24 hours to 30 days), but the philosophy was clear - tab hoarding is a problem, and Arc _forced_ you to deal with it.
    - For me, this was the biggest culture shock. My Chrome windows often had tabs that were months old, but in Arc, I rarely had more than 10 open at a time. When a tab was auto-archived, I barely noticed - and that's the point. If I hadn't touched it in a week, I probably wasn't going back soon, even if I thought I would.

4. **Small Features, Seamlessly Integrated** - Arc was filled with thoughtful, subtle features - especially around AI. One of my favorites: when you pressed <kbd><x-keyboard-cmd />+F</kbd> to search on a page, if your query returned zero results, Arc would offer to "ask AI" instead - using the page's content as context. No new shortcuts to learn. Just an elegant fallback. It was genius.

5. **Opinionated Defaults, Not Exhaustive Settings** - Some features, like auto-renaming downloaded files or collapsing long tab titles, didn't always work perfectly. But Arc made those calls intentionally - and when necessary, let you turn them off. It was a thoughtful balance.

## When Omakase Gets Diluted

Now that Arc has stopped evolving, a new open-source project called [Zen Browser](https://zen-browser.app/) has appeared to pick up where it left off. Zen is based on Firefox and very intentionally mirrors Arc's interface.

But **it doesn't hit the mark** like Arc did, and I think that's because it offers too much choice.

Zen greets you with hundreds of settings. Want to move the sidebar to the other side? Go for it. Want to have 30 pinned tabs? Sure! Want to disable every design decision Arc made? You can.

And that's the problem.

When you offer options for every little thing, your software starts to feel less like a cohesive product and more like a pile of preferences. The simplicity gets lost. Bugs multiply because you now need to account for every possible setting combination.

This is where I think Zen starts to fall apart. Even basic design interactions - like hover previews or dropdown animations - can get janky depending on how you've configured things. Spacing between elements _feels off_, and the overall experience becomes inconsistent - it feels like a collection of features rather than a well-crafted experience.

This is the antithesis of omakase. Instead of a curated, intentional experience, you get a buffet of options that dilutes the original vision.

Sure, some power users love this level of control. But it sacrifices the elegance and integrity of an opinionated experience. And worse, it shifts responsibility for good UX from the designer to the user.

## Omakase Is a Superpower

When you build software omakase-style, you're betting on trust. You're saying, "We know what a good experience looks like, and we're giving it to you".

It's easier to test. Easier to onboard. **Easier to love.**

And Arc proved that people will embrace constraints - as long as they're thoughtful, useful, and coherent.

==Software doesn't need to cater to every taste. It just needs a good chef.==

So build it omakase.

Make bold choices. Stand by them.

And serve something worth savouring.
