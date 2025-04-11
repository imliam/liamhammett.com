---
title: Twig Push/Stack Extension
alternate_title: 
slug: twig-pushstack-extension
published_at: 2025-04-11
updated_at: 
strapline: 
synopsis: 
previous_article: 
next_article: 
tags:
    - PHP
    - Open Source
---

When working with Twig, you might find yourself needing to push content in one file (like a component or partial) into a place in another file.

While you can achieve a similar effect with Twig's built-in `{% block %}` directive, [Laravel's Blade's `@@push` and `@@stack` directives](https://laravel.com/docs/11.x/blade#stacks) is far more versatile and functional, with no reliance on the template-extending syntax or order of the placements.

To bring this functionality to Twig, [a colleague of mine, Chris Powell](https://ampedweb.dev/blog/twig-pushstack-extension) created the [Twig Stack Extension](https://github.com/futureplc/twig-stack-extension) package that works in pretty much the same way as Blade's implementation.

For example, pushing into a named stack:

```twig
<!-- components/datepicker.twig -->
<input type="text" class="datepicker" />

{% pushonce 'scripts' %}
    <script src="/path/to/datepicker-lib.js" />
{% endpushonce %}
```

And rendering a named stack:

```twig
<!-- layout.twig -->
<html>
    <head>...</head>
    <body>
        ...
        {% stack 'scripts' %}
    </body>
</html>
```

You can get the package below.

<https://github.com/futureplc/twig-stack-extension>

Or see Chris' post about why we created it and didn't use an existing solution here:

<https://ampedweb.dev/blog/twig-pushstack-extension>
