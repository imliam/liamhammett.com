---
title: Laravel Set .env File Variable
slug: laravel-set-env-file-variable
published_at: 2018-06-13
strapline: ""
synopsis: ""
tags:
    - PHP
    - Laravel
---

Need a quick way to set an environment variable from the command line that'll save it to the Laravel `.env` file?

Here's a command to do just that.

<https://github.com/imliam/laravel-env-set-command>

When running the `env:set` artisan command, you must provide both a key and value as two arguments.

```bash
php artisan env:set app_name Example

# Environment variable with key 'APP_NAME' has been changed from 'Laravel' to 'Example'*
```

You can also set values with spaces by wrapping them in quotes.

```bash
php artisan env:set app_name "Example App"

# Environment variable with key 'APP_NAME' has been changed from 'Laravel' to '"Example App"'*
```

The command will also create new environment variables if an existing one does not exist.

```bash
php artisan env:set editor=vscode

# Environment variable with key 'EDITOR' has been set to 'vscode'*
```

Instead of two arguments split by a space, you can also mimic the `.env` file format by supplying `KEY=VALUE`.

```bash
php artisan env:set app_name=Example

# Environment variable with key 'APP_NAME' has been changed from 'Laravel' to 'Example'*
```

The command will do its best to stop any invalid inputs.

```bash
php artisan env:set @pp_n@me Laravel

# Invalid environment key. Only use letters and underscores*
```

<https://github.com/imliam/laravel-env-set-command>
