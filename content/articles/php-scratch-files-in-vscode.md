---
title: PHP Scratch Files in VSCode
alternate_title:
slug: php-scratch-files-in-vscode
published_at: 2025-04-17
updated_at:
strapline:
synopsis:
previous_article:
next_article:
tags:
    - PHP
    - VSCode
---

Throughout any given day, I find myself writing PHP code in "scratch files" - quick, throwaway scripts that I use to test out a concept or debug a problem.

Usually I just make a new file with <kbd><span x-data x-text="window.navigator.platform.includes('Mac') ? 'CMD' : 'CTRL'">CMD</span>+N</kbd> and don't even save it.

It's quick to set up, but to get rid of a couple of the little "papercuts" that might cost you precious seconds, you can go a step further.

## Code Runner Extension

The [Code Runner](https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner) extension for Visual Studio Code lets you run code snippets or files directly from the editor by going to a file and executing the "Run Code" command (I have this bound to <kbd>CMD+R</kbd>). It will show the output of the script in the output panel in the editor.

<https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner>

The default output is a bit verbose for my liking, so I tweak the settings a little to make it a bit more streamlined. I also never use the selection feature, so I disable that too.

```json
{
    "code-runner.clearPreviousOutput": true,
    "code-runner.enableAppInsights": false,
    "code-runner.showExecutionMessage": false,
    "code-runner.ignoreSelection": true
}
```

That all works great, now you can open a new scratch file and run it on-the-fly with a quick keyboard shortcut. This is perfectly usable for most cases, but I found myself taking a few seconds here and there to deal with a couple of little things that annoyed me each time I noticed, like papercuts.

## Papercut #1 - Opening PHP Tags & Changing Language Mode

When you open a new file in VSCode, it will default to the "Plain Text" language mode. This means that if you start typing PHP code, it won't be syntax highlighted or have any of the other PHP-specific features that you might expect. If you try to run the code, it won't run with PHP because Code Runner doesn't know what to do with it.

If you want, you can set the default language of new files in VSCode, but that's a bit of a blunt instrument, especially if you want your [default language to be something else](/writing-markdown-in-vscode), like me.

```json
{
    "files.defaultLanguage": "php"
}
```

So you need to run `Change Language Mode` then select `PHP` each time? That's a bit of a pain.

Instead, I added a feature to the TemPHPest extension that will detect PHP code at the start of a scratch file and set the language automatically. Now you can just start typing PHP code, it'll switch the language for you, and you can run it straight away!

![TemPHPest extension setting the language](/images/articles/auto-php-scratch-file.gif)

## Papercut #2 - Autoloading Project Files

Often when I write scratch files, I'll use classes from a project that I'm working on.

The Code Runner extension works by saving the file to the project root temporarily, running the file, then deleting it when it's done. This means that you can include the autoloader from your project and get the full benefits of running the code in the context of your project.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Access your project's autoloaded classes here
```

This is easy to do with a snippet if you find yourself doing it all the time - but it's still an extra step for such a common use case.

The [exec command from cpx](/cpx-exec-scratch-file-runner) helps solve this. It will automatically detect and use Composer's autoloader if it exists in the current directory or a parent directory. The Code Runner extension lets us configure which command to run for each language, so we can use this package to run our PHP scratch files instead once it's installed.

```json
{
    "code-runner.executorMap": {
        "php": "cpx exec"
    }
}
```

Now we can reference our project's autoloaded files for free!

## Papercut #3 - Class Aliasing

We still need to make sure to import the right namespaces for the classes we reference. If we forget, we'll get an error when we run the file. Laravel's [Tinker](https://github.com/laravel/tinker) command has a neat feature where, if a class namespace isn't found, it will attempt to find a class in the autoloader that matches the class name, and set it up as an alias it for you.

The [cpx exec command](/cpx-exec-scratch-file-runner) can also do this out-of-the-box if it can find an unambiguous match in your application code - so with no additional configuration we can reference some of our project's classes without worrying about the namespace.

```php
<?php

$user = new User();
$user->name = 'Liam';

dd($user->getAttributes());

// Aliasing 'User' to 'App\Models\User'
//
// array:1 [
//  "name" => "Liam"
// ]
```

## Conclusion

With a couple of minutes of setup, [Code Runner](https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner), [TemPHPest](https://marketplace.visualstudio.com/items?itemName=liamhammett.temphpest), and [cpx](/cpx-exec-scratch-file-runner) are installed and lets us make and run PHP scratch files in _seconds_. This might not feel like much, but when you do it a dozen times per day ==it feels like a superpower.==
