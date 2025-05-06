---
title: How to Create Full Width Containers in CSS
alternate_title: 
slug: how-to-create-full-width-containers-in-css
published_at: 2025-05-06
updated_at: 
strapline: 
synopsis: 
previous_article: 
next_article: 
tags:
    - CSS
    - TailwindCSS
---

What do you do when you have a container element with a fixed width, but want something inside that to stretch to the full width of the screen?

<https://www.youtube.com/watch?v=Y5ZDrw6uiEs>

<x-alert type="info"><a href="#final-styles" class="!text-white font-bold">Skip to the end</a> to get the final styles</x-alert>

This is such a common pattern and you'll see it all over the web, for example as a design choice in my [Think of an Elephpant](https://liamhammett.com/think-of-an-elephpant) article to make one section stand out, or in this example below:

<div class="px-8 py-16 my-16 text-white bg-gray-950 mx-full full-width-quote">
<div class="container max-w-[65ch] mx-auto">
Notice how the background here is a different colour, but that background colour spans the entire width of the screen?

If you inspect the page, you'll notice that this element is actually _inside_ the main container that's setting the width of the text!
</div>
</div>

Let's say we have some markup that looks like the following, and we want to make element "Three" span the entire width of the screen:

```html
<div class="max-w-3xl mx-auto">
    <p>One</p>
    <p>Two</p>
    <p>Three</p> <!-- Let's make this full-width -->
    <p>Four</p>
</div>
```

How do we do that?

## The New Container Approach

In most cases, the simplest approach would be to just end the wrapping container before the break-out element, and start a new one after it. That gives you complete control over the margins of the element.

```html
<div class="max-w-3xl mx-auto">
    <p>One</p>
    <p>Two</p>
</div>

<p>Three</p>

<div class="max-w-3xl mx-auto">
    <p>Four</p>
</div>
```

It's simple and it keeps the intent of classes being applied to the markup very clear, but it does have some drawbacks:

- You might not have control of the surrounding markup, for example in content that lives in a CMS
- You have to repeat the markup to create the container, which can be a hassle if you have a lot of wrapping elements not covered by common components
- It can force you to break up semantic elements on the page that you otherwise wouldn't

## The Negative Margin Approach

If you want to keep the surrounding markup unchanged, you can use negative margins to pull the element out of the container.

This is a common approach, but it can lead to some unexpected results if you're not careful.

Now let's just wrap the element we want to break out of the container in a new wrapper. For now we'll just apply a different colour to it so we can see the space it's taking.

```html
<div class="max-w-3xl mx-auto">
    <p>One</p>
    <p>Two</p>

    <!-- Our new wrapping element for the full-width -->
    <div class="bg-orange-500 text-white">
        <p>Three</p>
    </div>

    <p>Four</p>
</div>
```

See how that looks rendered on the page. As expected, it isn't full-width yet, but it is taking up the full width of the container:

<div class="bg-orange-500 text-white">
    <p>Three</p>
</div>

Now to set the element to take up the full size of the viewport, we can apply a width of `100vw` (100% of the viewport width, or the `w-screen` in TailwindCSS).

```html
<div class="bg-orange-500 text-white w-screen">
    <p>Three</p>
</div>
```

<div class="bg-orange-500 text-white w-screen">
Notice how this is now full-width...

...but the left side still starts in the container...

...and the right side goes off the screen and creates a horizontal scroll on the page?
</div>

To pull the left side out of the container, we can apply a negative margin of 50vw (50% of the viewport width) which will pull it too far to the left.

```html
<div class="bg-orange-500 text-white w-screen -ml-[50vw]">
    Three
</div>
```

<div class="bg-orange-500 text-white w-screen -ml-[50vw]">
That's a little off-screen...
</div>

But we can now fix this by making the element's position relative, alalowing us to set its `left` value to 50%, bringing it right back to the start of the viewport.

```html
<div class="
    bg-orange-500 text-white
    w-screen relative left-[50%] -ml-[50vw]
">
    Three
</div>
```

<div class="px-2 bg-orange-500 text-white w-screen relative left-[50%] -ml-[50vw]">
Hooray! A full-width element!
</div>

Now this works just like we wanted! The left side will be at the far left of the viewport and its width will be 100% of the viewport, so we can assume the right side will be at the far right of the viewport.

If we want, we could get rid of the 100vw width and just set the right margin to have a similar effect:

