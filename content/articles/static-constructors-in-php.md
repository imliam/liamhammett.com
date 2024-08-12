---
title: Static Constructors in PHP
slug: static-constructors-in-php
published_at: 2020-04-23
strapline: ""
synopsis: ""
tags:
    - PHP
---

"Static constructors" are a concept a lot of object-oriented programming languages support - although, unfortunately, PHP does not.

If you're not familiar with the concept, a static constructor is just a method the developer can define on a class which can be used to initialise any static properties, or to perform any actions that only need to be performed *only once* for the given class. The method is only called once as the class is needed.

The [C# guide about static constructors](https://docs.microsoft.com/en-us/dotnet/csharp/programming-guide/classes-and-structs/static-constructors) is a good resource to see how another language handles these, however we'll cover the important bits below.

Let's take the following example class, where we'll use the `__constructStatic()` method as our static constructor.

```php
class Example
{
    public static $value;

    public static function __constructStatic()
    {
        // ...
    }

    public static function test()
    {
        // ...
    }
}
```

A static constructor method...

- takes no parameters
- should only be called once
- is executed BEFORE any instances of the class are created (`new Example`)
- is executed BEFORE any static properties are referenced (`Example::$value`)
- is executed BEFORE any other static methods are called (`Example::test()`)

## What are they used for?

A static constructor can have a couple of uses, but the primary one is to **initialise any static properties on the class**.

You may want to do this for any number of reasons, as it's not always possible to define the value of a static property in the class definition.

For example, if we wanted the property to contain a list of values read from a file, PHP doesn't let us execute code at runtime like this...

```php
class Countries
{
    public static array $values = json_decode(file_get_contents(__DIR__ . '/countries.json'), true);
}
```

Instead, to achieve this result we often fall back to using "getter" methods that can abstract this logic. We may also combine it with a memoisation technique so that we only call the expensive-to-run code once and then re-use the values we retrieved from it the first time.

```php
class Countries
{
    private static array $values;

    public static function getValues()
    {
        return static::$values ??= json_decode(file_get_contents(__DIR__ . '/countries.json'), true);
    }
}
```

This technique in itself is fine for most scenarios, but may not be what you want. If you wish to manipulate the value, you'll also likely want a setter to ensure the initial data is there - and you are forced to go through methods instead of interacting with the property itself.

We could instead house this logic in a static constructor. Let's see what that might look like...

```php
class Countries
{
    public static array $values = [];

    public static function __constructStatic()
    {
        static::$values = json_decode(file_get_contents(__DIR__ . '/countries.json'), true);
    }
}
```

Now we can interact with the property itself and call `Countries::$values` or even manipulate it `Countries::$values[] = 'GB'` no matter where in the codebase we are, without needing to worry about setters.

## How do I execute the static constructor?

PHP doesn't natively handle static constructors, so we need to do something to invoke it ourselves.

Here we've named the method `__constructStatic` to follow the existing PHP magic method conventions (such as `__construct` and `__callStatic`), but realistically you could name the method whatever you like.

No matter what it's named, we need to invoke it somehow - and it needs to be done in such a way that happens automatically to abide by the rules we laid out above, so we can expect consistent behaviour. We don't want to have to explicitly call the method *everywhere* that we use the class, that would be a pain and easy to forget to do.

## Just call it...

The simplest way to invoke a static constructor is to... just call it... in the same file as the class definition itself.

```php
class Example
{
    public static function __constructStatic()
    {
        // ...
    }
}

Example::__constructStatic();
```

It's that simple!

If you follow [PSR-4 autoloading](https://www.php-fig.org/psr/psr-4/), you should have one class per file anyway, and due to the way PHP's autoloading works, this file is executed once when the class is required.

Usually this just means the class will be defined, but we can do more, like invoking methods, in this same step effortlessly too.

This will execute the static constructor immediately when the class is requested, before any outside code has a chance to call any properties or methods itself - meaning this meets all of the requirements we listed above.

## Use an autoloader

If you wanted this to be a bit more automated and "feel" more like it was a part of PHP, you could instead opt to define a custom autoloader to invoke the method (if it's present on the class) when the class is first loaded in.

This approach can have its benefits, although it also has some notable downsides to take into consideration:

- This approach will have to rely on reflection to ensure the method is present before it attempts to invoke it - meaning all classes will be loaded slightly slower to account for this check
- It abstracts this non-standard functionality, in such a way that may be confusing to people not familiar with the implementation

If you did want to go this route, there are [some existing packages](https://github.com/vladimmi/construct-static/) available to get you started easily.

## Conclusion

As you can see, static constructors can open up some different avenues to structure your classes, letting you move away from the getter + setter method combination, and performing more logic up front.

However, if you're relying on a class's state in this way, chances are you should re-think how you're using classes to store values in an object instance, perhaps following the singleton pattern.

Static constructors are not a perfect solution all the time, and you may seldom use them, but at least now you're aware of them and can consider if they're suitable for your code going forward!
