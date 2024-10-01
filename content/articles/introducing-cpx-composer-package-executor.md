---
title: Introducing cpx - Composer Package Executor
alternate_title:
slug: introducing-cpx-composer-package-executor
published_at: 2024-10-01
updated_at:
strapline: npx but for PHP
synopsis: Run Composer packages, effortlessly
previous_article:
next_article:
tags:
    - PHP
---

The Node ecosystem has long had `npx`, an almost universally used tool that does one thing well - it lets you invoke a script from an `npm` package without needing to install it globally or into your project.

What if the PHP ecosystem had something like this, but for invoking scripts from Composer packages?

Introducing cpx - or "Composer Package Executor".

You can install `cpx` onto your system like any other global Composer package initially (don't worry about conflicts, it has no dependencies):

```bash
composer global require cpx/cpx
```

Then you can go on to use it like you might expect, passing through the package name, command from the package you want to run, and any extra arguments.

Want to run the `php-cs-fixer` command from the `friendsofphp/php-cs-fixer` package?

```bash
cpx friendsofphp/php-cs-fixer php-cs-fixer fix ./src
```

What if the package only has one script, do you still need to include the command name? Nope! Omitting the command name if it's unambiguous will work too.

```bash
cpx friendsofphp/php-cs-fixer fix ./src
```

For a handful of popular packages, you can even omit the vendor name and use an alias for common tasks.

```bash
cpx php-cs-fixer fix ./src
cpx laravel new ProjectName
cpx psalm
```

What if you're working on different projects that use different code formatters, static analysers or testing frameworks and you have to remember the right command and arguments for it? Use `cpx format`, `cpx check` and `cpx test` respectively - it will figure out what tool is being used for the current project and run that one for you.

cpx does a whole lot more that makes working with the PHP ecosystem a breeze, check out the docs and readme for a full list of commands!

<https://cpx.dev>
