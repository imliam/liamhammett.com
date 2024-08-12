---
title: Supercharge GitHub.com with Browser Extensions
slug: supercharge-githubcom-with-browser-extensions
published_at: 2019-05-11
strapline: ""
synopsis: ""
tags:
    - GitHub
    - Browser Extensions
---

As a commercial software developer, my day-to-day work for the last several years has involved working on closed-source software on private GitLab and BitBucket repositories, but that doesn't mean GitHub has become a stranger to me.

I still spend a good portion of each week on the GitHub website, both for hosting my own personal repositories and looking into open source projects' code, issues and documentation.

GitHub's user experience is already pretty great and has only been getting better and better since Microsoft purchased it last year. That said, there are still a few things that are a bit lacklustre and could be improved — but that's where browser extensions come in!

Here are a few of my favourites that I find myself using all the time.

## Octotree - Code tree on steroids

![A GitHub repository with the Octotree sidebar open to the left](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/octotree.png)

Octotree might seem pretty basic at first - when you're browsing a repository, it just shows the repository's file tree in a sidebar. What's so special about that?

The magic is that it appears *everywhere* in the repository that is in the context of code. No matter where you are, you can quickly swap back and forth between different files. No need to wait for a full page reload and to lose your bearings when you're browsing to different directories. It's just intuitive, and hands down the fastest way to browse GitHub repositories in the browser.

It's also pretty smart and has special behaviour in a couple of different places. For example, if you're viewing a pull request, it'll only show the files changed in those commits - as well as the number of lines added and removed.

<https://github.com/ovity/octotree>

## SourceGraph - IDE-style "Go-to-definition"

![Source code in GitHub with a "Go to definition" and "Find references" button in a popover](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/sourcegraph.png)

If there's one thing that's great about browsing a code project in an IDE that understands the language, it's being able to quickly find where a symbol came from and where it's used.

SourceGraph does just that - just hovering over a symbol in any supported language will give you the option to "Go to definition" or "Find references", and it works great.

<https://github.com/sourcegraph/sourcegraph>

**Update (2019-05-15):** GitHub have recently introduced this directly into the website for some popular languages, with more coming soon!

## OctoLinker - Quickly open dependencies

![Hovering over a dependency name in a package.json file shows that it is a hyperlink](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/octolinker.png)

OctoLinker allows you to click on a require statement in a NodeJS project or dependency file like `package.json`, making it just that little bit easier to get around files, even if they're in a different repository entirely.

<https://github.com/OctoLinker/OctoLinker>

## Code Folding

![Hiding blocks of code on GitHub by folding them](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/code-folding.png)

GitHub Code Folding adds carets to let you selectively hide and show blocks of code. That's all there is to it.

<https://github.com/noam3127/github-code-folding>

## Issue Link Status

![Issues and pull requests in a GitHub comment showing their status](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/issue-link-status.png)

GitHub Issue Link Status finds any links to issues or pull requests within issues and pull requests, and shows the status of them by adding an icon and changing their colour.

This lets you easily see the status of the given issue or pull request without having to click through to another page.

<https://github.com/fregante/github-issue-link-status>

## Refined GitHub

![Some of Refined GitHub's features](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/refined-github.png)

Refined GitHub... well... it refines GitHub. It has hundreds of small tweaks and improvements that make every part of the platform more pleasant to use.

A handful of the tweaks it introduces are as follows:

- Fix merge conflicts in a single click
- See Gists inline in comments
- Automatically hides useless reaction comments like "+1" or "👍"
- See the commit or pull request that closed an issue
- Wait for checks to complete before merging a PR
- Show avatars of who reacted to a comment

Check out the repository to see the full list of features, there are *a lot* of them.

<https://github.com/refined-github/refined-github>

## ZenHub - Kanban for Issues

![Kanban board in a GitHub repository's tabs](https://res.cloudinary.com/liam/image/upload/v1560627150/liamhammett.com/zenhub.png)

ZenHub is an extension with a pretty good goal - to reduce the number of applications you need to use to manage your project.

It adds a new tab to GitHub repositories to view the issues in a kanban board, giving a great way to manage your issues, visualise where your bottlenecks might be, and managing sprints.

Note that GitHub does have a built-in "projects" board that can be used for kanban, but ZebHub goes into far more depth.

## CodeCopy - Quickly add snippets to your clipboard

!["Copy to clipboard" button on a code block](https://res.cloudinary.com/liam/image/upload/v1560627149/liamhammett.com/codecopy.png)

This extension adds a button to any snippet block on a handful of sites that will immediately copy the snippet to your clipboard - including sites like GitHub and StackOverflow.

<https://github.com/zenorocha/codecopy>

## Conclusion

All in all, these extensions add a ton of value to GitHub if you find yourself on it even a couple of times a day.

They mostly bring more interaction and information to the interface to save a few clicks here and there  —  but it all adds up super fast when you're browsing from one page to the next on the site a lot.

The ones I've listed here are only the main ones I find myself actively using all the time. There are a ton of other browser extensions available to enhance GitHub in many ways. If you want to get better notifications, a different homepage layout, or do a number of other things that could improve your productivity, check out this list:

<https://stefanbuck.com/awesome-browser-extensions-for-github>
