---
title: CSS Filter Utility Classes
slug: css-filter-utility-classes
published_at: 2018-09-12
strapline: ""
synopsis: ""
tags:
    - CSS
---

(Ab)using CSS variables to apply multiple filters with one utility

> Note: This article has a CodePen demo with inline examples, check it out towards the end.

The [filter property](https://developer.mozilla.org/en-US/docs/Web/CSS/filter) is a bit of an oddity when it comes to CSS.

Unlike most other properties, it can accept **any number** of named arguments, in **any order**, with each one being **optional**. This makes it extremely powerful and convenient, but a bit unorthodox to use, especially if you take a utility-first approach like [TailwindCSS](https://tailwindcss.com/) for styling.

A typical utility class would employ a single property with a specific value. Here's an example of how that might look when defining blur and opacity classes:

```css
.filter-blur {
    filter: blur(2px);
}

.filter-half-opacity {
    filter: opacity(50%);
}
```

However, both of these classes affect the same property! As [CSS specificity](https://www.smashingmagazine.com/2007/07/css-specificity-things-you-should-know/) works, the latest defined property in this example (the `.filter-half-opacity` class) would take precedence, and the blurring would be ignored.

Luckily, we can write a new class that will use both of these at once!

```css
.filter-blur-and-half-opacity {
    filter: blur(2px);
            opacity(50%);
}
```

While this works, it defeats the purpose of handy utility classes each providing just one function.

## **In come CSS variables!**

With newly introduced CSS variables, we can work around this by defining a single class that *always has all filters applied*.

To do this, first we will have to also declare some defaults to fall back to, in case our element doesn't have all filters explicitly declared already. These defaults can simply be set to have no discerning visual effect on the element.

```css
:root {
    --filter-blur: 0;
    --filter-brightness: 100%;
    --filter-contrast: 100%;
    --filter-grayscale: 0%;
    --filter-hue-rotate: 0deg;
    --filter-invert: 0%;
    --filter-opacity: 100%;
    --filter-saturate: 100%;
    --filter-sepia: 0%;
}

.filter {
    filter: blur(var(--filter-blur))
    brightness(var(--filter-brightness))
    contrast(var(--filter-contrast))
    grayscale(var(--filter-grayscale))
    hue-rotate(var(--filter-hue-rotate))
    invert(var(--filter-invert))
    opacity(var(--filter-opacity))
    saturate(var(--filter-saturate))
    sepia(var(--filter-sepia));
}
```

From here on, each of our utility classes merely needs to override one of these variables in the element it's placed on. For example, rewriting the previous blur and opacity helpers:

```css
.filter-blur {
     --filter-blur: blur(2px);
}

.filter-half-opacity {
     --filter-opacity: opacity(50%);
}
```

Now we can apply the `.filter` class to any element, then add any combination of our other filters as additional classes.

## **Examples in use**

As an example of this in action, here is a photo ([taken by Cody Board](https://unsplash.com/photos/jORYUUvgfpA)) with examples of different filters applied through utility classes.

![Examples of different filters being combined on the same image](/images/articles/filter-examples.png)

Examples of different filters being combined on the same image

## **Sass example**

In the CSS tab of the CodePen demo for this concept, you can find an example Sass script to generate a handful of these classes for you that were used in the examples above.

Take a look at the compiled CSS output to see what classes get generated.

<https://codepen.io/LiamH/pen/QVrBVx>

## **Wrapping up**

While this solution works pretty well, there are a couple of points to note about the approach...

- This is merely a proof-of-concept solution for a problem with CSS filters
- [Browser support](https://caniuse.com/css-variables) for CSS variables still isn't *quite* there to rely on
- This probably isn't the most performant solution, since all filters are always applied
- If you generate a ton of classes like this, you may wish to use [PurgeCSS](https://github.com/FullHuman/purgecss) to still get a relatively small CSS output
- You could remove the `.filter` class itself by having the filter property declared inside each individual utility class itself. This may make it a lot more easier to use as a developer, but would result in *huge* generated CSS.
