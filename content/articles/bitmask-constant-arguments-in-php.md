---
title: Bitmask Constant Arguments in PHP
slug: bitmask-constant-arguments-in-php
published_at: 2018-08-23
strapline: ""
synopsis: ""
tags:
    - PHP
---

PHP has a handful of core functions that can accept boolean arguments in the form of constants that have a binary value.

These can be combined together in a single function argument, essentially passing multiple boolean flags in a very compact manner.

They work a bit differently to how most people implement options in their userland functions, so let's take a look at how they work.

## How does core PHP use them?

The core PHP language uses these *a lot* for manipulating the behaviour of a handful of functions. As of PHP 7.2 with just a couple of extensions, there are over 1800+ constants defined, a lot of which are used as function arguments.

One example of these in use is the new option in PHP 7.3 to [make `json_encode()` throw exceptions](https://laravel-news.com/php-7-3-json-error-handling) upon encountering an error.

```php
json_encode($array, JSON_THROW_ON_ERROR);
```

Using the `|` [bitwise operator](http://php.net/manual/en/language.operators.bitwise.php) you can also pass multiple arguments at once, like [this example from the docs](http://php.net/manual/en/function.json-encode.php).

```php
json_encode($array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
```

Pretty nifty.

## But how do they work?

Using these bitmask operators to achieve the same effect in userland functions is pretty simple—but it requires at least a rudimentary understanding of how bits and certain bitwise operations work in PHP.

> Integers can be specified in decimal (base 10), hexadecimal (base 16), octal (base 8) or binary (base 2) notation. \[...\] To use binary notation precede the number with `0b`.
>
> — *[php.net](http://php.net/manual/en/language.types.integer.php)*

We'll keep this fairly straightforward by defining some [binary integer literals](http://php.net/manual/en/language.types.integer.php)as constants that each have a different bit set.

```php
const A = 0b0001; // 1
const B = 0b0010; // 2
const C = 0b0100; // 4
const D = 0b1000; // 8
```

The gist of it is that each binary value will represent a value twice as high for each zero on the end of it. The zeroes between the `0b` and `1` are completely optional, but can help line up the source code.

We want each of our constants to have a unique value.

Luckily, we only need to understand two bitwise operators to get a grasp on how to use these values for our goal.

## "Bitwise Inclusive OR"

The `|` ("*bitwise inclusive or*") is not to be confused with the more commonly used `||` ("*logical or*") operator you may find in "if" statements. Instead, it returns the bits that were set in either one or the other value.

```php
const A     = 0b0001;
const B     = 0b0010;
const C     = 0b0100;
const D     = 0b1000;

A | B === 0b0011;
A | C | D === 0b1101;
```

Since each of our original constants has a unique bit, we can combine any amount of these to get a new unique set of bits back, which we can use as a single value.

## "Bitwise AND"

Similarly, the `&` ("*bitwise and*") operator is not to be confused with the more commonly used `&&` ("*logical and*") operator found in "if" statements. It instead returns the bits that are set in both values.

```php
const A     = 0b0001;
const B     = 0b0010;
const C     = 0b0100;
const D     = 0b1000;
const VALUE = 0b1010; // Notice how we have two bits set

A & B     === 0b0000; // None of the same bits are set in A and B
A & C & D === 0b0000; // None of the same bits are set in A, B or C
A & A     === 0b0001; // The same bit is set in A twice
A & VALUE === 0b0000; // None of the same bits are in A and VALUE
B & VALUE === 0b0010; // This bit is set in both B and VALUE
```

## A number can be a boolean?

It's worth noting at this point that PHP has a concept called "type juggling". In laymen's terms, this means it will automatically attempt to cast one type of data to another if it needs to.

This can come in pretty handy when you understand what gets cast to another type and when.

For example, we know that the integer `0` will act as `false` if cast to a boolean, and any other integer will act as `true`. Remember how these binary values we're working with are actually integers?

## Let's put it all together!

We can now combine this knowledge to create an "if" statement that will only pass if a given bit out of many is present.

```php
const A = 0b0001;
const B = 0b0010;

function example($options = 0)
{
    if ($options & A) {
        echo 'A';
    }

    if ($options & B) {
        echo 'B';
    }
}

example(); // Echoes nothing
example(A); // Echoes 'A'
example(B); // Echoes 'B'
example(A | B); // Echoes 'AB'
```

That's pretty much exactly how other functions like `json_encode()` can be interacted with, sweet!

## Is it worth it?

Even if it may seem tempting to be able to pass any number of boolean values in a single argument, there are downsides to this approach to handling feature flags:

- You can't pass non-boolean values through the argument like you could with an associative array
- There's no standard way to document the arguments with docblocks
- You lose general hints and most IDE support that you would get if you were passing a single boolean value as an argument
- You could instead pass an associative array as the argument to be able to set non-boolean values ([or even a class](https://steemit.com/php/@crell/php-use-associative-arrays-basically-never))
- There's strong reasons [why you should avoid using boolean "feature flags" as arguments](https://martinfowler.com/bliki/FlagArgument.html) in general in the first place, and instead opt for making another function or method for the altered functionality

But now you know how to do it, if you ever wanted to.

*It can be a bit verbose and difficult to read a long collection of these constant definitions, so [here's a helper function to make defining them simpler](https://gist.github.com/ImLiam/b740732b72fdb80685b540e9f021860d).*
