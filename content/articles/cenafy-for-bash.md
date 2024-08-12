---
title: Cenafy For Bash
slug: cenafy-for-bash
published_at: 2018-05-10
strapline: ""
synopsis: ""
tags:
    - Terminal
---

![Photo of someone being Cenafied in a terminal](https://res.cloudinary.com/liam/image/upload/w_1024/v1/liamhammett.com/cenafy-photo.webp)

Based on the wonderful [Chrome extension of the same name](https://chrome.google.com/webstore/detail/cenafy/ndchmakhfaakbkhnkdgambadneloplnn) that lets you know who the champ is, this is a version for Bash that lets an unsuspecting victim who left their terminal open know about the champ via the magic of ASCII art (and even some killer singing, if they have the `say` command like on Mac OS).

You can install it as a command with `wget`:

```bash
wget https://raw.githubusercontent.com/ImLiam/cena.bash/master/cena.bash -O /usr/local/bin/cena
```

Or with curl:

```bash
curl -o /usr/local/bin/cena https://raw.githubusercontent.com/ImLiam/cena.bash/master/cena.bash
```

When using the command, you can pass it an argument that will determine the chance that John will pop his head up when the command is ran, this defaults to run every time. For example, for a 1 in 100 chance of the command actually doing something, you can run `cena 100`

Now, to make this show up unexpectedly, you can make this run at some unexpected event, like when the shell is first sourced, by adding it to the user's `~/.bashrc` file (or even their `~/.zshrc` or alternatives if that's what they're using).

```bash
echo 'cena 100' >> ~/.bashrc
```

That might be a bit too obvious though — most terminal-savvy users would expect such things to be there if they're aware someone's messing with them. How about hiding it in Ubuntu's message-of-the-day?

```bash
touch /etc/update-motd.d/98-secret-sauceecho 'cena 100' >> /etc/update-motd.d/98-secret-sauce
```

Of course, if you're concerned about this being found out too easily, you may also want to wipe their `~/.bash_history` file and take even further steps to hide where it's being loaded from.

![John Cena's beautiful face in ASCII art](https://res.cloudinary.com/liam/image/upload/v1560620414/liamhammett.com/cenafy-window.png)

You can find the GitHub repository below.

<https://github.com/imliam/cena.bash>