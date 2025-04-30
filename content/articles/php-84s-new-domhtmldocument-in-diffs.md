---
title: PHP 8.4's new Dom\HTMLDocument in Diffs
alternate_title:
slug: php-84s-new-domhtmldocument-in-diffs
published_at: 2025-05-01
updated_at:
strapline:
synopsis:
previous_article:
next_article:
tags:
    - PHP
---

With [PHP 8.4's release](https://www.php.net/releases/8.4/en.php), we have a new `Dom\HTMLDocument` class that makes working with HTML documents easier and more standards-compliant, while fixing some long-standing bugs in the old `DOMDocument` class.

For the most part the interfaces are similar and provide similar functionality - you can still call `saveHTML` on a document instance to get the HTML content back as a string, you can still use `getElementById` to get an element by its ID, or `getElementsByTagName` to get a list by their tag name, there's still the the concepts of nodes/elements/node lists/etc. and they all behave pretty similarly.

But...

<x-alert type="warning">The new Dom\\* classes are not backwards-compatible, drop-in replacements for the old DOM* classes, they provide similar but ultimately different interfaces.</x-alert>

Because of the lack of backwards compatibility, PHP 8.4 introduces a new set of classes in the `Dom` namespace that provide a more modern and standards-compliant way to work with HTML and XML documents - the old classes remain in the global namespace with their old behaviour.

Let's take a look at some of the differences between the old and the new.

## Creating documents

An instance of the `Dom\HTMLDocument` class can be created with a static constructor method.

```php
- $document = new DOMDocument();
- $document->loadHTML($html);
- $document->loadFromFile($pathToFile);
+ $document = Dom\HTMLDocument::createFromString($html);
+ $document = Dom\HTMLDocument::createFromFile($pathToFile);
+ $document = Dom\HTMLDocument::createEmpty();
```

## New implied behaviour

Because the new DOM classes use a fundamentally different parser for HTML under-the-hood, you may start to see some new implied behaviour you hadn't seen in previous versions.

While both classes support partial HTML documents...

**The old `DOMDocument` class would...**
- silently add an implied doctype to the document (unless you pass it the `LIBXML_HTML_NODEFDTD` flag) if you don't provide a doctype
- add a `<html>` and `<body>` tag around the content if you don't provide them

**The new `Dom\HTMLDocument` class will...**
- not add a doctype of its own, but will throw an `unexpected-token-in-initial-mode` warning about a missing doctype
- add a `<html>`, `<body>` AND empty `<head>` tag around the content if you don't provide them

```php
- $document = new DOMDocument();
- $document->loadHTML('<html><b>Hello</b></html>');
# <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">\n
# <html><body><b>Hello</b></body></html>\n

+ $document = Dom\HTMLDocument::createFromString('<html><b>Hello</b></html>');
+ $document->saveHTML();
# "<html><head></head><body><b>Hello</b></body></html>"
# Warning: Dom\HTMLDocument::createFromString(): tree error unexpected-token-in-initial-mode in Entity, line: 1, column: 2-5 in /Users/liam.hammett/projects/personal-blog/SCRATCH.php on line 10
```

The implied behaviour can still be disabled in both with the `LIBXML_HTML_NOIMPLIED` flag.

## Querying CSS selectors

The old `DOMDocument` classes had no way to directly query based on CSS selectors, so this often meant folks would use a package like [symfony/css-selector](https://symfony.com/doc/current/components/css_selector.html) to convert a CSS selector into an XPath query.

Thankfully, the new `Dom\HTMLDocument` class has a `querySelector` method to get the first node that matches a CSS selector, and a `querySelectorAll` method that allows you to get a list of selectors back.

```php
- $converter = new Symfony\Component\CssSelector\CssSelectorConverter();
- $xpath = new DOMXPath($document);
- $nodes = $xpath->query($converter->toXPath($cssSelector));
+ $nodes = $document->querySelectorAll($cssSelector);
```

## Importing nodes from `DOMDocument` into `Dom\HTMLDocument`

The new DOM classes also include an `importNode` method, though they only accept `Dom\Node` instances. However, if you are still using the old `DOMNode` classes in some point of your codebase, there is a helpful helper method to convert them; `importLegacyNode`!

```php
$oldDocument = new DOMDocument();
$oldDocument->loadHTML('<!doctype html><html><body><div id="old-content">Div from old document</div></body></html>');

$newDocument = Dom\HTMLDocument::createFromString('<!doctype html><html><body><div id="new-content">Div from new document</div></body></html>');

$oldNode = $oldDocument->getElementById('old-content');

- $newNode = $newDocument->importNode($oldNode, true);
# Uncaught TypeError: Dom\Document::importNode(): Argument #1 ($node) must be of type Dom\Node, DOMElement given
+ $newNode = $newDocument->importLegacyNode($oldNode, true);

$newDocument->getElementsByTagName('body')[0]->appendChild($newNode);

$newDocument->saveHTML();
# "<!DOCTYPE html><html><head></head><body><div id="new-content">Div from new document</div><div id="old-content">Div from old document</div></body></html>"
```

## Performance

Out-of-the-box, the new `Dom\HTMLDocument` class is faster than the old `DOMDocument` class, with the new parser being more efficient and less memory-intensive.

Take a look at this simple benchmark for simply loading and saving a document:

```diff
- Execution time for DOMDocument over 1,000,000 iterations: 4.3049809932709s
+ Execution time for Dom\HTMLDocument over 1,000,000 iterations: 3.4053020477295s
```

## Documentation

As of wrting this, [the official documentation](https://www.php.net/manual/en/class.dom-htmldocument.php) is littered with `/** Not documented yet */` comments, with a lot of specific details missing. With time, this documentation is expected to improve and provide clearer guidance on the usage of the new classes and methods.

In the mean time, JetBrains have published a "stubs" file to help their PHPStorm understand the available methods and properties of the new classes. It is a plain PHP file with stubbed classes and methods that don't have any implementations, but give you the ability to see the available methods and their signatures. You can check it out here:

<https://github.com/JetBrains/phpstorm-stubs/blob/master/dom/dom_n.php>

## Conclusion

The new `Dom\HTMLDocument` class is a welcome addition to PHP 8.4, providing a more modern and standards-compliant way to work with HTML documents. Common tasks have an easier interface, it handles HTML better, and it's more performant - a win all around.

With time, I expct the documentation to improve, tooling to be built (such as Rector rulesets) that will help migrate the old implementations to the new one, and the community to embrace the new classes.

I for one am excited to use it in my projects!
