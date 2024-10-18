---
title: PHP Shareable Social Media Links
slug: php-shareable-social-media-links
published_at: 2018-06-27
strapline: ""
synopsis: ""
tags:
    - PHP
---

![Example of how to use the package](/images/articles/php-shareable-social-media-links.webp)

I recently had the need to generate various "shareable" links to help share a particular URL to different social media platforms. The problem is that every platform requires information in a slightly different way.

Lo and behold, just a few days later, [Dennis Smink](https://medium.com/@dennissmink) shared their method of doing so in their article "[Laravel Shareable Trait](https://medium.com/@dennissmink/laravel-shareable-trait-1a6b12a05094)" that covers the few biggest platforms.

While their solution certainly works, I had a couple of issues with the implementation of it:

- Due to some functions used, its usage is limited to Laravel projects only, and to a trait on an Eloquent model
- There's a lot of needless repetition in the source
- I don't like the API it has  —  the options are limited to Eloquent columns and it's not immediately clear how the trait is to be configured.

So, I split this out into a separate package with a class you can use without the aforementioned limits.

```php
$url = new ShareableLink('http://example.com/', 'Example Site');

echo $url->facebook;
// https://www.facebook.com/dialog/share?app_id=ABC123&href=https://example.com/&display=page&title=Example+Site

echo $url->twitter;
// https://twitter.com/intent/tweet?url=https://example.com/&text=Example+Site

echo $url->whatsapp;
// https://wa.me/?text=Example+Site+https%3A%2F%2Fexample.com%2F

echo $url->linkedin;
// https://www.linkedin.com/shareArticle?mini=true&url=https://example.com/&summary=Example+Site

echo $url->pinterest;
// https://pinterest.com/pin/create/button/?media=&url=https://example.com/&description=Example+Site

echo $url->google;
// https://plus.google.com/share?url=https://example.com/**
```

Instead of a trait on an Eloquent model with an awkward configuration, you can just add a custom method so you have full control of the URL and title, if you wish to create them dynamically.

```php
class News extends Model
{
    public function getShareUrlAttribute()
    {
        $url = route('news.show', $this->slug);

        return new ShareableLink($url, $this->title);
    }
}
```

Check it out and get the package from GitHub below.

<https://github.com/imliam/shareable-link>
