---
title: Prepending a String Key to an Associative Array in PHP
slug: prepending-a-string-key-to-an-associative-array-in-php
published_at: 
updated_at: 
strapline: 
synopsis: Learn how to prepend a string key to an associative array in PHP.
tags:
    - PHP
---

Associative arrays in PHP are ordered - though they may not have a numerical index, their order does matter and can affect the way they are iterated over.

When you want to add a new key to an associative array, you might want to add it to the beginning of the array, rather than the end, but it might not be obvious how to do this.

If you try to simply define a new key on an existing array, by default it'll get appended to the end of the array.

```php
$array = [
    'Existing Key' => true,
];

$array['New Key'] = true;

// [
//     'Existing Key' => true,
//     'New Key' => true,
// ]
```

A handful of other methods you might use to prepend something in a regular array, like `array_unshift` or `array_splice`, won't work with associative arrays, as they require numeric keys.

Instead, both the `+` operator and the `array_merge` function can be used to prepend a new key to an associative array.

```php
$array = [
    'Existing Key' => true,
];

// Using the + operator
$array = [
    'New Key' => true,
] + $array;

// Using array_merge
$array = array_merge([
    'New Key' => true,
], $array);

// [
//     'New Key' => true,
//     'Existing Key' => true,
// ]
```

Both of these methods take into account the order the original arrays are used, so take that into account when using them.
