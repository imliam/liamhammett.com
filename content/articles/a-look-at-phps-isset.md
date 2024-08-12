---
title: A Look At PHP's isset()
slug: a-look-at-phps-isset
published_at: 2019-06-07
strapline: ""
synopsis: ""
tags:
    - PHP
---

[`isset()`](https://www.php.net/manual/en/function.isset.php) is one of the most important tools at your disposal to validate data in PHP. Like the name implies, it is designed to verify if a variable given to it is set, returning a boolean value based on the result.

However, it has some quirks and behaviours that are very much worth knowing as they can easily catch out even experienced developers.

Let's take a look through how it behaves and what's so special about it. Even if you're a veteran PHP developer, hopefully, you'll pick up something new here.

## It's a language construct, NOT a function

Despite looking like one, `isset()` is actually NOT a typical function as you might expect.

Just like `echo`, `die()`, `array()`, `print()` and others, it is a "language construct", which is a fancy phrase that, in laymen's terms, means it's built directly into the PHP engine and can have some special behaviour that is different than typical built-in or user-defined functions, which we will go over next.

## It can not be used as a callable

Any built-in or user-defined function can be used as a "callable" function pointer to be invoked dynamically and used for patterns like currying.

```php
is_callable('strtoupper');
// true

array_map('strtoupper', ['a', 'b', null, 'd']);
// ['A', 'B', '', 'D']
```

As it is a language construct and not really a function, it is not callable and cannot be used in such a way.

```php
is_callable('isset');
// false

array_map('isset', ['a', 'b', null, 'd']);
// PHP Warning:  array_map() expects parameter 1 to be a valid callback, function 'isset' not found or invalid function name...
```

## It does not accept an expression

While regular functions and other language constructs can accept the result of any expression, due to its unique nature, `isset()` can only accept a variable, array key or object property as an argument.

Attempting to use it any other way will result in a fatal error.

```php
if (isset('Hello world')) {
    // Fatal error: Cannot use isset() on the result of an expression (you can use "null !== expression" instead)
}

if (isset($a->b())) {
    // Fatal error: Cannot use isset() on the result of an expression (you can use "null !== expression" instead)
}

if (isset(! $a)) {
    // Fatal error: Cannot use isset() on the result of an expression (you can use "null !== expression" instead)
}

if (isset(CONSTANT)) {
    // Fatal error: Cannot use isset() on the result of an expression (you can use "null !== expression" instead)
}
```

## It also checks if a value is `null`

`isset()` will return `false` if a variable is undefined OR if its value is `null`. This may throw you off if `null` is a proper value you have *set* and want to allow.

```php
$value = null;

if (isset($value)) {
    // ...
}
```

One way to go about checking this, depending on your requirements, is to check if a variable is defined in the current scope with `get_defined_vars()` and examining the resulting array's keys.

```php
$value = null;

if (array_key_exists('value', get_defined_vars())) {
    // ...
}
```

## It can accept multiple arguments

It's very common to see people chaining calls together to check that multiple values are set one-by-one.

```php
if (isset($a) && isset($b) && isset($c)) {
    // ...
}
```

However, `isset()` is a variadic function that can accept any number of parameters at once to achieve the same effect, confirming if *all* of the passed variables are set.

This can be a great way to shorten long conditionals.

```php
if (isset($a, $b, $c)) {
    // ...
}
```

## It does not trigger "undefined variable/index/property" notices

If you're retrieving a value nested multiple levels deep, you probably want to make sure every step of the chain exists.

```php
if (isset($response, $response->list, $response->list['results'], $response->list['results'][0])) {
    // ...
}

if (isset($arr[$key], $otherArr[$arr[$key]], $otherArr[$arr[$key]][$otherKey])) {
    // ...
}
```

However, `isset()` will not trigger any "undefined variable", "undefined index" or "undefined property" notices, no matter how many layers you go through.

This means that instead of confirming the value at every individual step, they can all be done in a single check:

```php
if (isset($response->list['results'][0])) {
    // ...
}

if (isset($otherArr[$arr[$key]][$otherKey])) {
    // ...
}
```

PHP 7.4 introduces typed properties, which throw a new "must not be accessed before initialization" error if it doesn't have a value set. Using `isset()` to check these properties will not trigger the errors.

```php
class Foo {
    public array $bar;
}

$foo = new Foo();

if (isset($foo->bar)) {
  // This is acceptable use and won't trigger an error
}

$foo->bar;
// Fatal error: Uncaught Error: Typed property Foo::$bar must not be accessed before initialization
```

## "Undefined method" errors *do* get triggered

If a chain being checked happens to include a method call halfway through it, PHP will attempt to invoke the method.

This means that if an earlier part of the chain does not exist, or the last value in the chain is an object that simply does not have this method, an error will still be triggered.

```php
$a = new stdClass();

if (isset($a->b()->c)) {
    // Fatal error: Uncaught Error: Call to undefined method A::b()...
}

if (isset($a->b->c()->d)) {
    // Fatal error: Uncaught Error: Call to a member function c() on null...
}
```

One way to deal with this is to be explicit in your conditional checks, stopping the chain and calling `method_exists()` to verify the method exists every time it is needed.

```php
if (isset($a) && method_exists($a, 'b') && isset($a->b()->c)) {
    // ...
}
```

One way to shorten such an expression is to use the [error control operator](https://www.php.net/manual/en/language.operators.errorcontrol.php), which suppresses any errors for a single expression. If an error is triggered, the operator will make the expression return `null` instead and continue the execution.

```php
if (@$a->b()->c !== null) {
    // ...
}
```

However, while this may be convenient, you should be aware that the error control operator is very inefficient and can also suppress errors triggered within the called methods you call and are not intending to suppress. It is not an outright replacement for `isset()`.

## !empty() is not quite the same

[`empty()`](https://www.php.net/manual/en/function.empty.php) is also a language construct with similar behaviour to `isset()` in that it doesn't trigger undefined notices.

```php
$a = [];

if (empty($a['b']->c)) {
   // ...
}
```

It seems as if it serves as a direct inverse of `isset()`, but this is not the case. `empty()` can also accept expressions as its arguments, but more importantly, **it will type juggle** so that any falsey value is treated as such.

```php
$a = '0';

if (isset($a)) {
    // It IS set
}

if (empty($a)) {
    // It IS empty
}
```

## Null coalesce operator

It is a very common occurrence to want to provide a fallback value in case a variable is not set. This is typically done with a short conditional `if` statement or ternary clause.

```php
$result = isset($value) ? $value : 'fallback';
```

As of PHP 7.0, this can be shortened using the null coalesce operator (`??`) which will return the first value if it is set, or the second value if not.

```php
$result = $value ?? 'fallback';
```

If instead of returning a new value, you didn't want to set a new variable doing this, that is covered as well. As of PHP 7.4, the null coalesce assignment operator (`??=`) allows an even shorter way to set a variable to a fallback if it isn't already set.

```php
$value ??= 'fallback';
```

## It does not evaluate the `__get()` magic method

Let's assume we have a pretty typical class that can dynamically get properties by using the magic method `__get()` to retrieve a value.

```php
class Person
{
    protected $attributes = [];

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}
```

If we use this class to set a property, we can make use of it as we might normally expect. However, if we check if the value is set, it will return `false`

```php
$person = new Person();
$person->name = 'Liam';

echo $person->name; // 'Liam'

isset($person->name); // false
```

Wait, what's going on here?!

Because `isset()` is a language construct and not a regular function, the expression doesn't get evaluated before it's passed to it. Because `name` isn't a real property on the object, it doesn't *really* exist.

However, when `isset()` gets called on a property that doesn't exist or is inaccessible to the current scope (such as being protected or private), it will invoke a magic `__isset()` method if the class has one defined. This allows for custom logic to be done to determine if we think the property we're checking is set according to our own rules.

```php
class Person
{
    // ...

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }
}
```

With that implemented, everything works as expected.

```php
$person = new Person();
$person->name = 'Liam';

isset($person->name); // true
isset($person->somethingElse); // false
```

It is important to note that if you are checking nested properties, `__isset()` will be invoked as appropriate for each property in the chain.

Note that the `empty()` language construct will also trigger this magic method. Also note that `property_exists()` (or `array_key_exists()`, even though it shouldn't be used to check object properties) will NOT trigger this or any other magic method, so any magic properties can not be checked this way.

## You *can* pass non-existent variables to userland functions

As we have already discussed, because `isset()` is actually a language construct, it has special behaviour because of the PHP core, and thus does not behave like functions we define ourselves.

However, we can achieve a similar effect in userland functions, through the use of *references*. By doing this, we open up the possibility to expose additional functionality of our own choosing on top of the regular language construct.

One practical example of this might be to treat any objects implementing a [null object pattern](https://en.wikipedia.org/wiki/Null_object_pattern) as falsey values.

```php
interface NullObject {}

class Logger {
    // ...
}

class NullLogger extends Logger implements NullObject {
    // ...
}

function is_truthy(&$value)
{
    if ($value instanceof NullObject) {
        return false;
    }

    return (bool) $value;
}

is_truthy($a);
// false

$b = '';
is_truthy($b);
// false

$c = '1';
is_truthy($c);
// true

$logger = new Logger();
is_truthy($logger);
// true

$nullLogger = new NullLogger();
is_truthy($nullLogger);
// false
```

However, references are not always that safe to use, as simply using them *can* affect the original value, even if the function doesn't explicitly do it.

For example, any undefined array keys or properties will automatically be assigned and their value set to `null`

```php
$a = [];

is_truthy($a['b']['c']);
// false

var_dump($a);
// [
//     'b' => [
//         'c' => null,
//     ],
// ]
```

## Conclusion

Hopefully throughout this look at `isset()`, its behaviour and other related things, you will have picked something up that will help you make your code cleaner, more explicit, and not catch you out in edge cases when you need to check if a variable has been set.
