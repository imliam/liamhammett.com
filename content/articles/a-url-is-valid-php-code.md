---
title: A URL is valid PHP code?!
slug: a-url-is-valid-php-code
published_at: 
updated_at: 
strapline: 
synopsis: You can put a link straight into your PHP code, and it'll be fine, even if it's not in a comment!
tags:
    - PHP
---

I accidentally pasted a URL into my code, and it worked?!

You can put a link straight into your PHP code, and it'll be fine, even if it's not in a comment! <u>How?!</u>

- `https:` is a [label for a `goto` statement](https://www.php.net/manual/en/control-structures.goto.php), a syntax we seldom use in modern PHP
- `//` and everything after it is treated as a comment, so is ignored and not actually parsed

It's pretty dumb in hindsight, but it's a fun little quirk that got me to think about how my code wasn't breaking when I expected a fatal error for my bad syntax. I wouldn't ever intentionally use this quirk over a real comment, but it's interesting to know that it's possible.

One thing to note is that you can only define a single "goto label" of a given name defined in the current scope, so if you have multiple URLs in your code, you can only have one `https`, one `http` and so on unless they're in different files/functions.

```php
<?php

// The first time it's absolutely fine:
https://liamhammett.com

// The second time, you get a fatal error:
https://example.com
```

<x-alert type="error">Fatal error: Label 'https' already defined</x-alert>
