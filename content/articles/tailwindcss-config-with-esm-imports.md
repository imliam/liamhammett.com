---
title: TailwindCSS Config with ESM Imports
slug: tailwindcss-config-with-esm-imports
published_at: 
updated_at: 
strapline: 
synopsis: How to change TailwindCSS' configuration file to use ECMAScript modules (ESM) imports.
tags:
    - TailwindCSS
    - JavaScript
---

TailwindCSS' default configuration file uses CommonJS modules, but you can switch to ESM easily.

The default TailwindCSS config file uses CommonJS' `require()` and `module.exports = ...`{.whitespace-nowrap} statements, while a lot of applications and build tools are moving towards using ECMAScript modules (ESM) with `import` and `export` statements.

If your frontend build tooling uses ESM, you might want to convert TailwindCSS' configuration file to use ESM imports so you can use it seamlessly with your files. Here's how you can convert TailwindCSS' configuration file to use ESM imports.

<x-alert type="info">Remember that not everyone who uses TailwindCSS is familiar with the JavaScript ecosystem, so while this might be obvious to anyone who works with NodeJS, folks working primarily in other backends might not.</x-alert>

To start, here's what a typical default `tailwind.config.js` file will look like.

```js
const plugin = require('tailwindcss/plugin')

module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    plugins: [
        require("@tailwindcss/typography"),
        require("@tailwindcss/forms"),
    ],
};
```

1. Replace all `require()` statements with `import` statements.

You can use the `default` export from the plugins and give it a sensible name to use later down in the file.

```js
+ import { default as typographyPlugin } from "@tailwindcss/typography";
+ import { default as formsPlugin } from "@tailwindcss/forms";

module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    plugins: [
-         require("@tailwindcss/typography"),
-         require("@tailwindcss/forms"),
+         typographyPlugin,
+         formsPlugin,
    ],
};
```

1. Replace the `module.exports` statement with an `export default` statement.

```js
import { default as typographyPlugin } from "@tailwindcss/typography";
import { default as formsPlugin } from "@tailwindcss/forms";

- module.exports = {
+ export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    plugins: [
         typographyPlugin,
         formsPlugin,
    ],
};
```

3. That's it! You've now converted your TailwindCSS configuration file to use ESM imports.

Now you can import and resolve the TailwindCSS configuration in your JavaScript files, like if you wanted to build some logic around the screen sizes:

```js
import resolveConfig from "tailwindcss/resolveConfig";
import * as tailwindConfig from "/path/to/tailwind.config.js";
const screenSizes = resolveConfig(tailwindConfig).theme.screens;

console.log(screenSizes);
// { sm: '640px', md: '768px', lg: '1024px', xl: '1280px' }
```