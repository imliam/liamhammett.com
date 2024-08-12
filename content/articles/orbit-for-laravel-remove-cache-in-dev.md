---
title: Orbit for Laravel - Remove Cache in Dev
slug: orbit-for-laravel-remove-cache-in-dev
published_at: 
strapline: Cache got your tongue?
synopsis: Effortlessly handle Laravel Orbit cache clearing in development
tags:
    - PHP
    - Laravel
---

The [Orbit](https://github.com/ryangjchandler/orbit) package for Laravel, created by Ryan Chandler, is a great little utility - it allows you to have Eloquent models powered by a flat file system instead of needing a real database.

There's a caveat though - it _actually_ uses a real database behind the scenes - an SQLite one. It makes this transparent to someone developing with the package, but it does mean that once the database is created, it doesn't get rebuilt unless you explicitly tell it to.

This lead to some confusion for me when I was working on a local development environment, writing and changing content quite frequently...

<x-thought>Why aren't my changes reflecting when I refresh the page?!</x-thought>

Luckily, there's an easy solution to this - clear the cache. You can do this by running the `artisan orbit:clear` command, but that's a bit of a pain to remember to do every time you make a change.

Of course, there's a famous quote that comes to mind here:

<x-quote name="Phil Karlton" class="max-w-64">There are only two hard things in Computer Science: cache invalidation and naming things.</x-quote>

So instead, I added a little snippet to my `AppServiceProvider` to call the same function under-the-hood whenever the app is running in the `local` environment.

```php{}{8-10}
use Orbit\Actions\ClearCache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->environment('local')) {
            (new ClearCache())();
        }
    }
}
```

With this one little tweak, there's no need to clear the cache as I'm writing or making changes in development again, which makes me a happy coder!
