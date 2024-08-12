---
title: "PHP Wishlist: Typing"
slug: php-wishlist-typing
published_at: 2019-05-31
strapline: ""
synopsis: ""
previous_article: my-php-wishlist
next_article: php-wishlist-operator-overloading
tags:
    - PHP
---

PHP is a loosely typed language. It doesn't care what types you throw around. **Unless you want it to care.**

The language has come a long way in the last several years to bring in a robust type system, allowing developers to enforce types in both function parameters and what a function's return value is.

For everything else, there's docblocks, ugly sanitisation and assertion code, and crossing your fingers to hope your function's API holds up in practice.

What I wish for is for more of this to be brought straight into PHP's core typing system.

## Moving in the right direction

I'm already happy with the way PHP is moving in this respect. As early as PHP 5.0's release, 15 years ago, it has steadily been adding more ways to enforce type safety into our code at our own will. With the advent of PHP 7, this has been ramped up tenfold.

An example of this is [typed properties](https://wiki.php.net/rfc/typed_properties_v2), which, up until 7.4, had to be kept inside docblocks and respected by the developer or additional user code at runtime to assert any types are correct.

```php
// Before PHP 7.4:

class Person
{
    /**
     * @var string
     */
    protected $name;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```

As of PHP 7.4, the property itself can have a type defined against it.

```php
// From PHP 7.4 and above:

class Person
{
    public string $name;
}
```

This doesn't completely do away with the need for getters and setters as they serve more purposes than *just* enforcing types, but it does take that one verbose step away in a lot of scenarios.

I also want to say that *I like things about PHP's dynamic typing*. It makes it quick to pick up the language and make things, and being able to change the behaviour of a function depending on what type goes into it is a way to make wonderfully intuitive interfaces without having to expose a huge API.

*Loose typing* causes pains when things get implicitly cast back and forth between different types, but `declare(strict_types=1);` is a workable solution for this for now.

PHP gives the perfect combination of both the dynamic typing and the strict typing worlds and puts it entirely on the developer to decide what they want to do.

With that, let's talk about some of the things I think could make the language better and more pleasant to work with.

## Tuple notation

Tuples are, at a very basic level, an ordered list of data where each item in the list is an expected type of value. This is not an uncommon structure to see in PHP, especially with the [list function](https://www.php.net/manual/en/function.list.php) and the [shorthand variant brought at from PHP 7.1](https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring) to offer array destructuring.

They're not an ideal structure because PHP arrays can contain almost anything without limits. What I would like is a way to make these a little more robust by being able to dictate which types are in what order in an array.

```php
class Location
{
    // ...

    public function getCoordinates(): [float, float]
    {
            return [$this->latitude, $this->longitude];
        }
    }
}
```

## Associative array notation

For the same reasons as basic tuple notation - arrays can contain absolutely anything and it's not always obvious what's what.

Associative arrays are a particular structure that often gets abused to pass around a lot of structured data when an object would be more appropriate. While refactoring these unholy arrays entirely would be ideal, that doesn't always happen.

It would be nice to enforce certain keys and types in an associative array when they are used.

```php
class Article
{
    // ...

    protected $id;

    protected $title = '';

    public function toArray(): ['id' => ?int, 'title' => string]
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
```

## Array contents type-hint

It's quite common to want an array to only contain one type of item instead of letting anything be inserted into it.

This can already be achieved through docblocks and verbose userland code, but it's such a common scenario in any application that it should be handled more consistently and performantly at the language level.

```php
class UserCollection extends Collection
{
    protected User[] $users;

    public function __construct(User[] $users)
    {
        $this->users = $users;
    }
}

```

## Generics`<T>`

Generics are a pattern that can be used to strongly type-hint things in a class, but by defining the type where the class is instantiated or referenced, instead of in its source.

Say we want a `Collection` class that can only contain `User` objects within it. The way most people PHP developers would handle this now is by extending an abstract `Collection` class into a specific `UserCollection` class and overriding methods to enforce this at runtime.

This might be fine when more specific functionality is required for a collection of users than a generic collection, but 99% of the time that isn't needed.

Generics would let this be done with just the base `Collection` class.

The source will instead accept a placeholder (in this example, `<T>`) as a dynamic type.

The place the class is used, such as in a "new" keyword or parameter and return types, will instead declare the type that should be used.

```php
class Collection<T>
{
    protected T[] $values;

    public function __construct(T[] $values)
    {
        $this->users = $values;
    }

    public function getLatest(): T
    {
        // ...
    }
}

class User extends Model
{
    // ...

    public static function getAllAdmins(): Collection<User>
    {
        return new Collection<User>(
            User::where('admin')->get()
        );
    }
}
```

With generics, it means that a single class can be used with strongly typed members, without needing to enforce this by not needing to extend an abstract into multiple various classes for each type in the source.

## Union types

Even with strong type hinting in place where it can be , there are times where a value may be one of many types at any given time. Instead of not declaring a type and falling back to docblocks and userland assertions, instead, we could declare a list of permitted types.

```php
class User extends Model
{
    // ...

    public function getLatestUserActivity(): ?Thread|Post|Favorite
    {
        return $this->getUserActivity()
            ->sortBy('date_created')
            ->first();
    }
}
```

Arguably, this is where interfaces and other object-oriented patterns should come in to play, with a bit of refactoring - but that is not always easy, especially when working with vendor objects or primitive values.

This is something that [catch blocks have been able to do since PHP 7.1](https://www.php.net/manual/en/language.exceptions.php#example-285) to catch multiple types of exceptions.

## Variable type-hint

In PHP, we can already declare what type something should be in a lot of places; function parameters, returns and now class properties in PHP 7.4.

This covers most use cases, however, there are some situations where you can't be 100% sure of something's type —  particularly in sections of procedural code where a variable may be set to the result of a magic method chain or methods from an inherited class that doesn't care about the type.

This can, of course, be dealt with in userland code by using functions like `gettype()` and `is_string()`, language constructs like `instanceof`, or just passing the variables through something else that already enforces types - but these can again get verbose and detract from what the code is actually trying to do.

```php
$user:User = User::find(1);

$firstPost:?Post = $user->posts()->first();
```

## Method overloading

Method overloading is a pattern commonly found in other programming languages that lets a class have more than one method with the same name - so long as their arguments are different.

It allows a class to have a simple polymorphic API that can handle many different scenarios without introducing procedural complexity to handle each case differently.

```php
class DataExtractor
{
    public function getBody(Article $article)
    {
        return $article->articleBody;
    }

    public function getBody(Review $review)
    {
        return join(PHP_EOL, $review->getParagraphs());
    }
}

class DateDiffForHumans
{
    public function getTimeAgo(Carbon $date): string
    {
        return $date->diffForHumans();
    }

    public function getTimeAgo(string $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }

    public function getTimeAgo(string $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }

    public function getTimeAgo(int $year, int $month, int $day): string
    {
        return Carbon::create($year, $month, $day)->diffForHumans();
    }
}
```

Adam Wathan has [built a trait](https://gist.github.com/adamwathan/120f5acb69ba84e3fa911437242796c3) you can use to achieve this kind of functionality, but it's less than ideal. I'd recommend looking at the example he gives since there are a lot of scenarios where this can come in useful.

## Callable types

Passing callable types around is becoming an increasingly frequent thing in PHP, and it's only going to become more common as short closures are being introduced in PHP 7.4 and will be that much easier.

```php
function reduce($a, $b, callable $reducer)
{
    return $reducer($a, $b);
}

$result = reduce(666, 671, function($a, $b) {
    return $a + $b;
});

$result; // 1337
```

The problem with this is that, other than through expensive reflection, there's no way to declare what type of parameters a callable value should accept, nor what it should return. This can be problematic and lead to a lot of boilerplate type checking if you expect a callable to conform to a particular API.

Ideally, this would be available as an option, possibly using a syntax such as `callable(int, int):int $reducer` when hinting an object.

```php
function reduce(int $a, int $b, callable(int, int):int $reducer): int
{
    return $reducer($a, $b);
}

$result = reduce(666, 671, function(int $a, int $b): int {
    return $a + $b;
});

$result; // 1337
```

## Making Code Cleaner

You might have noticed a common theme among this wishlist... most of this can already be done in userland code. It might be a little verbose in places, taking away from seeing what the actual objective of the code is, but they're mostly possible.

What I want when I look at code is for it to be clear and expressive - procedural code should read like a sentence and everything should be obvious as to what it does.

<x-quote name="Uncle Bob" title="in his book 'Clean Code'">
Indeed, the ratio of time spent reading versus writing is well over 10 to 1. We are constantly reading old code as part of the effort to write new code. ...[Therefore,] making it easy to read makes it easier to write.
</x-quote>

When we have to stop writing code essential to the business logic in an application in order to deal with type checking just to make sure things are handled properly, we write code that detracts from what we're really trying to achieve.

## Conclusion

PHP is an insanely powerful language and you can do a lot with it, and the reflection API, in particular, brings another huge layer of control to developers in userland. As I said earlier, I'm personally very happy with where the language is right now and where it's going in the near future.

What would you think if these changes were introduced to PHP? What else would you like to see done to PHP's typing system?
