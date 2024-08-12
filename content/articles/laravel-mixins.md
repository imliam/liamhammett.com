---
title: Laravel Mixins
slug: laravel-mixins
published_at: 2020-01-20
strapline: ""
synopsis: ""
tags:
    - PHP
    - Laravel
---

Have you ever wished that a PHP class had another method on it that you'd like to use? Laravel makes this dream come true through its concept of "Macroable" classes.

`Macroable` itself is the name of [a trait Laravel comes with](https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Traits/Macroable.php) that is applied to many of the framework's own classes.

This trait allows you to call a static "macro" method at runtime to add a new method to the class by executing a closure. Behind the scenes, it will use the magic `__call()` and `__callStatic()` methods PHP provides to make the method work as if it were really on the class.

```php
Collection::macro('fill', function ($value) {
    return $this->map(fn () => $value);
});

$collection = new Collection(['a', 'b', 'c']);

$collection->fill('x')->all(); // ['x', 'x', 'x']
```

If you want to learn more about macros, [check out this post by Tighten](https://tighten.co/blog/the-magic-of-laravel-macros) who explain it in more detail.

## Mixins

The `Macroable` trait also provides another, lesser-known method: `mixin`.

This method does a similar thing, but instead allows you to register multiple methods at a time by using a "mixin class".

A mixin class is a class whose methods all return a closure - each closure being an individual macro. The method's name will be assumed as the name for the macro, too.

```php
class CollectionMixin
{
    public function fill()
    {
        return function ($value) {
            return $this->map(fn () => $value);
        };
    }
}

Collection::mixin(new CollectionMixin());
```

This can be a great way to deal with adding a lot of mixins to a class at once, keeping them all together instead of splitting macros out into separate places.

## Finding Macros / Mixins

One of the most common complaints about macros and mixins is their discoverability.

Because it's impossible for an IDE to understand macros as they are resolved at runtime, they can't offer any method suggestions, parameter name hints, or clicking to go to the definition of the method.

Some tools such as [laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper) try to add IDE support for these, but [it's not reliable](https://github.com/barryvdh/laravel-ide-helper/issues/531), and sadly, almost never deals with mixin classes.

In an attempt to mitigate this as much as possible, it's of paramount importance to make your mixin classes as discoverable as possible, and not scatter them about your codebase unpredictably.

Try to keep them in a namespace named `Mixins` or `Macros` and make the class names close to the original class, so they're easy to discover.

## Typehinting `$this`

Since the closures returned from mixin methods are bound into the real class, `$this` is available as if it were the real class.

Unfortunately, IDEs and other tools don't really know this, as macros and mixins are not a PHP language feature, but instead are more of a hack implemented by Laravel itself.

Laravel introduced the `@mixin ClassName` annotation for cases like this, where it's intended to denote that all of the `ClassName` methods and properties are available on the class the docblock is applied to. PHPStorm understands this, however the [intelephense language server does not](https://github.com/bmewburn/vscode-intelephense/issues/123) at the time of writing.

Intelephense does, however, let you use the `@var ClassName $this` inline annotation to achieve the same thing, although [PHPStorm does not](https://intellij-support.jetbrains.com/hc/en-us/community/posts/206377079-Special-case-for-var-this).

This means that for most commonly used IDEs to understand what `$this` means and provide proper suggestions and to not complain, both of these annotations need to be added:

```php
/** @mixin \Illuminate\Support\Collection */
class CollectionMixin
{
    public function fill()
    {
        return function ($value) {
            /** @var \Illuminate\Support\Collection $this */
            return $this->map(fn () => $value);
        };
    }
}
```

## Psalm

Understandably, the static analysis tool [Psalm](http://psalm.dev/) does not understand mixin classes either - and Psalm is a great tool that should be run on the entire project if possible to prevent issues for us.

To stop Psalm from complaining about the properties and methods it doesn't understand, while still maintaining all the other benefits it gives, we can just suppress the problematic errors:

```php
/**
 * @psalm-suppress UndefinedMethod
 * @psalm-suppress UndefinedThisPropertyFetch
 * @psalm-suppress UndefinedThisPropertyAssignment
 */
class CollectionMixin { ... }
```

## Registering Mixins in a Service Provider

If you ever grow to have more than a couple of mixin classes in a project, it can start to get a bit much to keep the logic to register them in your `AppServiceProvider`.

Like anything else that gets too much for one service provider, we can make a new service provider just to handle this, keeping it short, sweet and easy to understand where the mixins live.

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MixinServiceProvider extends ServiceProvider
{
    /** @psalm-var array<class-string, class-string> */
    protected array $mixins = [
        \Illuminate\Support\Collection::class => \App\Mixins\CollectionMixin::class,
        \Illuminate\Database\Query\Builder::class => \App\Mixins\QueryBuilderMixin::class,
        \Illuminate\Routing\UrlGenerator::class => \App\Mixins\UrlGeneratorMixin::class,
        \Illuminate\Database\Eloquent\Relations\HasMany::class => \App\Mixins\HasManyMixin::class,
        \Illuminate\Database\Schema\Blueprint::class => \App\Mixins\BlueprintMixin::class,
        \Carbon\Carbon::class => \App\Mixins\CarbonMixin::class,
    ];

    /** @psalm-var array<class-string, class-string> */
    protected array $testingMixins = [
        \Illuminate\Foundation\Testing\TestResponse::class => \App\Mixins\TestResponseMixin::class,
        \Laravel\Dusk\Browser::class => \App\Mixins\DuskBrowserMixin::class,
    ];

    public function register()
    {
        foreach ($this->mixins as $class => $mixin) {
            $class::mixin(new $mixin);
        }

        if ($this->app->environment('testing')) {
            foreach ($this->testingMixins as $class => $mixin) {
                $class::mixin(new $mixin);
            }
        }
    }
}
```

This structure is simple and lets us easily see the mapping of what original class should get which mixin applied to it, as well as separating out mixins that are for classes that may only be available in a testing or development environment.

We also used the [`class-string`](https://psalm.dev/docs/annotating_code/type_syntax/scalar_types/#class-string) annotation for [generic arrays](https://psalm.dev/docs/annotating_code/type_syntax/array_types/#generic-arrays) that Psalm provides, so that our static analysis always understands that these values should be classes.

*You may have noticed that `Carbon`, while not a Laravel file, is macroable. Carbon also supports macros and mixins out-of-the-box!*

## Conclusion

Not everyone likes macros and mixins, and that's understandable - but if you do decide to use them in your own projects, hopefully now you'll understand some of the issues they bring up, how to deal with them, and maybe even learn to love them on the way.