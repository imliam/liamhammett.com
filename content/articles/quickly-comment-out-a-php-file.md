---
title: Quickly Comment Out a PHP File
alternate_title: 
slug: quickly-comment-out-a-php-file
published_at: 2024-08-29
updated_at: 
strapline: 
synopsis: Use an early return to prevent any code from executing in a PHP file.
previous_article: 
next_article: 
tags:
    - PHP
---

What do you do if you need to "comment out" a whole PHP file?

You could prefix every line with `//`, wrap the whole thing in a `/* */` block, wrap it in `if (false) { }`, but none of those are very elegant. In most IDEs, if you select the whole file <kbd><x-keyboard-cmd />+A</kbd> and use a hotkey to comment it <kbd><x-keyboard-cmd />+/</kbd>, it'll actually wrap it in HTML comments `<!-- -->` because the code outside the PHP tags is typically HTML.

There's a little technique I spotted [Nuno Maduro](https://twitter.com/enunomaduro) using in his talk at Laracon US 2024 - it feels obvious in hindsight - just add an early return immediately after the opening PHP tag - then nothing after it will be loaded at all:

```php
<?php return;

none_of_this_code_will_execute();
```
