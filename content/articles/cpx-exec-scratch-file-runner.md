---
title: cpx exec - Scratch File Runner
alternate_title:
slug: cpx-exec-scratch-file-runner
published_at: 2025-03-21
updated_at:
strapline: The Perfect Companion
synopsis: Use cpx exec to invoke scratch files and get a bunch of benefits
previous_article:
next_article:
tags:
    - PHP
    - Laravel
    - VSCode
---

`cpx exec` is a command that ships with the [`cpx`](https://cpx.dev/) package which just... runs PHP files.

It does a few things ontop of the regular PHP file execution that make it ==awesome== for working with scratch files. You can install it using Composer like any other global package by running `composer global require imliam/cpx` and then benefit from the exec command it provides.

But why use `cpx exec ./file.php` over plain old `php ./file.php`?

<https://cpx.dev/>

## Automatic Autoloaders

When you run a PHP file, if you want to use any of your project's code, somewhere you need to require your autoloader `require_once __DIR__ . '/vendor/autoload.php';`

This is a bit of a pain to do in scratch files - you just want to write and run some code quickly and not have to worry about this boilerplate all the time. You can set up a snippet to do this, but it's better if you don't have to think about it.

**`cpx exec` looks for an `autoload.php` file and includes it for you.**

## Laravel Bootstrapping

If the directory the autoloader was found in also happens to be a Laravel project, `cpx exec` will include the Laravel bootstrap code - setting up service providers and giving you a real Laravel app state to work with.

## Class Aliasing

If a class is used in the scratch file but the namespace isn't imported, `cpx exec` will try to find an appropriate namespaced class out of the autoloaded files that matches the same name, and use it for you.

Now you can just type `User::create()` instead of `\App\Models\User::create()` or importing a full namespace into your use statements. Laravel Tinker does this, and it's a great little time saver.

## Require Composer Packages Dynamically

cpx's whole schtick is that it makes running Composer packages easier - and this extends to the `cpx exec` command too.

A new function, `composer_require('vendor/package')`, is available in files run with `cpx exec`. When this function is invoked, the given packages will be installed into by Composer into a temporary directory and their autoloader invoked.

This means if you want to try out a package that's not in your project, you can give it a test quickly and without needing to install it into a project and figure out dependency conflicts, just to undo it if you figure out the package isn't what you wanted.

## Use cpx exec For Scratch Files in VSCode

If you use either the [TemPHPest REPL](https://marketplace.visualstudio.com/items?itemName=liamhammett.temphpest) or [Code Runner](https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner) extensions for VSCode, they will run this file in your project's root directory, so if you configure them to use `cpx exec` as the package executor, you get your project's files autoloaded with no extra effort.

```json
{
  "code-runner.executorMap": {
    "php": "cpx exec",
  },
  "temphpest.phpReplBinaryPath": "cpx exec"
}
```

I use scratch files all the time, and this has helped me save a bunch of time every day when I'm trying out code, so hopefully it helps you too!

<https://cpx.dev/>
