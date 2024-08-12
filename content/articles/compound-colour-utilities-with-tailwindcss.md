---
title: Compound Colour Utilities with TailwindCSS
slug: compound-colour-utilities-with-tailwindcss
published_at: 2020-09-02
strapline: ""
synopsis: ""
tags:
    - CSS
    - TailwindCSS
---

When creating UIs with TailwindCSS, I often find myself writing the same few utilities together on the same elements all the time. This is why I often end up composing some common components, like a `.button` and `.form-input` classes.

In my mind, however, a "component" is a fully-formed set of styles for an element - something like a button may affect the colours, padding, border radius, text size and more - styles that are not necessary to be coupled together to still have a usable element.

But what do you do if you want to modify something that's tightly coupled together, like the colours? If your default element has black text and you set a dark background utility, you probably don't want it to keep having black text - coupling another text-colour utility to it.

For example, imagine you have an "alert" element that you'll use to display a message to the user. The alert itself may set some base styling, but what do you do when you want to denote that it's a success message by making it green, or an error by making it red?

```html
<div class="alert bg-green-500 text-green-100 border-green-600">Success! Good job, you did it.</div>

<div class="alert bg-red-500 text-red-100 border-red-600">Whoops! Something went wrong.</div>
```

Simple enough.

But what about when you want to re-use these colour combinations all over the application? Sure there's alerts, but what about badges, buttons, cards, form elements, banners, popovers, and other elements that will often have the same colour combinations available? What happens if you use `bg-green-500` in one place but mistakenly use `bg-green-600` in another and start getting out of sync? Wouldn't it be easier to have a single class that handles all of these colour classes consistently?

In come compound utilities.

## Compound Utilities

A "compound utility" is what I like to call a class that affects more than one CSS property at a time, but still isn't a component in itself.

An example you're probably familiar with in Tailwind is the `.mx-*` utility. It affects both the `margin-left` and `margin-right` CSS properties at the same time, but not `margin-top` and `margin-bottom` which it would've done if it used the shorthand `margin: top right bottom left` syntax.

The advantage of this approach is that:

- As it's a utility, it will have precedence in the cascade over any component classes
- As a compound utility should be declared before single-property utilities, those single-property utilities can still override properties of the compound one.

Implementing these kind of utilities yourself in a Tailwind plugin might be a little bit confusing, though. You want the compound utilities to come *after* components in the CSS file (so compound utilities would have higher specificity in the cascade), but *before* the base utilities (so single-property utilities could still override the compound ones).

At the time of writing, Tailwind will always append any utilities or components from an plugin onto the *end* of its sort list, so to achieve this we will have to treat compound utilities as a component by using the `addComponents` function, making sure to put it at the end of our `plugins` list so the classes it generates go at the end of any other plugin's components.

## Compound Colour Utilities

I've found myself often reusing the same few colour classes together when using Tailwind, so I created a plugin that will automatically generate some compound utilities for me so that I can consistently use the same colour combinations in several places in the application with ease.

The plugin generates the following 3 classes for each colour:

- `.green` - will set the `color`, `background-color`, `border-color` and `outline-color` properties to values that work together, with readable text
- `.button-green` - will use the same base colours as `.green`, but also handle hover and focus states.
- `.input-green` - uses a slightly different colour scheme as the others, as I find inputs often look better with a lighter background (on light themes, at least). Also handles the various states, such as focus colours and making it neutral if the element is disabled.

You can find the code for the plugin below - copy it into your Tailwind config's `plugins` array to install it.

Note that this plugin's setup relies on the default Tailwind config's approach of the colour configuration having a list of shades from 100-900 for any given colour. If these shades are not available for any given colour, it won't create a utility for it.

```js
module.exports = function({ e, addComponents, config }) {
    const newCompoundUtilities = {};
    const colors = config("theme.colors", {});
    const expectedKeys = ["100", "200", "300", "400", "500", "600", "700", "800", "900"];

    for (const colorName in colors) {
        const color = colors[colorName];

        if (typeof color !== "object") {
            continue;
        }

        if (!expectedKeys.every(key => Object.keys(color).includes(key))) {
            continue;
        }

        newCompoundUtilities[`.${e(`${colorName}`)}`] = {
            color: color["100"],
            backgroundColor: color["500"],
            borderColor: color["600"],
            outlineColor: color["600"]
        };

        newCompoundUtilities[`.${e(`${colorName}`)}::selection`] = {
            color: color["800"],
            backgroundColor: color["200"]
        };

        newCompoundUtilities[`.button-${e(`${colorName}`)}`] = {
            color: color["100"],
            backgroundColor: color["500"],
            borderColor: color["600"],
            outlineColor: color["600"]
        };

        newCompoundUtilities[`.button-${e(`${colorName}`)}:disabled`] = {
            color: color["200"],
            backgroundColor: color["400"]
        };

        newCompoundUtilities[
            `.button-${e(`${colorName}`)}:hover, .button-${e(`${colorName}`)}:focus`] = {
            color: "white",
            backgroundColor: color["400"],
            borderColor: color["500"],
            outlineColor: color["500"]
        };

        newCompoundUtilities[`.button-${e(`${colorName}`)}.-outline`] = {
            color: color["700"],
            backgroundColor: "transparent",
            borderColor: color["600"],
            outlineColor: color["600"]
        };

        newCompoundUtilities[`.button-${e(`${colorName}`)}.-outline:hover, .button-${e(`${colorName}`)}.-outline:focus`] = {
            color: "white",
            backgroundColor: color["500"],
            borderColor: color["600"],
            outlineColor: color["600"]
        };

        newCompoundUtilities[`.input-${e(`${colorName}`)}`] = {
            color: color["700"],
            backgroundColor: color["100"],
            borderColor: color["300"],
            outlineColor: color["300"],
            caretColor: color["700"]
        };

        newCompoundUtilities[`.input-${e(`${colorName}`)}:focus`] = {
            backgroundColor: "#fff",
            borderColor: color["300"],
            outlineColor: color["300"]
        };

        newCompoundUtilities[`.input-${e(`${colorName}`)}:placeholder`] = {
            color: color["500"]
        };

        newCompoundUtilities[`.input-${e(`${colorName}`)}:disabled`] = {
            color: color["400"],
            backgroundColor: color["200"]
        };

        newCompoundUtilities[`.input-${e(`${colorName}`)}::selection`] = {
            color: color["100"],
            backgroundColor: color["600"]
        };
    }

    addComponents(newCompoundUtilities, ["responsive"]);
};
```

Hopefully you'll find some use in this approach yourself. If you do, don't hesitate to let me know what other kind of compound utilities you end up making in your own designs!