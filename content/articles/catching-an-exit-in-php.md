---
title: Catching an exit(); in PHP
slug: catching-an-exit-in-php
published_at: 2018-09-08
strapline: ""
synopsis: ""
tags:
    - PHP
---

![Catching an exit statement in a glove](https://res.cloudinary.com/liam/image/upload/v1560558841/liamhammett.com/catch-exit.jpg)

What do you do when something goes wrong in your PHP application? Probably throwing an exception, so it can be caught at a higher level and handled appropriately.

What happens when instead of throwing an exception, an `exit();` is executed? **Can you catch it?**

## But... why would you want to?

This should *never* be done, as every modern PHP framework has decent, extendable exception handlers that should be taken advantage of when anything like this is needed  —  but everyone's first database connection in PHP looks something like this:

```php
<?php

$db = mysql_connect(...) or die('Could not connect.');
```

Luckily this is a thing of the past in modern PHP applications as the community has come a long way, but unfortunately it still seems to be a practice *some people* use  —  over the past couple of years I've come across multiple Composer packages that still use `exit` and `die` statements when an error occurs.

These packages should be avoided or a pull request made to sort them out, but is it possible to deal with this situation any other way? What if we could treat an early exit like a try-catch statement?

## Registering a shutdown function

PHP has a handy function called [`register_shutdown_function()`](https://secure.php.net/manual/en/function.register-shutdown-function.php) to deal with this behaviour. It's a special higher-order function that will accept a callback to be executed when PHP's lifecycle is almost over and the application is in the process of shutting down.

It's not so simple though, because this callback will also be executed when the application's lifecycle is ending naturally  —  after most other things have been unloaded.

This makes determining whether or not the application is being manually exited a little bit more of a hassle. We could set a custom global variable, but that requires a bit of additional logic to cope with multiple catches defined.

The simplest solution I found to this however is to use references on a `&$variable`, which allows you to pass some unique information around between contexts —  and use that to determine whether the shutdown script should be executed or not.

We can take this referenced variable and use it to determine whether or not the shutdown function should *actually* be executed, with an early return.

```php
$shouldExit = true;

register_shutdown_function(function() use (&$shouldExit) {
    if (! $shouldExit) {
        return;
	}

    echo 'Something went wrong.';
});

exit; // Uh-oh, something's gone wrong and triggered a shutdown

$shouldExit = false; // If something didn't go wrong, we tell our shutdown function that it shouldn't execute the main body

// We can continue running our application like normal
```

We can now "catch" any potential `exit` statement and determine how to deal with it at that point.

*Neato burrito! 🌯*

## Continuing the application flow

Unfortunately, at the point an exit occurs, there's no way to properly continue the flow of the application as we can continue with a try-catch block.

[Goto](http://www.php.net/manual/en/control-structures.goto.php) is limited to its current scope, and we're locked into the shutdown function callback  —  as soon as the callback exits the application finishes shutting down.

I've tried a couple of other things, but nothing else promising springs to mind when it comes to continuing the application as if nothing happened.

## Okay, what *CAN* we do?

At the point the application is shutting down, we still have some level of control. We can new up classes, build responses, get and manipulate the current output buffer, maybe that's enough control...

If something as horrific as an `exit` occurs, you might want to do a few things:

- Log this incident
- Flash a message to the user to display to them on next page load
- Redirect the user back to a more appropriate page
- Render an error page

You can take this one step further and even handle fatal errors, like instantiating a class that doesn't exist  —  or going over PHP's maximum execution time.

I've taken this concept and wrapped it in a helper function in a package for convenient use, with some additional functionality such as checking the exit message, the current output buffer and handling a couple of quirks of this approach.

<https://github.com/imliam/php-catch-exit>

Hopefully no-one ever finds the need to genuinely use this, but it's nice to know that PHP does give us a way to handle this scenario.
