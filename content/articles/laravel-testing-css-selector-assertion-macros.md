---
title: Laravel Testing - CSS Selector Assertion Macros
slug: laravel-testing-css-selector-assertion-macros
published_at: 2020-01-30
strapline: ""
synopsis: ""
tags:
    - PHP
    - Laravel
    - Testing
---

Laravel offers some very useful ways to test that your pages are being rendered with the right content by making assertions directly on the response object.

It's such an elegant and fast way to be sure your application is doing what you want, but doesn't necessarily always do what you might want.

A test doing this in Laravel may look like the following, creating an article then making sure the title of that article shows on the listing page:

```php
/** @test */
public function an_article_will_show_on_the_listing_page()
{
    $article = factory(Article::class)->create();

    $this->get('/articles')
        ->assertSee($article->title);
}
```

Awesome!

However, the `assertSee` method is very vague, and may not test exactly what you want it to. All it'll do is assert that the given text is anywhere in the response, but it doesn't care where.

This can cause problems that you can easily run into without realising...

**Is the asserted text already vague?** If you call `assertSee('html')` it'll always pass, as it'll find that text in the opening `<html>` element of every page.

**Could the text show up in multiple places already?** Perhaps you have an article listing as the main content, but also have a sidebar that shows the latest posted articles, and the name can show up in both places.

Wouldn't it be better if you could be more specific about *where* you see the text, like in a particular CSS selector?

## Testing Macros

Luckily, the Laravel `TestResponse` class is "macroable" - meaning that at runtime, you can add custom methods to the class - even without extending it or bringing the class into your own application's code.

Typically, we would add macros to a class in the `register` method of a service provider.

If you're not familiar with macros and want to learn more, Tighten has a [great article about them](https://tighten.co/blog/the-magic-of-laravel-macros) that you should check out.

## Selecting nodes with a CSS selector

