---
title: "PHP Wishlist: Operator Overloading"
slug: php-wishlist-operator-overloading
published_at: 2019-05-24
strapline: ""
synopsis: ""
previous_article: php-wishlist-typing
tags:
    - PHP
---

**Note**: Since writing this post, I came across the `pecl-php-operator` extension on GitHub that does exactly what I described in this post, it’s awesome!

<https://github.com/php/pecl-php-operator>

PHP is a great language that puts a lot of power into the hands of the developer. The core language supports a handful of [magic methods](https://www.php.net/manual/en/language.oop5.magic.php) and [interfaces](https://www.php.net/manual/en/reserved.interfaces.php) that can be implemented into classes that lets the developer choose how an object should behave in certain situations, like when it's being iterated over or put through core functions like `count()`.

These have a lot of use cases that allow objects to have a fluent API and be interacted with in an intuitive way. A primary example of this being used is in [Laravel's collection class](https://github.com/laravel/framework/blob/5.8/src/Illuminate/Support/Collection.php#L40), which implements many of these so it can be interacted with like any regular array.

```php
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    // ...

    public function __toString() { /* ... */ }

    public function __get($key) { /* ... */ }
}
```

There's a strong argument that making objects "magic" like this is a bad design pattern as it abstracts important functionality away from the developer and can make it unclear as to what's actually happening behind the scenes.

This is definitely an argument to consider, but I believe that with moderation and a well-thought-out implementation, they can reduce perceived complexity and make our code cleaner so we can focus on business logic, instead of the semantics of interacting with an object.

Because of this, I think PHP can go a step further than it already has, and let objects decide how they should interact with different [operators](https://www.php.net/manual/en/language.operators.php) in the language, like when it's compared with another value, multiplied by one, or anything else you can imagine.

## How PHP Could Implement It

This is not a new pattern for programming languages to adopt; notably C++, C#, Python and Ruby all have some form of operator overloading. Let's take a look at Lua's...

### Lua Metamethods

The prototype-based programming language "[Lua](https://www.lua.org/)" is a notable example that allows "tables" (similar to PHP arrays in that they can be flat or associative, and contain any other data type) to be extended with functions and additional properties, just like PHP objects.

[Lua has a handful of "metamethods"](https://www.lua.org/manual/5.3/manual.html#2.4) that can be added, which extend the behaviour of the object in different scenarios. However, it has a whole other set of these metamethods that PHP does not offer, can be executed when basic language constructs like comparison and modifying operators are used on the object.

```lua
-- The original table is just a simple list of strings
originalTable = { 'a', 'b', 'c' }

-- Create a "metatable" to let us declare special methods on it
specialTable = setmetatable(originalTable, {
    -- Define a special function that is executed every time
    -- the "add" operator is used with this special table
    __add = function(specialTable, newTable)

        -- Iterate over each item in thew new table
        for i = 1, #newTable do
            -- Insert the values from the 2nd table into the 1st
            table.insert(specialTable, #specialTable + 1, newTable[i])
        end

        return specialTable
    end
})

results = specialTable + { 'd', 'e', 'f' }

-- The "results" variable now includes the following values:
-- { 'a', 'b', 'c', 'd', 'e', 'f' }
```

### PHP Core Already Does This

The PHP core already implicitly allows this kind of behaviour in a couple of places. For example, the `+` operator may perform an addition between two numbers, but perform a union between two arrays.

```php
5 + 2 === 7;
2 + 5 === 7;

['a' => true] + ['b' => true] === ['a' => true, 'b' => true];
```

What's interesting is that the union of two arrays can also result in two different outcomes depending on which array comes first, which is a problem we would have to look out for if we were to implement this ourselves.

```php
$one = [
    'a' => true,
    'b' => true,
];

$two = [
    'b' => false,
    'c' => true,
];

$one + $two === [
    'a' => true,
    'b' => true,
    'c' => true,
];

$two + $one === [
    'b' => false,
    'c' => true,
    'a' => true,
];
```

Comparison operators can also be used with DateTime objects to compare if one comes before another.

```php
$one = new DateTime('2016-01-01');
$two = new DateTime('2019-04-30');

$one < $two === true;
$one > $two === false;
```

### Implementing It In Userland for PHP

There's a variety of ways PHP could implement this kind of functionality to allow developers to make use of it, two primary ones being:

- **Standalone magic methods**. The use of any one operator does not necessarily rely on another, so they can be implemented one-by-one if needed.
- **Interfaces**. This could enforce methods for similar operators to all be implemented simultaneously, so you'd never run into a situation where `$a > $b` works but `$a < $b` is not implemented.

#### Left Associativity

I imagine these magic methods would all be left associative. That is, they will only trigger if the object they're within is on the left side of an operation. A single value will be passed as a parameter into the method; the value on the right side of the operation.

This way, both sides of the operation will be available in the method's scope, `$this` to access the left side and the one parameter for the right side.

This could get confusing as it means `$a + $b` may not generate the same result as `$b + $a` if the two variables are not the same. Similarly to using the union operator on two arrays, this is just an implementation detail the developer has to be aware of.

#### Types

These magic methods would be able to accept any type as its argument. This means that any type-checking is up to the developer to sanitise and validate.

#### Example

Let's take a look at how this might work in practice. Instead of remembering to use functions like `bcmath()` instead of the addition operator when working with the [BCMath extension](https://www.php.net/manual/en/book.bc.php), we could make it implicit and natural like it would be working with regular numbers.

```php
class BCNumber
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __add($toAdd)
    {
        if (is_string($toAdd) && is_numeric($toAdd)) {
            return new BCNumber(bcadd($this->value, $toAdd));
        }

        if (is_integer($toAdd)) {
            return new BCNumber(bcadd($this->value, $toAdd));
        }

        if ($toAdd instanceof BCNumber) {
            return new BCNumber(bcadd($this->value, $toAdd->value));
        }

        throw new InvalidArgumentException('Can not add type ' . gettype($toAdd) . ' to BCNumber.');
    }
}

$exampleOne = new BCNumber(5);
$exampleTwo = new BCNumber(2);

$exampleThree = $exampleOne + $exampleTwo;
echo $exampleThree->value; // 7
```

## The Operators

Now that we've got an understanding of how these could be used, let's take a look at what operators could be made available.

### Modifying operators

These are operators in PHP take two different values and return something else entirely.

```php
interface Operatable
{
    /** $this . $value */
    public function __concatenate($value);

    /** $this + $value */
    public function __add($value);

    /** $this - $value */
    public function __subtract($value);

    /** $this * $value */
    public function __multiply($value);

    /** $this / $value */
    public function __divide($value);

    /** $this % $value */
    public function __modulo($value);

    /** $this ** $value */
    public function __exponent($value);

    /** $this & $value */
    public function __bitwiseAnd($value);

    /** $this | $value */
    public function __bitwiseOr($value);

    /** $this ^ $value */
    public function __bitwiseXor($value);

    /** $this << $value */
    public function __shiftLeft($value);

    /** $this >> $value */
    public function __shiftRight($value);
}
```

Let's take a look at another example of how one of these methods might be implemented in a real-world collection object to combine two internal arrays, similarly to the array union operator.

```php
class Collection
{
    public $array = [];

    public function __construct(array $array)
    {
        $this->array = $value;
    }

    public function __add($value)
    {
        if (is_array($value)) {
            return new Collection(array_merge($this->array, $value));
        }

        if ($value instanceof Collection) {
            return new Collection(array_merge($this->array, $value->array));
        }

        throw new InvalidArgumentException('Can not add type ' . gettype($toAdd) . ' to Collection.');
    }
}

$collectionOne = new Collection(['a', 'b', 'c']);
$collectionTwo = new Collection(['d', 'e', 'f']);

$newCollection = $collectionOne + $collectionTwo;
$newCollection->value; // ['a', 'b', 'c', 'd', 'e', 'f']
```

### Comparison operators

Some operators in PHP are used for comparisons between two objects. They return a boolean value to determine if a comparison was true or not - they're the cornerstone of logic in any programming language.

The only difference with the magic methods for these operators is that they have an expected return type.

The spaceship operator returns an integer of `-1`, `0` or `1`, not a boolean value*

```php
interface Comparable
{
    /** $this == $value */
    public function __equal($value): bool;

    /** $this === $value */
    public function __identical($value): bool;

    /** $this != $value */
    /** $this <> $value */
    public function __notEqual($value): bool;

    /** $this !== $value */
    public function __notIdentical($value): bool;

    /** $this > $value */
    public function __greaterThan($value): bool;

    /** $this < $value */
    public function __lessThan($value): bool;

    /** $this >= $value */
    public function __greaterThanOrEqualTo($value): bool;

    /** $this <= $value */
    public function __lessThanOrEqualTo($value): bool;

    /** $this <=> $value */
    public function __spaceship($value): int;
}
```

Let's take another look at an example of where this could be used - an application that deals with money in multiple currencies. When comparing one monetary value to another, it could implicitly convert both values to the same currency using the current exchange rate.

```php
class Money
{
    // ...

    public function __greaterThan($value): bool
    {
        if (! $value instanceof Money) {
            throw new Exception('Cannot compare with type other than Money');
        }

        return $this->amount > $value->convertToCurrency($this->currency)->amount;
    }
}

$currentBalance = new Money(50, 'GBP');
$transactionAmount = new Money(25, 'USD');

if ($transactionAmount > $currentBalance) {
    throw new Exception('You do not have enough balance to make this transaction.');
}
```

### Unary operators

Unary operators are unique in that they only take a single value and do something with it. This means any magic methods for unary operators will not accept any parameters, nor expect any one type as a response - they could do anything with the current object and return any value.

```php
interface UnaryOperatable
{
    /** !$this */
    public function __unaryNot();

    /** +$this */
    public function __unaryAdd();

    /** -$this */
    public function __unarySubtract();

    /** ~$this */
    public function __unaryBitwiseNot();

    /** ++$this */
    public function __preIncrement();

    /** $this++ */
    public function __postIncrement();

    /** --$this */
    public function __preDecrement();

    /** $this-- */
    public function __postDecrement();
}
```

### Assignment Operators

There are also a handful of [assignment operators in PHP](https://www.php.net/manual/en/language.operators.assignment.php) that are not to be ignored. These could either be extended to inherit some of the behaviours of the primary operators they relate to, or have their own magic methods

My primary concern here is that things may get funky if an object is designed to be immutable. The developer *could technically* already alter an object's state inside any comparison or modifiable operator's magic methods, but it's not an expected outcome like an assignment operator would be.

An `Immutable` interface could be implemented into such classes that would prohibit the use of these operators by throwing an error if they are used.

```php
class Example implements Immutable
{
    // ...

    public function __add($value)
    {
        // ...
    }
}

$example = new Example();
$example += 'Value'; // PHP Fatal error:  Uncaught Error: Object of type 'Example' is immutable and can not be modified.

```

## Conclusion

There are several times throughout my development with PHP that I would have found some of these magic methods useful, and I would love to see them in the future.

I'm well aware that there would be problems with such an approach in some circumstances, and that not everyone is a fan of magic methods in the first place, let alone adding more.

This is not an RFC to the PHP core, although I would be delighted if someone were to take it up. It is merely something that would make me enjoy programming with PHP that little bit more if it were to ever make it into the core.

What are your thoughts on magic operator methods?
