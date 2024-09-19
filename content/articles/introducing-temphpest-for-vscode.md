---
title: Introducing TemPHPest for VSCode
alternate_title: 
slug: introducing-temphpest-for-vscode
published_at: 2024-09-10
updated_at: 
strapline: Let your PHP papercuts be healed
synopsis: 
previous_article: 
next_article: temphpest-for-vscode-showcase
tags:
    - PHP
    - VSCode
---

"Why does any self-respecting PHP developer use VSCode?! It's just not as good as PHPStorm."

This is a sentiment we hear a lot in the PHP community, and it's extremely unfortunate that the gatekeeping is so loud when PHP has had to stave off the gatekeeping that comes from other circles in the wider programming community. PHPStorm is a _wonderful_ piece of software, but so is VSCode (and Vim, and any other editor/IDE out there).

In the [Laracon US 2024 Keynote](https://www.youtube.com/watch?v=AwWepVU5uWM), the Laravel team announced a new first-party package for Laravel in VSCode. This extension has a lot of features that make working with Laravel in VSCode a lot easier, and it's a great step forward for the PHP community.

But what about the other little papercuts that PHP developers face when working in VSCode?

Let's try to fix that, bit by bit. If VSCode does one thing not just well - but knocks it out of the park - it's extensibility. So, I'm writing an extension to improve the PHP experience in VSCode.

## In comes TemPHPest

TemPHPest is an extension for VSCode that I've been working on to bring nice little tweaks and features to improve working with PHP. Each feature could probably be a separate extension, but being bundled in one is easier for me to maintain and you to install.

I'd recommend checking out the VSCode Extension Marketplace page, as it will always have the most up-to-date information on what the extension can do.

<https://marketplace.visualstudio.com/items?itemName=liamhammett.temphpest>

Some of my favourite highlights:

![New PHP files are filled out with stubs as you would expect. There's also rich Laravel support!](https://res.cloudinary.com/liam/image/upload/v1725928075/file-template-with-namespace.gif)

![Code actions and quick fixes let you quickly whip your code into shape!](https://res.cloudinary.com/liam/image/upload/v1725928075/code-actions.gif)

![The REPL runs your code and shows you the results inline](https://res.cloudinary.com/liam/image/upload/v1725928075/repl.gif)

![Trying to interpolate a value within single quotes will fix it to double quotes automatically](https://res.cloudinary.com/liam/image/upload/v1725928075/auto-interpolation.gif)

![Heredoc strings finally can render Laravel Blade syntax highlighting](https://res.cloudinary.com/liam/image/upload/v1725928075/blade-heredoc.png)

What are you waiting for? Check it out now!

<https://marketplace.visualstudio.com/items?itemName=liamhammett.temphpest>