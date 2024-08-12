---
title: Laravel TALL Stack Preset
slug: laravel-tall-stack-preset
published_at: 2020-05-08
strapline: ""
synopsis: ""
tags:
    - PHP
    - Laravel
    - TailwindCSS
    - AlpineJS
    - Livewire
---

Do you enjoy using TailwindCSS, AlpineJS, Laravel and Livewire together?

Well you're in luck! Along with Dan Harrin and Ryan Chandler, we've just released a preset that can provide all of this out-of-the-box with sensible defaults to get you up and running with these technologies in a flash.

<https://github.com/laravel-frontend-presets/tall>

The preset will install everything you need to get going with this stack:

- [TailwindCSS](https://tailwindcss.com/)
  - [Tailwind Custom Forms Plugin](https://tailwindcss-custom-forms.netlify.app/)
 - [Tailwind UI Plugin](https://tailwindui.com/components)
- [AlpineJS](https://github.com/alpinejs/alpine)
- [Laravel](http://laravel.com/)
- [Livewire](https://laravel-livewire.com/)

It's quick and easy to install, and if you want your new app to have auth components it's as simple as adding the `--auth` flag to the command.

```bash
composer require livewire/livewire laravel-frontend-presets/tall
php artisan ui tall --auth
npm install
npm run dev
```

The auth pages and components come with a beautiful template from Tailwind UI that Adam Wathan & Steve Schoger agreed we could provide in this preset's boilerplate.

![TALL preset sign-in page](https://res.cloudinary.com/liam/image/upload/v1588944798/liamhammett.com/tall-login.png)

We've also put some effort into writing tests that covers all the auth logic - you don't even get that with Laravel's own auth presets!

![Tests running in GitHub Actions](https://res.cloudinary.com/liam/image/upload/v1588944791/liamhammett.com/tall-ci.png)

There's a bunch of other awesome things about the preset too:

- Auth templates and controllers have been rewritten as Livewire components
- The couple of things that don't fit into Livewire components are now single action controllers
- Pagination views use templates from Tailwind UI, including Laravel's default and simple pagination, and Livewire's pagination
- We've provided a renderless `base` layout to provide functionality without getting in the way of your application's own UI
- Tailwind 1.4's built-in PurgeCSS config is configured to support Laravel's directory structure
- All of the preset's code is published to your application. You have full control over all of the routes, components, controllers, views and tests to change them as you want. This also means you can completely remove the preset package when you're done and everything will still work!
- Some [weird bugs](https://github.com/laravel-frontend-presets/tall/issues/7) you'll run into with these technologies have already been handled as a joint effort

We've also kept the stubs super simple - they follow the same directory structure as a regular Laravel app, which means it's easy to browse and anyone can help contribute to it.

It also has the added benefit of being able to just copy and paste files manually into an existing project if you need, there's no magic going on behind the scenes to format the stub files.

<https://twitter.com/LiamHammett/status/1258511169121210369>

If you're interested in using the TALL stack at all, go check out the GitHub page for the preset to get started!

<https://github.com/laravel-frontend-presets/tall>
