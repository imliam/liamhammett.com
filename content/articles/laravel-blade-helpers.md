---
title: Laravel Blade Helpers
slug: laravel-blade-helpers
published_at: 2018-11-29
strapline: ""
synopsis: ""
tags:
    - Laravel
---

Laravel's [Blade templating engine](https://laravel.com/docs/5.7/blade) offers a ton of convenient directives you can use to make your view files beautiful and abstract anything that may be too complex or verbose to live inside HTML. It even gives a really handy way to add your own custom directives using the `Blade::directive(...)` method.

However, the callback in custom directives only receives a single parameter - the raw string expression from the view file. It seems to be rare that developers actually parse the contents of the expression itself within the directive, opting instead to pass the entire expression as arguments to a helper function or a method on another class. For example:

```php
BladeHelper::directive('uppercase', function($expression) {
    return "<?php echo strtoupper($expression); ?>";
});
```

As this seems to be the most common use case, I put together a package that attempts to help make these helper functions that little bit easier to define without the boilerplate of returning the string or having to consider what an expression may be when creating a directive.

<https://github.com/imliam/laravel-blade-helper>

The package introduces a new class with a method to define helper functions as Blade directives in much more convenient ways.

This method accepts two arguments; the first is the name of the directive, and the second is the function that the directive should call:

```php
// Define the helper directive
BladeHelper::directive('uppercase', 'strtoupper');

// Use it in a view
@uppercase('Hello world.')

// Get the compiled result
<?php echo strtoupper('Hello world.'); ?>

// See what's echoed
"HELLO WORLD."
```

If no second argument is supplied, the directive will attempt to call a function of the same name:

```php
// Define the helper directive
BladeHelper::helper('join');

// Use it in a view
@join('|', ['Hello', 'world'])

// Get the compiled result
<?php echo join('|', ['Hello', 'world']); ?>

// See what's echoed
"Hello|world"
```

The second argument can also take a callback. The advantage of a callback here over the typical `Blade::directive(...)`method Laravel offers is that the callback given can have specific parameters defined instead of just getting raw expression as a string. This brings several advantages to the process of creating a Blade helper directive:

- Typehint the arguments for the callback
- Manipulate and use the individual arguments when the directive is called, instead of the raw expression as a string
- Define a directive without having to only use it as a proxy to a helper function or class in another part of the application

```php
// Define the helper directive
BladeHelper::helper('example', function($a, $b, $c = 'give', $d = 'you') {
    return "$a $b $c $d up";
});

// Use it in a view
@example('Never', 'gonna')

// Get the compiled result
<?php echo app('blade.helper')->getDirective('example', 'Never', 'gonna'); ?>

// See what's echoed
"Never gonna give you up"
```

There's a couple of other little features and examples in the package's readme. Check it out below!

<https://github.com/imliam/laravel-blade-helper>
