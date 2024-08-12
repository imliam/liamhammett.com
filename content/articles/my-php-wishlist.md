---
title: My PHP Wishlist
slug: my-php-wishlist
published_at: 2019-06-21
strapline: ""
synopsis: ""
next_article: php-wishlist-typing
tags:
    - PHP
---

Back when I started using PHP properly in the early 5.0 days, it felt like the language was pretty *basic*. Other languages were making leaps and bounds every year, and as time went on, PHP seemed to have stagnated. The language wasn't bad, but it wasn't as good as it could've been.

That all changed this decade. PHP has come an awful long way in the last few years and is once again proving that it's got what it takes to be a programming language people should take seriously, even outside the web. I'm hugely happy with the direction PHP has taken and the amazing work of the core contributors and the entire ecosystem.

That said, there are a handful of things I would love to see PHP implement at some point in the future. These are a few things that I would've liked to be able to use every now and again when the circumstances for them come up.

Here I'm going to dive into a couple of them.

## Stronger Typing System

PHP's typing system has been getting gradually better since PHP 5. It doesn't get in your way if you don't want to use it, but if you want to write code knowing what types are being passed around and used in your application, it's pretty good at doing that for you.

It's still pretty loose in places, however, and there are plenty more changes that could be made to the language's typing system to improve it, without breaking backwards compatibility or forcing it on everyone.

I've written about this in more depth before, covering what a slew of different improvements could contain, including array notation, generics, union types, overloading, and a lot more. Check it out at the link below:

<https://liamhammett.com/php-wishlist-typing-LkoZazl3>

## Operator Overloading

Operator overloading would allow a developer to define how their objects should interact with different operators. They would introduce a way to make userland objects feel natural and intuitive to work with.

I've previously written about this in a lot more depth, which I recommend checking out if you want to know more about what this is and how it might look:

<https://liamhammett.com/php-wishlist-operator-overloading-wEQXAr4p>

## "primitive objects"→titleCase();

Primitive objects are hands-down the one thing that I believe would improve PHP the most. On a basic level, they would allow primitive objects, such as strings, integers and arrays, to behave like an object.

This would bring a new way to interact with PHP objects in a fluent and intuitive way by chaining methods off of them.

```php
$string = 'Hello world';

// Functional approach
strtoupper($string);

// Object-based approach
$string->toUpper();
```

- It is the best approach to making the standard library consistent. People complain about the differences in PHP functions all the time, like different naming schemes, different parameter orders, et cetera. A method based approach would:
    - Bring an entirely new API to the methods that can follow a consistent design
    - Not affect the current functional approach and introduce any backwards incompatible changes to those functions
- It is a common paradigm found in prototype-based languages like JavaScript and Lua. It's not an abstract concept to developers or would be difficult for people to understand when using.
- It would result in much cleaner code in many scenarios. Being able to write readable code is a hugely important factor for developers, so not having to nest function calls inconsistently to manipulate primitives.

