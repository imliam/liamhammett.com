---
title: "CSS box-decoration-break: clone - Styling Inline Elements That Line Break"
slug: css-box-decoration-break-clone
published_at: 2024-08-21
updated_at: 
strapline: 
synopsis: 
tags:
    - CSS
---

Today I learned about the CSS property `box-decoration-break: clone`.

I found myself wanting to style some inline text on a page to highlight it with a background for emphasis, but I noticed that when the text wraps onto a new line, the styles for the inline element just kind of... cut off abruptly.

Take a look for yourself with this example, see how the beginning and ends of lines don't follow the same padding and border-radius as the beginning and end of the full highlighted text:

<div class="border-2 border-gray-300 border-dashed rounded-2xl px-4 -mx-4 sm:px-6 sm:-mx-6 py-4">
Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua <span class="px-2 py-1 bg-orange-500 text-orange-50 from-orange-400 to-orange-600 bg-gradient-to-br rounded-tr-md rounded-bl-md rounded-tl-xl rounded-br-xl">laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptatevelit esse cillum dolore eu fugiat</span> nulla pariatur.
</div>

There's no great way to apply separate styles to the line break spots, but I just found out about the `box-decoration-break` property, which you can apply to an inline element with the `clone` value, ensuring the complete styles are cloned for each instance of text regardless of line breaks, making the text look a bit more consistent:

Now take a look with `box-decoration-break: clone` applied:

<div class="border-2 border-gray-300 border-dashed rounded-2xl px-4 -mx-4 sm:px-6 sm:-mx-6 py-4">
Lorem ipsum dolor sit amet consectetur adipisicing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua <span class="px-2 py-1 bg-orange-500 text-orange-50 from-orange-400 to-orange-600 bg-gradient-to-br rounded-tr-md rounded-bl-md rounded-tl-xl rounded-br-xl box-decoration-clone">laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptatevelit esse cillum dolore eu fugiat</span> nulla pariatur.
</div>

`box-decoration-break: clone` has [pretty great browser support](https://developer.mozilla.org/en-US/docs/Web/CSS/box-decoration-break) (with prefixes), and there are [more examples on MDN](https://developer.mozilla.org/en-US/docs/Web/CSS/box-decoration-break) to see how it's useful. [TailwindCSS also has a utility class](https://tailwindcss.com/docs/box-decoration-break) for this, `.box-decoration-clone`.