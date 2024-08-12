---
title: PHP Function Chaining with Pipes
slug: php-function-chaining-with-pipes
published_at: 2018-02-24
strapline: ""
synopsis: ""
tags:
    - PHP
---

I highly recommend reading the aforementioned post and RFC to get an understanding of what function piping is as they do an amazing job at explaining the benefits it brings.

In short; it's syntactic sugar to help make code more readable.

<https://wiki.php.net/rfc/pipe-operator>

The original RFC was intended to make it easier to read code when multiple functions are executed on the same value by means of a new operator in the core language (already seen [implemented in HackLang)](https://docs.hhvm.com/hack/operators/pipe-operator).

In short, it would take the following line of code, where you have to start reading in the middle and simultaneously work your way out both sides:

```php
$subdomain = explode('.', parse_url('https://blog.sebastiaanluca.com/', PHP_URL_HOST))[0];
```

And refactor it into the following block, which shows the clear chain of what happens in an easy to read format so you can understand what happened at each stage to get the final result:

```php
$subdomain = 'https://blog.sebastiaanluca.com/'
    |> parse_url($$, PHP_URL_HOST)
    |> explode('.', $$)
    |> reset($$);
```

Now it's much clearer what's happening, and in what order.

Realising the benefits this can bring to writing readable code, Sebastiaan created a helper function to bring this kind of function chaining to our current PHP applications.

<https://blog.sebastiaanluca.com/enabling-php-method-chaining-with-a-makeshift-pipe-operator>

Using his function, the same code can be rewritten in the following way:

```
$subdomain = take('https://blog.sebastiaanluca.com/')
    ->pipe('parse_url', PHP_URL_HOST)
    ->pipe('explode', '.', '$$')
    ->pipe('reset')
    ->get();
```

Pretty neat — each operation is now in a clear order going from start to finish, eventually `->get()`ing the result.

However, I believe that this syntax is completely different and doesn't help to solve the problem that the RFC was aiming to do — help make existing code cleaner and more readable.

Some of the issues I see with this are:

- This solution turns everything into strings, you're not going to get any of the features an IDE affords you, such as syntax highlighting for function names, peeking a function's definition, or assistance when filling out the arguments
- You can't call non-static methods on an already instantiated object
- The '$$' string seems confusing and will confuse people looking at this code in the future. It's not a language construct everyone's expected to know, and it's not immediately obvious when looking at the source code for the function what this special string does.
- It's another format to remember instead of naturally calling the function.
- Calling a piped function can take multiple forms depending on where the piped value should go. For example, `take('Hello world.')->strtoupper()` is fine as strtoupper only accepts one argument which is automatically piped through, but `take('Hello world.')->explode(' ', '$$')` requires the '$$' string to determine where in the argument list the value should be piped.
- The '$$' string can be problematic in lots of scenarios — for example processing user input where it could be a legitimate string entered by a user.
- The closure option may be useful in some cases, but makes it even more verbose again when the goal is to make the code more readable.
- Having to call the method `->pipe()` after each link in the chain is almost as verbose as the original temporary variable option described in the blog post:

```php
$subdomain = 'https://blog.sebastiaanluca.com';
$subdomain = parse_url($subdomain, PHP_URL_HOST);
$subdomain = explode('.', $subdomain);
$subdomain = reset($subdomain);
```

There must be a better way to get a similar syntax while mitigating some of those issues.

My biggest concern was the `'$$'` value, being the single cause of the most problems previously listed.

Since PHP is infamous for its needle/haystack problem with its core functions, I figure it's best to make this feature more consistent and always force the *real* value to be piped through to the place the developer wants each time, even if it's a little verbose.

How can we get the *real value* though? It's not easily possible while chaining because we can't define and then access a variable in our main scope until the chain is complete, right?

Right, but with one exception — making use of the `$GLOBALS` superglobal offered by PHP to share the value between scopes as it's being changed. We can get access to this with a property inside the class that's returned, making it easy to use without worrying about the internals.

Once we can access the raw value, it's easy for us to bring all of the function calls back up to vanilla PHP instead of abstracted to strings, and we can end up piping functions like so:

```php
$taken = take('https://blog.sebastiaanluca.com/');
$taken->pipe(parse_url($taken->value, PHP_URL_HOST))
      ->pipe(explode('.', $taken->value))
      ->pipe(reset($taken->value))
      ->value;
```

This solves most of the issues brought up above — you now get the benefits your IDE affords you, you can use objects and methods you've already got instantiated, you don't need to use closures for anything, et cetera.

Unfortunately there's still a couple of problems with it:

- We need to access the function's return value before the piping is done, so there must be an intermediary variable set. This may lead to extra code when only a couple of functions are piped, but not so much of a problem where you need to pipe lots of things together (the RFC gave a real world example where FBShipIt pipes 11 functions together for 1 output).
- It's still pretty verbose since we need to type `->pipe()` after each function piped.

Regardless, this source for this solution is available in the following Gist:

<https://gist.github.com/imliam/11940ab73b024140f8e7cd76ef56b0e0#file-take-php>

To try and remove the `->pipe()` method calling each time to make it more readable, we can use the `__call()` magic method that PHP classes offer to automatically call a function. This lets us change `->pipe(strtoupper(...))` into just `->strtoupper(...)`

While this makes a long chain of commands much easier to read, it does come with its own trade-offs:

- We no longer get some of the things your IDE provides, such as peeking a function's definition (it's no longer a function being called, it's a method), no help filling out the arguments of the function (again, because the IDE doesn't know what the magic method wants), and it will now be syntax highlighted as a method instead of a function if that bothers you.
- We cannot use this approach to call methods on another object, as there's no syntax for it. We can bring this back by using a closure, or even a special syntax to pass an object through like Sebastiaan's original method allowed for, such as`->pipe([$object, 'method_name'], $arguments...)` but this is something we were hoping to avoid.