PHP core contributor Nikita has [written an extension](https://github.com/nikic/scalar_objects) that unofficially adds this to PHP - it would be great to see it brought to the core.

## Pipe operator

I've already talked about [function chaining with pipes](https://medium.com/@liamhammett/php-function-chaining-with-pipes-a5c637587d7d) and how it can *kind of* be achieved within userland before, but what I really want is the [pipe operator proposal for core PHP](https://wiki.php.net/rfc/pipe-operator).

For an example of what this would help solve, imagine you want to get the subdomain from a URL.

You might nest some function calls inline but reading that making you scan back and forth to figure out what the original value was halfway through the line and work out what's being done to it.

```php
$subdomain = explode('.', parse_url('https://blog.sebastiaanluca.com/', PHP_URL_HOST))[0];
```

This could be rewritten in a slightly different way by moving every function call to its own line, but this is quite verbose and repetitive. This might be clearer, but the variable named `$subdomain` is not always the subdomain at every step in the process so adds a layer of confusion when reading the block.

```php
$subdomain = 'https://blog.sebastiaanluca.com/';
$subdomain = parse_url($subdomain, PHP_URL_HOST);
$subdomain = explode('.', $subdomain);
$subdomain = reset($subdomain);
```

You can always give each variable a different name, but [naming things is one of the hardest things in computer science](https://martinfowler.com/bliki/TwoHardThings.html), so when you ultimately don't care about these intermediate values, why bother naming them?

The proposed pipe operator would offer a way to clear up any procedural code in a clear, chainable way.

```php
$subdomain = 'https://blog.sebastiaanluca.com/'
    |> parse_url($$, PHP_URL_HOST)
    |> explode('.', $$)
    |> reset($$);
```

## Cast to an object

Casting one value to another type, like `(string) 1337`, is a pretty common approach to enforcing a value conforms to a certain primitive type.

This could be expanded to userland objects through the use of a new magic method that gets called when a cast to that type is requested.

```php
class User
{
    public function getEmailAddress()
    {
        return (EmailAddress) $this->getProperty('email_address');
    }
}

class EmailAddress implements Castable
{
    protected $address;

    public function __construct(string $address)
    {
        $this->address = $address;
    }

    public static function __cast($value): static
    {
        if ($value instanceof EmailAddress) {
            return $value;
        }

        return new static($value);
    }
}
```

## Annotations

Annotations are a way to define metadata in the source code that can be read and interacted with from userland code.

It's another pattern found in a handful of other languages like Java and C# that can open up a ton of new and intuitive ways to put different parts of a codebase together.

```php
@middleware("guest", except={"logout"})
class AuthController extends Controller
{
    // ...

    @route("logout", as="logout")
    @middleware("auth")
    public function logout()
    {
      $this->auth->logout();

      return $this->redirect('login');
    }
}
```

It's worth noting that there are already third-party packages such as [doctrine/annotations](https://github.com/doctrine/annotations) that can bring this functionality to applications through the use of docblocks and reflection.

## Named function parameters

Another thing commonly found in other programming languages. Named parameters would allow the developer to call the nth parameter of a function, without requiring them to pass a value to each one that comes before that.

This is particularly useful if a function accepts a number of parameters with default values.

```php
function do_something($arg1, $arg2 = '', $arg3 = 'default', $arg4 = 128)
{
    // ...
}

do_something('Hello', 'world');
do_something('Hello', 'world', $arg4 => 32);
do_something($arg2 => 'world', $arg1 => 'Hello');
do_something($arg1 => 'Hello', $arg2 => 'world', $arg4 => 32);
do_something('Hello', $arg4 => 32);

```

It also inadvertently has a good readability bonus - when going back to this style of code, the developer can now see what the name of the parameters are that they're using, giving them additional context and not forcing them to look at the function's definition or relying on PHPStorm's parameter hints.

## Match statement

I see long `if/elseif/else` or `switch` statements used all the time in PHP to figure out what a given value should be. Quite often they're only getting a single value with over various conditions, but can get unnecessarily verbose very easily.

Using guard clauses with early returns often means the logic to return that value has to be split out into its own method too.

```php
class Example
{
    public function action()
    {
        $number = 14;

        $result = $this->getResult($number);

        echo $result; // 'A teen'
    }

    private function getResult($number)
    {
        if ($number === 1) {
            return 'One';
        }

        if (in_array([2, 3, 5, 7, 11], $number)) {
            return 'This is a prime';
        }

        if (in_array(range(13, 19), $number)) {
            return 'A teen';
        }

        return 'Ain\'t special';
    }
}
```

I don't mind this so much - it's often a good idea to extract blocks of logic like this stand on their own when they can. It's not unusual to see these get unwieldy, however, with a lot of conditions with different return values.

[Taking a leaf out of Rustlang's book](https://doc.rust-lang.org/rust-by-example/flow_control/match.html), I feel like a new `match` statement would be an ideal fit for such a use case. It would look similar to a switch statement and evaluate from top-to-bottom, but be far less verbose, with an implicit return for the first value that gets matched.

This approach means that code won't be evaluated until it's reached in the chain *if* it's even needed. This is how it could be used:

```php
$number = 14;

$result = match ($number) {
    1 => 'One!';
    2 || 3 || 5 || 7 || 11 => 'This is a prime';
    in_array(range(13, 19), $number) => 'A teen';
    default => 'Ain\'t special';
}

echo $result; // 'A teen'

$result = match($number, [
    1 => fn() => 'One';
]);
```

There's a few things to note in this here:

- The statement itself will return a value, so it can be used inline.
- Similarly to a switch statement, if the passed parameter is identical to the value on the left side (the case), it would return the value on the right.
- The logical `or` operator can be used to match the identity of multiple values. This is similar to using `case 'a': case 'b': return 'Result';` in a switch statement to match multiple values, but is far less verbose and easier to read. It would also be nice to see this syntax for switch statements too.
- For more complex logic that may involve passing the value to a function, this can also be done to determine a match.
- If no value is matched, a fallback can be defined with the `default` keyword. If no fallback is defined, an implicit `null` will be returned.

## "Closure Use" Aliases

With closures, it's possible to pass additional variables through to them from the current scope with the `use` keyword. This works well most of the time but isn't always ideal.

Similarly to how imported namespaces and trait methods can be aliased to another name in PHP, I'd like to be able to do the same with these imported variables. This would give a way to solve two problems that can come up because of them:

1.  Because the closure will be executed in a different context, the variable name inside the closure may be more explicit if it had a different name than the parent scope.
2.  A closure can be dynamically bound to a class - this means you can't be sure if the variable `$this` will be an instance of the parent object from the original scope or another object it's been bound to before execution. Aliasing would let you pass specific properties and values through where you know exactly what they are.

```php
// Closure use aliases

$fnc = function($a, $b) use ($c, $this->d as $d, $elephant as $e) {
    return [$a, $b, $c, $d, $e];
};
```

## Miscellaneous

There's a handful of other things I'd like to see come about, but I don't have much to say about them individually right now.

- Native support for async/await
- `empty()` should be variadic and accept any number of parameters
- Native multibyte UTF8 support
- Native big number support (above the 64-bit range)
- Core standard library to be namespaced where appropriate

Once again, these are all *nice-to-haves*, but not hugely urgent or important. They would make dealing with certain things easier, but can all mostly be achieved through userland code or calling slightly different functions or constructs here and there.

What would you like to see make it into PHP in a future version?
