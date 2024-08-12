---
title: Protecting Against Infinite Gmail Addresses Using Regex
slug: protecting-against-infinite-gmail-addresses-using-regex
published_at: 2020-09-16
strapline: ""
synopsis: ""
tags:
    - PHP
    - Regex
    - Gmail
---

You might be aware that one Gmail account could have an almost unlimited number of email addresses associated with it, to no effort of the user who owns the account. If you're not aware of this, check out my post below explaining it:

<https://liamhammett.com/make-infinite-gmail-addresses-for-one-inbox-nqoVprjX>

If you're thinking to yourself "how can I protect against users abusing this in my own applications?" - you're in luck, because that's exactly what we'll be covering here.

> ⚠️ Users can use these aliased email addresses for genuine purposes, as outlined in the aforementioned post.

This is a good trick to have in your toolbelt, but should seldom be the first approach to stop malicious users - there are other anti-spam measures to take first, like implementing honeypots and captchas.

Okay, so you still want to do this…

## Normalizing an address

There are two typical approaches to tackling a problem like this that are fairly easy and reliable.

The first approach might involve normalizing any email addresses and saving that.

> 💡 Always store and use the original email address the user entered.

If a user signed up with a denormalized email address, they expect to receive emails at that exact address, so we don't want to only save the normalized version.

Instead, you might want to store the normalized email address in your database in a separate column alongside the original one - so our `users` table may have an `email` and `normalized_email` column side-by-side.

This has a huge benefit in that it would allow us to normalize an incoming user's email address in our application before checking the database - then we can use an exact equality match to check for other users with the same address.

```sql
-- All users who have the same email inbox
SELECT * FROM users WHERE denormalized_email = "..."
```

This can have some huge performance gains over the other approach we'll be going over, if you need to do this check a lot. This could even be automatically generated as a virtual column if you use a database that supports them.

However, how would you check this if you couldn't also store the normalized email addresses, or had an existing set of email addresses you needed to check against?

## Writing a regex

The next easiest and most reliable way to check if two Gmail addresses really belong to the same inbox is with a regular expression.

For example, if we wanted to compare `example@gmail.com` against duplicates, we might start off with a regular expression that checks for a direct match for another email address of the same.

```
/^example\@gmail.com$/
```

We can then add a condition to check if there's a `@gmail.com` or `@googlemail.com` alternative of the same email.

```
/^example\@(gmail|googlemail).com$/
```

Next, we'll match against any plus + character in the username with anything after it.

```
/^example(\+.*)?\@(gmail|googlemail).com$/
```

Finally, we can check for any dot/period characters between any letters of the username.

```
/^e(\.?)+x(\.?)+a(\.?)+m(\.?)+p(\.?)+l(\.?)+e(\+.*)?\@(gmail|googlemail).com$/
```

Now our regular expression is complete, we can use this to assert against other email addresses and see if they belong to the same account. For example, using the `preg_match()` function in a PHP script.

```php
<?php

$regex = '/^e(\.?)+x(\.?)+a(\.?)+m(\.?)+p(\.?)+l(\.?)+e(\+.*)?\@(gmail|googlemail).com$/';

(bool) preg_match($regex, 'example@gmail.com'); // true
(bool) preg_match($regex, 'example@googlemail.com'); // true
(bool) preg_match($regex, 'ex.ample@gmail.com'); // true
(bool) preg_match($regex, 'example+test@gmail.com'); // true
(bool) preg_match($regex, 'ex.am.ple+test123@googlemail.com'); // true

(bool) preg_match($regex, 'best@gmail.com'); // false
(bool) preg_match($regex, 'example@foobar.com'); // false
(bool) preg_match($regex, 'example@booglemail.com'); // false
```

## PHP Package

To save you from having to write this logic yourself, I've put together a PHP package that can accept an email address as an input and generate the normalized email address, or this regular expression, automatically.

<https://github.com/imliam/php-unique-gmail-address>

You can install it via Composer and start making use of it right away.

```php
<?php

$validator = new UniqueGmailAddress('example@googlemail.com');

if (preg_match($validator->getRegex(), $user->email)) {
    // ...
}

// Or alternatively:

if ($validator->matches($user->email)) {
    // ...
}
```

The package also comes with a validation rule for the Laravel framework that will check the uniqueness of the Gmail address against the database.

```php
$request->validate([
    'email' => ['required', new UniqueGmailAddressRule('users', 'email')],
]);
```

It currently does this by using the `REGEXP` operator to match against each row in the database, which is an operation that MySQL supports. PRs for other database driver support is welcome.

Running a regular expression against a large table in the database can be a slow operation, but to protect an important and infrequent action such as a user signing up for a new account, can be worth the tradeoffs.

<https://github.com/imliam/php-unique-gmail-address>

## Going further

If you wanted, you could even go further than this to also protect against Gsuite email addresses on custom domains that can use the same techniques, by checking the if the MX records point to Google's mail servers.

```bash
❯ dig liamhammett.com mx

;; ANSWER SECTION:
liamhammett.com.        3599    IN      MX      5 alt1.aspmx.l.google.com.
liamhammett.com.        3599    IN      MX      5 alt2.aspmx.l.google.com.
liamhammett.com.        3599    IN      MX      10 alt3.aspmx.l.google.com.
liamhammett.com.        3599    IN      MX      10 alt4.aspmx.l.google.com.
liamhammett.com.        3599    IN      MX      1 aspmx.l.google.com.
```

That said, users will still always be able to create new email accounts in Gmail and other services easily enough, so how far you go to protect against this is up to you.

This approach won't stop every bad actor, but it can be a step to making it tougher for malicious users to continuously create new accounts on your service, and every little helps.
