---
title: PHP Scratch File Runner
alternate_title: 
slug: php-scratch-file-runner
published_at: 2024-09-02
updated_at: 
strapline: 
synopsis: Run PHP scratch files and save a few seconds here and there
previous_article: 
next_article: 
tags:
    - PHP
---

=="Run PHP"== is a script to help run PHP scratch files when writing quick-and-dirty code. It has a couple of extra features over running a plain PHP file that can save a little bit of time.

1. **Automatic Autoloading** - When running a PHP file, it will automatically detect and use Composer's autoloader if it exists in the current directory or a parent directory
2. **Class Aliasing** - If a class is used in the file that doesn't exist, it will attempt to alias it to a class in the autoloader, just like [Laravel Tinker](https://github.com/laravel/tinker)

You can install the package via Composer:

```bash
composer global require futureplc/run-php
```

To use, just call the command and pass through a file path as a single argument:

```bash
run-php /path/to/file/to/run.php
```

With this, we can run a file, reference classes and functions in our app without needing to import the autoloader, and not thinking about namespaces most of the time because they'll be aliased for us.

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

Check it out here:

<https://github.com/futureplc/run-php>
