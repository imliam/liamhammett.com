---
type: video
title: Toggling 2 Views with 1 Hotkey in VSCode
slug: toggling-2-views-with-1-hotkey-in-vscode
published_at: 2023-11-07
updated_at: 
strapline: 
synopsis: Learn how to switch between 2 different views in VSCode with a single hotkey.
tags:
    - VSCode
---

Want to switch between two different views in VSCode with a single hotkey? Well, that's where [a little studio engineering](https://www.youtube.com/watch?v=_lK4cX5xGiQ) comes in handy, my hard-rocking amigo!

<https://www.youtube.com/watch?v=UpBqWZBlaA4>

The GitLab Workflow extension: <https://marketplace.visualstudio.com/items?itemName=GitLab.gitlab-workflow>

The `keybindings.json` shown in the video:

```json
[
    {
        "key": "shift+cmd+g",
        "command": "workbench.view.scm",
        "when": "focusedView != 'workbench.scm'"
    },
    {
        "key": "shift+cmd+g",
        "command": "issuesAndMrs.focus",
        "when": "focusedView == 'workbench.scm'"
    }
]
```