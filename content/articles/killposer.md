---
title: killposer
alternate_title:
slug: killposer
published_at: 2026-05-27
updated_at:
strapline: Reclaim disk space from Composer vendor directories
synopsis: killposer is a small interactive CLI tool to find and delete Composer vendor directories from projects you're not actively working on
previous_article:
next_article:
tags:
    - PHP
---

If you've got dozens of PHP projects checked out and haven't touched most of them in months, their `vendor` directories are quietly eating your disk. Mine were eating about 20GB of it.

I built [killposer](https://github.com/imliam/killposer) to fix that, inspired by [npkill](https://npkill.js.org/) for the Node ecosystem. It scans for Composer `vendor` directories, surfaces the last-touched date and size of each one, then lets you interactively pick which to delete.

It's easy to install:

```bash
# Install globally with Composer and run it
composer global require imliam/killposer
killposer ~/projects

# Alternatively, use CPX to run it immediately
cpx imliam/killposer ~/projects
```

And even easier to use:

```
  Scanning for Composer projects... ✓

┌ Select vendor directories to delete ──────────────────────────────┐
│ › ◼ my-app                     · 48.3 MB · 2025-11-02                │
│   ◻ old-api                    · 31.1 MB · 2024-06-14                │
│   ◻ legacy-plugin              · 12.7 MB · 2023-09-30                │
└───────────────────────────────────────── Space to toggle, Enter ─┘

  Delete 1 vendor directory totalling 48.3 MB? No / Yes

  Deleted /home/user/projects/my-app/vendor
```

The interactive selection matters. A blind `find . -name vendor -type d -exec rm -rf {} +` would nuke everything - including projects you're actively working on. killposer gives you the context to make that call.

It's written in PHP, which means PHP is now helping you delete PHP - pretty meta.

<https://github.com/imliam/killposer>
