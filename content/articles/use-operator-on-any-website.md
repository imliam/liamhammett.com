---
title: Use Operator on Any Website
slug: use-operator-on-any-website
published_at: 2018-07-05
strapline: ""
synopsis: ""
tags:
    - CSS
    - Typography
---

[Operator](https://www.typography.com/fonts/operator/styles/) is a beautiful monospaced typeface that took the world by storm as it proved that coding typefaces can be both elegant and functional.

One way it helped stand out from the crowd is how certain italic glyphs take inspiration from script typefaces, making them distinct from the ordinary characters.

Improve it again by adding coding ligatures to it with [Operator Mono Lig](https://github.com/kiliman/operator-mono-lig) and you've got one swish font to code in!

![Part of a Vue.js component, shown in Operator Mono Lig, in VS Code](/images/articles/operator-in-vscode.png)

It's a bit pricier than most typical folk would think of paying for a typeface, at between $200  —$800 depending on the package, but well worth it if you spend all day every day looking at code.

Unfortunately, it's not something that you'll typically see when browsing websites, unless they have paid for and implemented the webfont solution themselves. So how can we get more use out of this typeface and see it in other places we typically look at code?

In come [User Styles](https://userstyles.org/), where browser extensions allow you to add custom CSS to modify the look of a website yourself.

Just change the `font-family` of all `<code>` blocks, right?

```css
code {
    font-family: "Operator Mono Lig" !important;
}
```

That works for the most part, but you still don't get to see the gorgeous italic characters it offers for most.

Luckily, there are a handful of common Javascript plugins such as [Prism](https://prismjs.com/), [Highlight.js](https://highlightjs.org/) and [CodeMirror](https://codemirror.net/) that websites use to add syntax highlighting to code blocks on them.

These libraries add predictable CSS classes to the code in order to colour them according to the syntax highlighting theme, so luckily we can make use of these to make certain parts of code italic where we want.

![The Laravel documentation with Operator Mono Lig](/images/articles/operator-on-laravel-com)

If you want to enable this yourself, you can install the user style or get the Gist to use to modify and see what it affects below.

<https://gist.github.com/imliam/e3854276a5e4fd83c38c35c398de5d46/raw/8d650f8eb9500b2aadbc11b2f3602ab04c6225b9/operator-mono-lig.css>

If Operator isn't quite your style but you want a similar typeface, consider trying [Dank Mono](https://dank.sh/) instead.