Another problem we had with our last solution was needing to declare a variable before we even started the chaining process, making it so that chaining only 2 functions together would result in 3 lines of code.

Now that we're not calling the raw functions and are instead passing values through a custom method, we can impart a bit of our own logic into how we retrieve the piped value, and instead just get it directly from our Pipe object.

However we don't want to fall back into the same problem as before of having a poorly described string such as `'$$'` passed through. One way to get around the problems that provides is to define a constant we can use to *represent* the value through the piping process, even if it isn't the real value.

- We can define a constant with an appropriate name such as `PIPE_VALUE` which helps other people understand the code — it's clearer what this represents in the piping process rather than '$$'.
- This constant can contain a uniquely generated ID so it's not susceptible to user input breaking it.

```php
define('PIPE_VALUE', '__pipe-' . uniqid());
```

Now with a bit of logic inside the `__call()` magic method, we can determine if the argument passed is equal to this constant, to replace it with the *real* value before we go on to call the actual function.

```php
foreach ($args as $key => $arg) {
    if ($arg === PIPE_VALUE) {
        $args[$key] = $this->value;
    }
}
```

Now the verbosity is gone and it's clear where the value is being piped to at each step in the chain. Win win!

```php
$subdomain = pipe('https://blog.sebastiaanluca.com/')
    ->parse_url(PIPE_VALUE, PHP_URL_HOST)
    ->explode('.', PIPE_VALUE)
    ->reset(PIPE_VALUE)
    ->value;
```

Finally, just for some more syntactic sugar, we can implement some of the [predefined interfaces](https://secure.php.net/manual/en/reserved.interfaces.php) and [magic methods](https://secure.php.net/manual/en/language.oop5.magic.php) that PHP offers to allow our Pipe object to be able to be cast to a string, traversable like an array, serialisable, etc. and still use the real value instead of our placeholder object. This way, for most use cases, we can get rid of that final `->value` call.

After all of this, we end up with the following code:

<https://gist.github.com/imliam/e09695cafaf306e097321461f0cb72f8#file-pipe_class-php>

All-in-all, I think I personally prefer this final method for chaining regular functions together easily on-the-fly as I find this is what I most often find myself doing when I feel piping would be useful, and the convenience it affords to do on a whim is great.

However, none of the solutions shown here are perfect, they're all with their own drawbacks that can't be solved with vanilla PHP. I can't wait to see where [the original RFC](https://wiki.php.net/rfc/pipe-operator) goes, and I sincerely hope it makes its way into a version of PHP in the near future.

**Update:** On the same day as writing this post, Sebastiaan has released an updated package dedicated to function piping, which with [some minor changes](https://github.com/sebastiaanluca/php-pipe-operator/pull/1) should support mostly everything discussed in this article. Check it out below.

<https://github.com/sebastiaanluca/php-pipe-operator>