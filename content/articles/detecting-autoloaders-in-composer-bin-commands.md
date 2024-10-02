---
title: Detecting Autoloaders in Composer Bin Commands
alternate_title:
slug: detecting-autoloaders-in-composer-bin-commands
published_at: 2024-10-02
updated_at:
strapline:
synopsis:
previous_article:
next_article:
tags:
    - PHP
---

If you're building a command line application you want to distribute with Composer, like I just did with [cpx](https://cpx.dev), what path do you require to get the Composer autoloader so your code works?

It might seem like a simple question, but depending on where your CLI is being run from, the path to the autoloader will be different:

1. If the CLI from its own repository (eg. for development of the CLI itself), you probably want to require `vendor/autoload.php`
2. If the CLI is being run from the `vendor/<vendor>/<package>` directory of a project, you probably want to require `../../autoload.php`
3. If the CLI is being run from the `vendor/bin` directory of a project, Composer will set a variable in `$GLOBALS['_composer_autoload_path']` that you can use

Taking all of this into account, here's the code I use in `cpx` to detect the autoloader so it'll work in all scenarios:

```php
if (isset($GLOBALS['_composer_autoload_path'])) {
    require_once $GLOBALS['_composer_autoload_path'];

    unset($GLOBALS['_composer_autoload_path']);
} else {
    foreach ([__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php'] as $file) {
        if (file_exists($file)) {
            require_once $file;

            break;
        }
    }

    unset($file);
}
```
