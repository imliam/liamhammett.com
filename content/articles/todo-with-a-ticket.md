---
title: Todo With A Ticket
alternate_title:
slug: todo-with-a-ticket
published_at: 2025-05-30
updated_at:
strapline:
synopsis:
previous_article:
next_article:
tags:
    - Productivity
---

There's nothing worse than seeing a vague "todo" comment in a codebase only to realise it was committed 8 years ago and you've got no clue what it's referring to.

That's why when I leave a todo, I leave a ==todo with a ticket==.

How often have you seen something like this

```php
// TODO: Make this accept language param
$thisIs = some_really($esotericCode)->isnt('IT');
```

There's no guarantee anyone will ever modify this file again. There's no guarantee anyone will pay attention to the comments. There's no guarantee that, even if they do see it, they will know what the intent of this comment is.

Even if you write a great comment with detail, **context gets lost over time** and the comment will naturally become less useful the longer some time passes.

It's rare that a todo comment is simple enough for _just_ a comment anyway - if it was simple enough you'd probably have just made the change when you were editing the file anyway.

So write a ticket.

Put it in the backlog. Put it in your next sprint. Solve it later today if you can. So long as it goes _somewhere_, it is immediately more useful in so many ways:

**1. More visibility**

As a ticket, it shows on a board/list somewhere. It's not just lost in a file with low churn rate that no-one ever touches or will see. It's a _real piece of work_ and doesn't get missed.

If a todo comment is there, it's tech debt. That might be fine once or twice, but over the lifetime of a successful application this can result in hundreds of little bits of tech debt that start eating away at it.

It's no longer just tech debt that builds up over time, it's something you have to make a conscious decision to either do or discard. You'll see the ticket on your board, and you'll either do it or close it.

**2. You know _who_ reported it and _when_**

Git is great - and `git blame` helps a ton when working on a project over a long time, but it's not perfect. People move code around, run style fixes on files, refactor the surrounding code while not touching the "todo" comment itself. All things that break the usefulness of `git blame`.

In a ticket, though? You can see which user reported it, you see when it was raised, you see if someone else tried to take the task in the past, you have a place to talk about it.

**3. You can link to the code**

In the ticket, you can link to the line of code from the current commit ref. That way, if the files in Git change and the todo comment gets moved around, you can still see the original intent of the ticket - and then go and find how that code got updated to see where it actually went.

_____

If you still want to put a comment in the codebase to let people know something needs to be worked on, do that. Just add a mention of the ticket.

Either add the URL to the comment, or just something that's clearly a ticket, like `!13` for GitHub issues or `PROJ-13` if you use JIRA, so someone can look it up.

```php
// TODO: PROJ-13 - Make this accept language param
$thisIs = some_really($esotericCode)->isnt('IT');
```

Hopefully you can see where I'm coming from and the benefits. So if you really feel the need to leave a todo comment, leave a todo with a ticket.
