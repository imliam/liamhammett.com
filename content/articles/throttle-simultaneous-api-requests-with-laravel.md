---
title: Throttle Simultaneous API Requests with Laravel
slug: throttle-simultaneous-api-requests-with-laravel
published_at: 2018-06-13
strapline: ""
synopsis: ""
tags:
    - PHP
    - Laravel
---

Laravel comes with a handy [`ThrottleRequests` middleware](https://laravel.com/docs/5.6/routing#rate-limiting) out-of-the-box that blocks users of an API from being able to send more than a particular amount of requests within a defined amount of time.

This is extremely useful for preventing an API from being abused by spammed requests, but isn't suitable for every use case.

What about an API call that takes a lot of limited processing power, or performs an action that simply can't be running twice at the same time?

I put together a middleware package to handle just this use case, allowing the ability to block users from sending more than a given number of requests while the previous ones are still running.

<https://github.com/imliam/laravel-throttle-simultaneous-requests>

Once installed, you can use the middleware like any other. For example, to limit a particular endpoint to only 3 concurrent requests by the same user:

```php
Route::get('/', 'HomeController@index') ->middleware('simultaneous:3');
```

## Why not use queues?

Queues have their place to defer time consuming tasks to a later date, however they are not always the most appropriate solution for a task. A given task could require use of limited hardware resources, or require some other kind of processing that does not make sense to run concurrently.

For a good example of this kind of functionality in production, you can [read about how Stripe uses this method to protect their API.](https://stripe.com/blog/rate-limiters)