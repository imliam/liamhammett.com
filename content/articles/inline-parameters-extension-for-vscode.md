---
title: Inline Parameters Extension for VSCode
slug: inline-parameters-extension-for-vscode
published_at: 2020-06-06
strapline: ""
synopsis: ""
tags:
    - PHP
    - VSCode
    - JavaScript
    - TypeScript
    - Lua
---

Does the needle or haystack go first? What's the 12th argument for that function do again? Is this function variadic or does it need an array?

If you've ever found yourself asking any of these questions and happen to use VSCode, maybe my new [Inline Parameters extension](https://marketplace.visualstudio.com/items?itemName=liamhammett.inline-parameters) will help you out!

<https://marketplace.visualstudio.com/items?itemName=liamhammett.inline-parameters>

This functionality is something that was [popularised by JetBrains' IDEs](https://blog.jetbrains.com/phpstorm/2017/03/new-in-phpstorm-2017-1-parameter-hints/) when they implemented it, and for good reasons! It was one of the few things that was missing when I decided to move from PHPStorm to VSCode.

Eventually, I decided to implement it into VSCode myself by building an extension. I think what I ended up with works pretty well.

![Example of the extension in use](https://raw.githubusercontent.com/imliam/vscode-inline-parameters/master/example.gif)

## Take #1

Every now and then at [Future Plc](https://www.futureplc.com/), the technology & engineering team has a "hack day", where we get some time set aside to hack on whatever we want. Some people use it to learn new technologies, others to make experimental products or proof-of-concepts. Almost anything goes.

Back in a hack day we had in September 2019, I decided to make a VSCode extension that would display PHP parameter names inline in the code editor.

I'd never made a VSCode extension before, nor did I have extensive experience with TypeScript, so this was an interesting experience. Luckily, Microsoft has a repository published with lots of example extensions, which helped me to get up-and-running quickly, and gave me a taste of the kind of APIs I'd have to interact with.

<https://github.com/microsoft/vscode-extension-samples>

By the end of the day I had a working proof-of-concept ready to show! It worked, showing the right names in the right places in the code, and it was great!

<https://twitter.com/LiamHammett/status/1169665102997524480?ref_src=twsrc%5Etfw>

A huge part of this extension was parsing the abstract syntax tree (AST) of some source code to get various information about the file. To parse PHP's AST, I used the [Glayzzle parser](https://github.com/glayzzle/php-parser/).

<https://github.com/glayzzle/php-parser>

To figure out what the AST *actually* meant and represented, I found [AST Explorer](https://astexplorer.net/) to be a huge help when trying to visualise what a certain object in the AST was referring to.

<https://astexplorer.net/>

However, the way I hacked the extension together in a few hours was less than ideal. It worked by going through a few steps:

- Crawling the AST of the current file to find any functions being called
- Figuring out where the function is originally defined through PHP and Composer's standard autoloading behaviour
- Crawling the AST of the file it found the source in, extracting the parameter names from the function's source code
- Adding the parameter names as annotations in the editor

This seemed okay at first - while I was testing it during development I was mostly relying on only a couple of files and it worked flawlessly. However, as soon as I opened a real project, it was clear that this approach was far too slow; it was having to parse tens of files at a time, and if I wanted it to be able to update as the user typed, it would potentially be having to do this to a lot more files a lot quicker.

There was another flaw with this approach; we don't have the source code of every function available through autoloading. Core PHP functions and language constructs like `var_dump()` and `strpos()` are built into the language, so we couldn't use the same method to get their parameter names. One workaround for this could've been to use JetBrains' [PHPStorm Stubs](https://github.com/JetBrains/phpstorm-stubs), which redefines all of the core and extension functions in .php files.

It was clear another approach was needed...

## Take #2

I didn't really look back to this since the original hack day, so 9 months went by before I decided to come back and take another stab at it and get it done.

After scouring some of the VSCode source code and documentation, I eventually came across exactly what I was looking for all this time, `vscode.executeHoverProvider` - perfect!

> **vscode.executeHoverProvider** - Execute all hover providers.
>
> - **uri** - Uri of a text document
> - **position** - Position of a symbol
> - **(returns)** - A promise that resolves to an array of Hover instances.

With this command, you can give it a position in a file, and it'll return the same text that's displayed in a popup when you hover over that position with your cursor.

![VSCode function hover popup](/images/articles/vscode-hover.png.png)

This is perfect, as this includes all of the information we need! We get the parameter names that we can extract, plus some additional information like whether a parameter is variadic and can be repeated infinitely, which we can also use to improve the experience in the extension.

Another advantage is that this will work for any language that implements these hover providers (most languages), although the text may be formatted differently and so the logic to extract the parameter names may differ.

With this approach, I cleaned up the logic, resulting in it being a lot faster and more reliable than before:

- Crawling the AST of the current file to find any functions being called
- Executing the hover providers of any functions to extract parameter names
- Adding the parameter names as annotations in the editor

Success!

## Alternative Extensions

Before I started working on this, I'd already looked around for existing extensions that may have supported this functionality, after all - it's a popular feature in JetBrains's IDEs so would have made sense that someone would've ported it, but didn't find anything.

However, a friend brought to my attention Bobby Zrncev's "[IntelliJ Parameter Hints](https://github.com/bzrncev/intellij-parameter-hints)" extension which also aims to do exactly what I was, in almost exactly the same way, and also for PHP - it was too close to be true. I ended up reusing some of the logic from this extension (per the license) to further my own extension, fixing it up and handling edge cases as I went, but want to give full credit for any reused logic to Bobby.

I was also made aware of Benjamin Lannon's "[JS Annotations](https://github.com/lannonbr/vscode-js-annotations)" extension, which closely mirrors my first attempt by parsing the AST of every relevant definition. Unfortunately, this extension seems to no longer be maintained, so I decided to extract some of the AST parsing logic from it, fixing any problems I found with its parsing and making it use the hover providers, in order to bring JS and TS support alongside PHP in the same extension.

With the best of all these things brought together, I feel like my new package is a valient contender:

- It brings consistent support to multiple languages, making it easy to add new language support under this framework
- It properly handles numerous bugs and edge cases that I encountered when testing each of the aforementioned extensions
- There are a handful more options to customise the display and behaviour

## Conclusion

Even if no-one else decides to use it, it was a great experience for me to learn more about TypeScript and VSCode's internals, build an AST crawler, and end up with something that scratches my own itch.

It's also given me a taste of what's possible with some of the VSCode extension APIs, and I've now got a handful of other ideas that I intend to make extensions for in the future too.

If you want to grab the extension yourself, you can grab it from the extension marketplace:

<https://marketplace.visualstudio.com/items?itemName=liamhammett.inline-parameters>