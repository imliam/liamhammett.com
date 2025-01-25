---
title: Introducing GitGhost - Sync Git Activity History To Another Repo
alternate_title:
slug: introducing-gitghost
published_at: 2025-01-25
updated_at:
strapline:
synopsis:
previous_article:
next_article:
tags:
    - Git
---

As developers, we often take pride in our GitHub activity history, even if it is just a vanity metric. It's like a badge of honour, a reflection of our coding journey to show off how much hard work we put into coding over time.

But what happens if your day job doesn't involve using GitHub, but instead something else like GitLab or Bitbucket? None of that activity gets reflected in your GitHub profile, and it looks like you've not touched a line of code in months.

That's where GitGhost comes in.

<https://github.com/imliam/gitghost>

## What is GitGhost?

GitGhost is a CLI tool that can sync _only the commit activity_ from one Git repository to another, without syncing the actual code. This allows you to keep your GitHub activity history up-to-date with your work activity, even if you're not using GitHub for your day-to-day work.

Take a look at how my GitHub activity graph looked before and after using GitGhost:

![My GitHub activity graph before using GitGhost](/images/articles/gitghost-before.png)

![My GitHub activity graph after syncing my work activity using GitGhost](/images/articles/gitghost-after.png)

## Why care about this?

You shouldn't. The activity graph on GitHub is just a vanity metric, and doesn't really make a difference. It's not a reflection of your coding skills or how good you are as a developer - it only shows how many commits you make.

There are [plenty of tools out there](https://github.com/Shpota/github-activity-generator) that can help you [fake your GitHub activity](https://github.com/artiebits/fake-git-history), but that's just lying. GitGhost is different - it syncs your actual commit history from other repos, so your activity graph becomes **a real reflection of the number of commits you make** across multiple services and projects and isn't just a facade.

## How do I use it?

As GitGhost is built with PHP, it can be installed with Composer (`composer global require imliam/gitghost`) or [cpx](https://cpx.dev) (`cpx imliam/gitghost <arguments>`)

Once installed, running the `gitghost setup` command will take you through a one-time process to set up your dummy repository and determine which authors to commit from and to.

After that, you can run the `gitghost sync` command to sync the commit history from one repository to another. For example, to sync the commit history from a local repository to the dummy GitHub repository, you can run:

```bash
gitghost sync /path/to/local/repository
```

This will recreate the git history by making commits dated into the past to the dummy repository and push them to the remote repository.

If this tool sounds like something you'd be interested in using, you can find it on GitHub:

<https://github.com/imliam/gitghost>
