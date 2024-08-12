---
title: Private Constructors
slug: private-constructors
published_at: 2019-07-02
strapline: ""
synopsis: ""
tags:
    - PHP
---

Private constructors are a pattern found in object-oriented programming languages that prevents the class from being instantiated, except by itself.

The first time I saw this pattern in my programming career, I was confused. It wasn't immediately apparent why such a feature would ever be beneficial in the real world. How are you meant to use the class if it can't be instantiated? Why even bother defining a constructor at all if it can't be called?

It turns out there are a handful of uses that private constructors can lend themselves to. Here I'm going to go over a few purposes they serve. The examples are in PHP but should transfer to any language that supports this feature.

## The basics

When you try to instantiate a class that has a private constructor, you'll be greeted with a fatal error as if you tried to call any other private method.

```php
class Fruit
{
    private function __construct()
    {
        // ...
    }
}

new Fruit();
// Fatal error: Uncaught Error: Call to private Fruit::__construct() from invalid context...
```

However, methods can be overridden by extending the parent class and exposing a new public one - this means the class can still be instantiated by people using it unless it is declared as `final` so that it can not be extended.

If an extended class declares its own constructor, the original private one will not be executed.

```php
class Apple extends Fruit
{
    public function __construct()
    {
        // ...
    }
}

new Apple();
// This is fine 🍎
```

Because a private method can still be called from the original class it's defined within, the only way to execute a private constructor is from a static method, which does not require an instance of the object to execute.

```php
final class Apple
{
    private function __construct()
    {
        // ...
    }

    public static function make(): Apple
    {
        return new Apple();
    }
}

Apple::make();
// We get an Apple object instance and the constructor method is executed 🍎
```

Now that we have a fundamental understanding of how we can interact with private constructors let's take a look at some patterns we can implement with them.

## Singletons

A singleton is an object that can only ever be instantiated once, and never any more times throughout the execution of the application. This is commonly seen implemented in an application's dependency injection container or a web framework's request/response objects.

With a private constructor, we can force the users of the class to interact with it through a static method that enforces this condition with our own code, instead of entrusting the user to do this themselves.

```php
final class Singleton
{
    private static Singleton $instance;

    private function __construct()
    {
        // ...
    }

    public static function getInstance(): Singleton
    {
        if (is_null(static::$instance)) {
            static::$instance = new Singleton();
        }

        return static::$instance;
    }
}

var_dump(Singleton::getInstance());
// object(Singleton)#1 (0) {}

var_dump(Singleton::getInstance());
// object(Singleton)#1 (0) {}
```

Note that no matter how many times we call `Singleton::getInstance()`, the same one instance of the object is returned, with the same identifier.

## Class only has static methods

Sometimes a class serves no purpose but to house static methods that act as utilities under a common namespace or purpose, so there is no reason for it *ever* to be instantiated.

While this can be achieved by declaring the class as `abstract` (so that it cannot be instantiated itself, only extended classes can do so), this can also be enforced through an empty private constructor.

```php
final class StringUtilities
{
    private function __construct() {}

    public static function upper(string $value): string
    {
            return strtoupper($value);
    }

    public static function lower(string $value): string
    {
        return strtolower($value);
    }

    // ...
}
```

## Predefined values

Sometimes you may want to be able to instantiate an object, but as the constructor sets the initial state for the whole object, you want to have more control over what goes into it, such as having predetermined values it might accept.

Another possibility is the desire to parse some values to normalise the arguments that get passed into the constructor; however, in most programming languages, this is typically handled through [function overloading](https://en.wikipedia.org/wiki/Function_overloading).

```php
final class PaymentFailedException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function lowBalance()
    {
        return new PaymentFailedException('A payment could not be made as the account in question does not have the funds required.');
    }

    public static function fraudulentTransaction()
    {
        return new PaymentFailedException('The current payment has been determined to be fraudulent and has been automatically declined.');
    }

    // ...
}

throw PaymentFailedException::lowBalance();

```

## Enums

Because PHP has no native enum type, they're typically handled by adding constants to a class. Because constants can be accessed statically, these classes never need to be instantiated.

With a completely empty private constructor, we can prevent instances of these from being created.

```php
final class ArticleStatus
{
    const PUBLISHED = 0;
    const DRAFT = 1;
    const DELETED = 2;

    private function __construct() {}
}

class Article extends Model
{
    // ...

    public function delete()
    {
        $this->setStatus(ArticleStatus::DELETED);
    }
}

```

## Conclusion

By now you've seen that there are some genuine uses that private constructors can be a solution for, even though they are often overlooked in favour of other features a programming language may have, such as the `abstract` keyword or method overloading.

If you know of any other handy uses for private constructors that I've missed here, please let me know in the comments below.