```html
<div class="
    bg-orange-500 text-white
    relative left-[50%] -ml-[50vw] right-[50%] -mr-[50vw]
">
    Three
</div>
```

<div class="px-2 bg-orange-500 text-white relative left-[50%] -ml-[50vw] right-[50%] -mr-[50vw]">
This is also a full-width element
</div>

Notice that there is a difference in this approach, which you can see on the examples above on this page:

- With the width set to 100vw, the element will always be the full width of the viewport, even if the viewport is smaller than the container (eg. there is horizontal scrolling)
- With no width set, but the right margin set, the element will be as wide as it needs to be, even if there is horizontal scrolling

## The Scrollbar Problem

Unfortunately, there's still one issue with this approach. The viewport-width value from `100vw` includes the vertical scrollbar of the page. In my case, this means that the element is actually 100% of the visible space of the document PLUS 12px for the scrollbar. This is not ideal, because it makes the element wider than our container, causing a horizontal scrollbar to appear for those extra 12px.

To fix this, we can use the `calc()` function to subtract the width of the scrollbar from the viewport width.

Where we're setting the margins, we can also use the `calc()` function to set the margins to half of the viewport width minus half of the scrollbar width.

```html
- <div class="... w-screen -ml-[50vw] -mr-[50vw]">
+ <div class="... w-[calc(100vw-12px)] ml-[calc(-50vw+6px)] mr-[calc(-50vw+6px)]">
    Three
</div>
```

This now solves the scrollbar problem, but we can't just hardcode the values of `12px` and `6px` because the scrollbar width can vary between browsers and operating systems. Even on our one device, some pages may have a scrollbar and others may not, changing the behaviour of the viewport width - so we need to calculate the scrollbar width dynamically.

This is tricky to do in CSS alone, but we can use a little bit of JavaScript to calculate the scrollbar width and set it as a CSS variable.

## Calculting Scrollbar Width

JavaScript gives us a way to calculate the scrollbar width by comparing the `window.innerWidth` (the width of the viewport, including the scrollbar) with the `document.body.clientWidth` (the width of the body element, without the scrollbar). The difference between these two values is the width of the scrollbar.

We can calculate those, and set them into a CSS property on the page. We'll make sure to do this when the page loads and when the browser resizes, so the property will always be up to date.

```js
const setScrollbarWidthProperty = () => {
    document.documentElement.style.setProperty(
        '--scrollbar-width',
        (window.innerWidth - document.body.clientWidth) + 'px',
    )
}

window.addEventListener('load', setScrollbarWidthProperty);
window.addEventListener('resize', setScrollbarWidthProperty);
```

Now that we've got that available in CSS, we can use the variable to set the width of the element, and use `calc()` to find half of the scrollbar width to set the margins.

```html
<div class="
    ...
-     w-[calc(100vw-12px)]
-     ml-[calc(-50vw+6px)]
-     mr-[calc(-50vw+6px)]
+     w-[calc(100vw-var(--scrollbar-width))]
+     ml-[calc(-50vw+calc(var(--scrollbar-width)/2))]
+     mr-[calc(-50vw+calc(var(--scrollbar-width)/2))]
">
    Three
</div>
```

If using TailwindCSS, the markup might be getting a little verbose, but you can always abstract this into a component.

## Final Styles

If you want a recap of the final styles, ready to copy + paste into your own project, you can find them below:

For TailwindCSS, find the styles below. You might even consider using Matt Anderson's [tailwind-container-break-out](https://github.com/LucidNinja/tailwind-container-break-out) plugin to get some prebuilt utility classes for this.

```html
<div class="
    relative
    left-[50%]
    right-[50%]
    w-[calc(100vw-var(--scrollbar-width))]
    ml-[calc(-50vw+calc(var(--scrollbar-width)/2))]
    mr-[calc(-50vw+calc(var(--scrollbar-width)/2))]
">...</div>
```

For plain old vanilla CSS, consider using this utility class in your project:

```css
.full-width {
    position: relative;
    left: 50%;
    right: 50%;
    width: calc(100vw - var(--scrollbar-width));
    margin-left: calc(-50vw + (var(--scrollbar-width) / 2));
    margin-right: calc(-50vw + (var(--scrollbar-width) / 2));
}
```

## Conclusion

This negative margin, viewport-width approach might have a little bit of a learning curve, but it can be a really powerful way to create full-width elements in your designs.

It allows you to keep the surrounding markup intact, while still giving you the flexibility to create full-width elements that span the entire width of the screen.

CSS truly can do anything.
