---
title: pm-guard
alternate_title:
slug: pm-guard
published_at: 2026-05-23
updated_at:
strapline: Stop the wrong package manager sneaking into your project
synopsis: pm-guard detects which Node package manager a project uses and blocks foreign lock files from landing in CI or a pre-commit hook.
previous_article:
next_article:
tags:
    - JavaScript
---

We have four Node package managers now. npm, Yarn, pnpm, Bun - all alive, all producing their own lock files. On any given project, exactly one of them is right.

I watched someone run `npm install` in a project that relies on pnpm. A `package-lock.json` turned up in the PR. Not a catastrophe, but a mess - and the kind of thing that quietly erodes trust in a repo's tooling over time.

I built [pm-guard](https://github.com/imliam/pm-guard) to catch it. Drop it in CI and it'll exit 1 if a foreign lock file is detected:

```bash
npx pm-guard
```

It infers the intended package manager from the `packageManager` field in `package.json`, or from whichever lock file is already present. If you want to be explicit:

```bash
npx pm-guard --expect bun
```

There's also a `clean` command if you've already got conflicting lock files and want to sort it out interactively.

```bash
npx pm-guard clean
```

That's it. One less thing to catch in review.

<https://github.com/imliam/pm-guard>
