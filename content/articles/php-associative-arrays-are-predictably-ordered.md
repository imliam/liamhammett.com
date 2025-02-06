---
title: PHP Associative Arrays Are Predictably Ordered
slug: php-associative-arrays-are-predictably-ordered
published_at: 2025-02-10
updated_at: 
strapline: 
synopsis: 
tags:
    - PHP
---

It's not uncommon to see people try and equate PHP's arrays to similar features in other programming languages, as PHP has a bit of a unique approach by using a single data structure for both ordered lists and key-value objects.

Some common comparisons include:

- PHP ordered array: Python lists, JavaScript arrays, Ruby arrays
- PHP associative array: Python dictionaries, JavaScript objects, Ruby hashes

However, because both of these concepts are a single data structure in PHP, that adds an oft-overlooked feature for newcomers to the language:

<mark>Associative arrays in PHP are predictably ordered.</mark>

Let's look at an example, a simple real-world use case; a navigation menu.

```php
// The order of the items in the source code determine
// the order they will be shown on the rendered page:
$navigation = [
    '/' => 'Home',
    '/blog' => 'Blog',
    '/about' => 'About us',
    '/contact' => 'Get in touch!',
];

// We can iterate over them like any ordered list:
foreach ($navigation as $link => $text) {
    echo "<a href=\"{$link}\">{$text}</a>\n";
}

// The output is always predictable:
// <a href="/">Home</a>
// <a href="/blog">Blog</a>
// <a href="/about">About us</a>
// <a href="/contact">Get in touch!</a>
```

Most languages allow iterating over key-value data structures, but they're not always guaranteed to be ordered like PHP's are. For example, the [Lua](https://www.lua.org/) programming language has a table data structure that can be used for both ordered lists and key-value objects, but the order of the string keys in a table are not guaranteed.

If you ever add a new key to the array, such as by defining `$navigation['/services'] = 'Our services';`, it will always be added to the end of the list when you iterate over the array. If you need to [prepend a key to an associative array, you can do that too](/prepending-a-string-key-to-an-associative-array-in-php).
