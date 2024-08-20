---
title: The First Useful Test is Empty
slug: the-first-useful-test-is-empty
published_at: 
updated_at: 
strapline: 
synopsis: 
tags:
    - PHP
    - Testing
---

Getting started with anything is hard, but writing the first test for a new project? Daunting.

When do you run your first test on a new project? When do you write your first test? When do you set it up to run automatically in CI?

It's tempting to wait until you've got something to test before you tackle any of this, ==the TDD mindset doesn't vibe with everyone==, but there are some wins to be had from running tests early - even just as a vague way to prove parts of your application are working.

- **Running PHPUnit with 0 tests** is still executing Composer's autoloader. This means it's ensuring your dependencies get loaded properly, and that any autoloaded files (such as those using the [autoload.files](https://getcomposer.org/doc/04-schema.md#files) field) work.
- **Running PHPUnit with an empty feature test** actually invokes your code. The [base TestCase Laravel comes with](https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Testing/TestCase.php) uses `setUp` and `tearDown` methods to build an environment for your tests to run in. This means it executes your service providers, loads config, and a whole lot more without you realising it. Even if your test does nothing!
- **Running PHPUnit calling a function, but not asserting anything** is an odd thing to do as it might appear like you're not proving anything by making no assertions, but you're still testing that the code runs without error. By calling a function you're seeing the code execute and ensuring there's no fatal errors or exceptions in whatever path it went through. This is a minimal way to ensure your code is at least _runnable_.

I don't advocate for this as a replacement for writing tests, but when it comes time to take that first step and write your first _real_ test, well the first step is easier to take when it's not the first.
