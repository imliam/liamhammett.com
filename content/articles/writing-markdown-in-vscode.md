---
title: Writing Markdown in VSCode
slug: writing-markdown-in-vscode
published_at: 2024-08-19
updated_at: 
strapline: 
synopsis: Some tips to make writing Markdown prose in VSCode a little bit nicer
tags:
    - VSCode
    - Markdown
---

As a developer, I find my IDE to be like my home. I have it open all the time, I know where everything is, and I'm comfortable there.

But an IDE is for writing code, not text, right? That's where the lines get blurred, as formats like Markdown have become mainstream not just for READMEs and documentation, but for writing articles, blog posts, and even books. It's such a ubiquitous format that it's hard to avoid.

So, I write Markdown in VSCode. ==A lot of it.==

- My blog posts are in Markdown in a repo - no fancy database or CMS
- I write documentation for my day job in Markdown
- My default "new window" in VSCode is Markdown format, for taking notes throughout the day in scratch files
- I'm even writing my book, [l10n.expert](https://l10n.expert), in Markdown
So, here are some things I do to make writing Markdown in VSCode a little bit nicer. It's all pretty subjective, so take it with a grain of salt.

## Language-specific settings for Markdown

In VSCode, there are a bunch of settings you can change on a per-language basis, basically any of the "editor" settings.

This is pretty awesome if you think about it - writing prose is quite different than writing code.

I like to give myself:
- A larger text size, because often I'm focussed on writing a paragraph rather than grokking all the code in a function
- A different font that's better for reading (rather than monospaced with legible symbols, like most coding fonts)
- Turn off line numbers (when did you ever need that in a markdown file?)
- Turn off line highlighting (the cursor is enough for me to know where I am when I'm reading/writing prose)
- Enable word wrap (the ideal reading length is somewhere between 60-70 characters, but I keep it a bit longer as markdown tends to get littered with additional characters for formatting)

The result of this is a nice, clean, distraction-free writing environment that's easy on the eyes. Even better than that, it's something you can put side-by-side with your code without affecting the coding experience at all.

![A screenshot of my real VSCode window showing code and markdown side-by-side](/images/articles/vscode-markdown-code-side-by-side.png)

```json
// settings.json
{
    "[markdown]": {
        "editor.fontSize": 18,
        "editor.fontFamily": "'iA Writer Duo S', Helvetica, sans-serif",
        "editor.lineNumbers": "off",
        "editor.renderLineHighlight": "none",
        "editor.wordWrap": "bounded",
        "editor.wordWrapColumn": 80,
        "editor.quickSuggestions": {
            "comments": "on",
            "strings": "on",
            "other": "on"
        },
    }
}
```

## Markdown as the default language

I take notes in my editor all the time. <kbd>CMD+N</kbd> and bam, a blank window for me to jot down whatever I need to remember.

I could've stuck with the default "plaintext" language, but I like the syntax highlighting that Markdown gives me, as well as some of the language features from extensions - like being able to check off tasks in a task list.

```json
// settings.json
{
    "files.defaultLanguage": "markdown"
}
```

## Disable Emmet in Markdown

Emmet is a wonderful tool and I use it all the time when writing HTML. But it's a bit too aggressive for me when writing Markdown. I rarely want to write HTML in Markdown, but Emmet's tab completion is a bit too eager to help me out.

```json
// settings.json
{
    "emmet.excludeLanguages": [
        "markdown"
    ]
}
```

## Markdown All-in-One Extension

This extension does a lot of things that are handy out of the box, but there are a couple of minor visual things it provides that I like:

1. VSCode uses `*` by default for italics in Markdown. I prefer `_` as it's easier to see that's an underline in plain text - the extension allows that
2. The "render code span" setting adds a nice touch - it adds a visual decoration around inline code blocks

```json
// settings.json
{
    "markdown.extension.italic.indicator": "_",
    "markdown.extension.theming.decoration.renderCodeSpan": true
}
```

## Enable GitHub Copilot (or another AI assistant)

GitHub Copilot is by no means the best writing assistant, but it's got great integration with VSCode and knows a lot about code, which makes it ace for technical writing. `code`

I mostly use it just to bypass the writer's block on a sentence, or to get a quick idea of how to phrase something. Anything to keep the momentum going is good.

```json
// settings.json
{
    "github.copilot.enable": {
        "markdown": true
    }
}
```

## Conclusion

There are a lot of other extensions out there to improve Markdown. I use a handful, but they have less of an impact on my writing experience as I don't use them frequently - but I'm glad they're there when I need them.

- [Markdown Todo](https://marketplace.visualstudio.com/items?itemName=fabiospampinato.vscode-markdown-todo) - for task lists
- [Markdown Table](https://marketplace.visualstudio.com/items?itemName=TakumiI.markdowntable) - for creating tables
- [Markdown Paste](https://marketplace.visualstudio.com/items?itemName=telesoho.vscode-markdown-paste-image) - For the rare occasion I want to paste something else into a document and have it formatted for me

With all of this in place, the editor is a great reading experience and I can focus on writing. If you have any other tips or tweaks, I'd love to hear them!
