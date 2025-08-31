---
title: Keep It Simple, Stupid!
alternate_title:
slug: keep-it-simple
published_at: 2025-09-01
updated_at:
strapline: Why dumb software is often the smartest move
synopsis:
previous_article:
next_article:
tags:
    - Engineering
    - Opinion
---

I've been building a new little tool lately. I've wanted this for a while but didn't know what shape it would take until a couple of days ago. And then in a moment of inspiration, it clicked.

I figured out exactly what I wanted - and in doing so, a lesson I've known for years hit me harder than ever:

==Keep it simple, stupid!==

## A Really Dumb Formatter

The tool I built is a code formatter - but not like Prettier or PHP-CS-Fixer or Go's fmt command. It's not language-specific. It's not deeply opinionated. In fact, it's not even particularly clever.

Those tools have complex logic to understand the structure of a language. They parse the code into an Abstract Syntax Tree (AST) or tokens, then make decisions based on that structure. They know about edge cases, syntax rules, and conventions specific to that language.

**So what?** Did I build all that?

Nope, with my tool, you give it a file and it formats the file's contents based on a config.

That config file? Just a bit of JSON. A list of start and end patterns; regexes, strings, whatever, and a configured indentation style. That's it.

```json
{
    "indentation": "    ",
    "rules": [
        {
            "start": "\[$",
            "end": "^\s+?\]"
        }
    ]
}
```

It doesn't need to know about PHP or HTML or Python. It doesn't need to understand ASTs or token trees. Under the hood, it's just a for-loop, some regex, and a little bit of state.

It's _stupidly simple_. And it works.

Sure, if you need something with in-depth knowledge of a language to make very specific conditional decisions, you'll want a dedicated formatter. But if you're working with an obscure file format, or just something that doesn't have great dedicated tooling yet, this gets the job done.

## Tailwind Did It First

This idea that "dumb" tools can be wildly effective isn't new.

Take Tailwind CSS, for example.

Back in the day? It was _stupidly simple_.

It generated _every class it could_ - `.p-1`, `.p-2`, `.p-3`, all the way up to `.p-128` or whatever. It didn't care if you used them or not. It built the whole set, even if it ended up being 50MB of mostly unused styles.

And _then_ it used [PurgeCSS](https://purgecss.com/) to sweep through and delete whatever wasn't referenced in your templates. The result? You'd go from a 50MB stylesheet to a few hundred kilobytes.

That sounds inefficient. But it was brilliant.

You didn't have to think about it. You didn't need to configure anything. Tailwind just worked - because it kept things dumb and simple. And it worked long enough to get Tailwind off the ground, into production, and eventually into profitability.

**So what?** Is it still like that?

Once it proved itself and they had the resources, the Tailwind team rebuilt it. They made it smarter. More efficient. Now [it uses a Rust-based toolchain](https://tailwindcss.com/blog/tailwindcss-v4-alpha) to efficiently generate only the styles that are needed and bring more functionality that wasn't previously possible like dynamic values, and it's quick!

But they didn't _start_ there.

## One Line of Code

At work, we had a similar situation.

A few years ago, we introduced the idea of "live blogs" to a platform. Editors could post updates, and we wanted users to see those updates appear on the frontend in near real-time.

The "right" way to do this? Something like Server-Sent Events (SSE). Real-time architecture. The backend tells the frontend when there's new content to show. Doing it right with <abbr title="Server-Sent Events">SSE</abbr> meant new infrastructure, orchestration between services, and keeping long-lived connections open at scale. In short: _a lot_.

**So what?** We wanted to get something out of the door quickly, did we have to do all this?

No. We used one _stupidly simple_ line of HTML:

```html
<meta http-equiv="refresh" content="60" />
```

This one line of code is enough to tell any browser to automatically refresh the page after 60 seconds. Then after another 60. Then again.

And browsers are smart, too! They remember your scroll depth when you refresh, so the user might see a slight flicker after 60 seconds, but they'll basically be reading uninterrupted.

It seems inefficient, but with all the page assets already cached in the user's browser, and the main web page served by a caching CDN that was already part of our infrastructure, it wasn't really that bad.

**And it worked!**

It wasn't perfect - it was obviously inefficient, and it blocked certain kinds of content being included in the page. Try watching a YouTube video where every 60 seconds the page refreshes and you lose track of where you were because it starts at the beginning.

You can't expect perfection when you put in 0.1% of the effort. But you _can_ appreciate the simplicity.

Later, we came back to revisit it and see if it was worth going the <abbr title="Server-Sent Events">SSE</abbr> route, but instead opted for a different progressive enhancement.

In comes Javascript! We went from 1 line of HTML to 50 lines of Javascript. Every 60 seconds we could poll an endpoint to see if there's actually new content to display on the page, then dynamically insert it. Same HTTP request every minute, but we could even make it better.

- No interrupting the user when performing an action like watching a video
- We can poll less frequently, eg. if the live blog is over, or if the user's focus isn't on the window with the live blog, so our CDN gets hit less
- Give the user notifications when there is a new post, and let them scroll it into view

Maybe at some point we'll revisit it again and see if <abbr title="Server-Sent Events">SSE</abbr> is appropriate, but for now 50 lines of code is more maintainable than 5,000.

## KISS

The moral of the story is one that Voltaire touted centuries ago and is still relevant to this day:

<x-quote name="Voltaire">Perfect is the enemy of good</x-quote>

You don't need to start with a perfect solution. Start with one that just does the job, even if it seems stupid at the time, and move on. You can always come back and improve it later, when you know it's worth it.

Don't over-engineer. Don't chase perfection. Start dumb, start simple, and you'll get further faster.

Keep it simple, stupid!

<https://braindump.transistor.fm/episodes/keep-it-simple-stupid>