When parsing HTML documents in PHP, it's common to use a parsing library that's readily available, most commonly the [DOM library](https://www.php.net/manual/en/book.dom.php) that comes with PHP. While it lets us interact with HTML nodes as if they were PHP objects, it unfortunately doesn't come with any way to use CSS selectors.

However, it does let you make selections on the DOM using XPath - another query language used for XML and HTML documents. XPath is similar to CSS selectors in some ways, but far from the same.

Personally, as a developer that uses CSS selectors every day through writing JavaScript and CSS, I would rather keep using them instead of having to switch my brain back to something else.

Luckily, Symfony has already solved this problem with their [css-selector package](https://symfony.com/doc/current/components/css_selector.html) that handles this case perfectly - it takes a CSS selector and turns it into an XPath selector, so we can use it to query the DOM.

Before moving on, make sure you have that package installed in your project by requiring it with Composer.

```bash
composer require symfony/css-selector
```

The following macro will handle all of this for us - accepting a selector and returning the list of matching nodes from the response. We won't use it directly in our tests, but it will be used by our other assertion macros later on.

```php
use DOMXPath;
use DOMDocument;
use DOMNodeList;
use Illuminate\Foundation\Testing\TestResponse;
use Symfony\Component\CssSelector\CssSelectorConverter;

TestResponse::macro('getSelectorContents', function (string $selector): DOMNodeList
{
    $dom = new DOMDocument();

    @$dom->loadHTML(
        mb_convert_encoding($this->getContent(), 'HTML-ENTITIES', 'UTF-8'),
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    $converter = new CssSelectorConverter();
    $xpathSelector = $converter->toXPath($selector);

    $xpath = new DOMXPath($dom);
    $elements = $xpath->query($xpathSelector);

    return $elements;
});
```

## Assertions

Now that we've got a method in place that handles this boilerplate for us, we can move on to actually making some assertions that we can use for our tests!

## assertSelectorContains($selector, $value)

This method will assert that the given string value is contained within the response in any matching CSS selector.

However, it's worth noting that if a selector returns multiple elements on a page, if any one of them contain the given text, the assertion will pass.

```php
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\Assert as PHPUnit;

TestResponse::macro('assertSelectorContains', function (string $selector, string $value) {
    $selectorContents = $this->getSelectorContents($selector);

    if (empty($selectorContents)) {
        PHPUnit::fail("The selector '{$selector}' was not found in the response.");
    }

    foreach ($selectorContents as $element) {
        if (Str::contains($element->textContent, $value)) {
            PHPUnit::assertTrue(true);

            return $this;
        }
    }

    PHPUnit::fail("The selector '{$selector}' did not contain the value '{$value}'.");

    return $this;
});
```

Here is how we may use it a test to be specific about where we see a given bit of text:

```php
/** @test */
public function an_article_will_show_on_the_listing_page()
{
    $article = factory(Article::class)->create();

    $this->get('/articles')
        ->assertSelectorContains('main h2.article-title', $article->title);
}

```

## assertSelectorsAllContain($selector, $value)

Similarly, this method will assert that the given string value is contained within the response in **all** matching CSS selectors - not just one.

```php
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\Assert as PHPUnit;

TestResponse::macro('assertSelectorsAllContain', function (string $selector, string $value) {
    $selectorContents = $this->getSelectorContents($selector);

    if (empty($selectorContents)) {
        PHPUnit::fail("The selector '{$selector}' was not found in the response.");
    }

    foreach ($selectorContents as $element) {
        if (!Str::contains($element->textContent, $value)) {
            PHPUnit::fail("The selector '{$selector}' did not contain the value '{$value}'.");

            return $this;
        }
    }

    PHPUnit::assertTrue(true);

    return $this;
});
```

The parameters for this method match the previous one exactly, so using it should be very familiar:

```php
/** @test */
public function an_article_will_show_on_the_listing_page()
{
    factory(Article::class, 5)->create([
	    'title' => 'Hello world',
	]);

    $this->get('/articles')
        ->assertSelectorsAllContain('main h2.article-title', 'Hello world');
}
```

## assertSelectorAttributeEquals($selector, $attribute, $expected)

The previous assertions only work on the text content of a node. If you want to be a bit more specific about the assertions made in the test and make sure that a particular attribute on a node is what you want, this method will do that.

```php
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\Assert as PHPUnit;

TestResponse::macro('assertSelectorAttributeEquals', function (string $selector, string $attribute, $expected) {
    $nodes = $this->getSelectorContents($selector);

    if (count($nodes) === 0) {
        PHPUnit::fail("The selector '{$selector} was not found in the response.");

        return $this;
    }

    $firstNode = $nodes[0];

    PHPUnit::assertEquals($expected, $firstNode->getAttribute($attribute));

    return $this;
});
```

For example, you might use this to assert that some meta tags are correct:

```php
/** @test */
public function hreflangs_will_link_to_the_correct_region_page()
{
    $this->get('/')
        ->assertSelectorAttributeEquals('link[hreflang="x-default"]', 'href', 'https://www.liamhammett.com/')
        ->assertSelectorAttributeEquals('link[hreflang="fr-FR"]', 'href', 'https://www.liamhammett.com/fr-fr/')
        ->assertSelectorAttributeEquals('link[hreflang="no-NO"]', 'href', 'https://www.liamhammett.com/no-no/');
}
```

## Conclusion

These are just a few methods that will work out-of-the-box to help make some more accurate assertions, but there are many other cases that could be handled with some additional macros.

A disclaimer, however - is that CSS selectors aren't appropriate to use for every testing scenario. Your HTML can be shuffled around at a whim, CSS class names may change, and overuse of either terse or vague selectors will make your tests very brittle.

Sometimes you may reach for adding an ID to an element in the source code to help your tests hook into it. Figure out what works for you.

You may want need to reach for these assertion methods when `assertSee` doesn't cut it - I hope they help.

If you'd just like to get these macros working in your project quickly, you can copy [this prebuilt mixin](https://gist.github.com/imliam/0a373d86dbf3d8706d057f184dd83cf3) and register it.