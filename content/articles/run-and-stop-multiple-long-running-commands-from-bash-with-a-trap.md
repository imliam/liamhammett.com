---
title: Run and stop multiple long-running commands from Bash with a trap
slug: run-and-stop-multiple-long-running-commands-from-bash-with-a-trap
published_at: 2019-07-13
strapline: ""
synopsis: ""
tags:
    - Bash
    - Terminal
    - Laravel
---

Sometimes when working on a project, I'll always want to run a handful of commands at the same time, some of which may return when they're done, others might be long-running, like watchers or services actively exposing ports.

This is something that might seem simple to do with a basic Bash script at first, but what if your script has multiple processes running side-by-side and you want to be able to stop them all at once too?

Here we're going to take a look at how we can achieve this with Bash traps and the single-ampersand operator.

## Example usage

A typical example of what I might do when working on a [Laravel](https://laravel.com/) PHP project is:

- Make sure MySQL is started for the application's database
- Make sure Redis is running for the caching layer
- Run a local web server for the PHP application to get served from
- Start up a Javascript watcher to recompile assets automatically

That's quite a few things going on to get a simple application in a usable state for development, and some of these steps might have their own prerequisites, like ensuring dependencies are installed first.

In a more complex real-world application, it's not hard to see how there could be tens of additional commands needing to be run for different purposes.

## The commands we want to run

To achieve this, we will want to run each of the following commands every time we start development on the application.

```bash
# Start the MySQL server
mysql.server start

# Start the Redis server
redis-server

# Install Composer dependencies
composer install

# Run a local PHP web server
php artisan serve

# Install NPM dependencies
npm install

# Watch for asset file changes
npm run watch
```

These commands are pretty simple but need to be done in a certain order.

- MySQL and Redis need to start before the PHP web server is ran
- Composer dependencies need to be installed before the PHP web server is started
- NPM dependencies need to be installed before the watcher is started

Additionally, the `redis-server`, `php artisan serve` and `npm run watch` commands are all long-running. That is, they don't return a value and finish running, their processes keep running in your terminal until you tell them to stop.

## The ampersand operators

Bash has a useful double-ampersand operator (`&&`) that's probably familiar to anyone that has worked with any programming languages before.

What it does is let you chain multiple commands together synchronously - it will only run the second command when the first has completed.

```bash
# `composer install` must finish BEFORE `php artisan serve` is executed
composer install && php artisan serve
```

What's a little bit less known to people unfamiliar with Bash is the single-ampersand operator (`&`).

What this does differently is that it directs the first command to run asynchronously in a separate, forked sub-shell, continuing and running the second command immediately after.

```bash
# Both `php artisan serve` and `npm run watch` will execute at the same time
php artisan serve & npm run watch
```

Typically you can only run a single long-running process in your terminal at once, requiring you to open multiple terminals to run multiple of them. This lets both execute at the same time, with all their output going to the same terminal.

We can combine both of these to run our commands in our desired order, chaining prerequisites with the double-ampersand to make sure they finish first, and the long-running processes with the single-ampersand so they don't block other commands from running.

```bash
mysql.server start &&\
redis-server &\
composer install &&\
php artisan serve &\
npm install &&\
npm run watch
```

Here, we can see that when we execute our script, all of the long-running commands end up running in parallel, but only after their prerequisites are finished.)

![Starting the script](/images/articles/start%20dev.sh.gif)

## Keep the process running

Now our script is running all our services and watchers at the same time from a single command, great! However, if we want to be able to stop them all at once when we're done working on this application, we first need to prevent our script from exiting once they're are started up.

To do this, we're going to have a function that runs a "while loop" forever, waiting a second between each iteration. We will give it an exit condition though, so that if the variable `$scriptCancelled` is ever set to `"true"`, the loop will stop.

```bash
scriptCancelled="false"

waitforcancel() {
    while :
    do
        if [ "$scriptCancelled" == "true" ]; then
            return
        fi

        sleep 1
    done
}
```

By executing this function after the main application, we can prevent the script from returning too soon, as it will never get out of the loop until that condition is met.

```bash
# mysql.server start &&\
# ...

waitforcancel
return 0
```

## Breaking the condition with a trap

Now we have the script running in a "limbo" state, where every second it's checking if the `$scriptCancelled` variable is `"true"` before it can stop - so we need to find a way to set that variable when we want.

Using the `trap` command, we can detect when the script is interrupted (the `INT` signal) and execute our own `quitjobs` function as we want.

```bash
trap quitjobs INT
```

Inside our `quitjobs` function, we want first to set the `$scriptCancelled` variable to `"true"` to denote that the "while loop" should stop and the script can return.

At the same time, we want to stop listening for the interrupted signal so it can't be triggered more than once, which we can unset by declaring `trap - INT`, then exiting from the function.

```bash
quitjobs() {
    scriptCancelled="true"
    trap - INT
    exit
}
```

However, now that the "while loop" has stopped iterating, our script has finished running, but all of our long-running commands are still being executed in the background. We want to stop them as soon as we tell our script to cancel.

## Killing the processes

Now that we know when the user wants to stop the running processes, we can explicitly stop our jobs, killing off all sub-processes.

To do this, we can get our current process ID with the `$$` variable, and pass it through to the `pkill` command's `-P` flag. This allows us to kill all sub-processes based on the parent ID - precisely what we want!

We can update our `quitjobs` function to do this too.

```bash
quitjobs() {
    echo ""
    pkill -P $$
    echo "Killed all running jobs".
    scriptCancelled="true"
    trap - INT
    exit
}
```

Here, we can see that with the script already running and watching, we only need to cancel the script with the CTRL+C hotkey, and it'll stop all of the sub-processes running gracefully.

![Stopping the script](/images/articles/stop%20dev.sh.gif)

## Final script

Putting everything together, we can see our final script with our custom functions and variables in place.

```bash
#!/bin/bash
#
# Start the application and its prerequisites at localhost:8000, and
# automatically re-compile assets whenever any changes are made to
# them. You can stop the application running by pressing CTRL+C.
#

# Triggered when the user interrupts the script to stop it.
trap quitjobs INT
quitjobs() {
    echo ""
    pkill -P $$
    echo "Killed all running jobs".
    scriptCancelled="true"
    trap - INT
    exit
}

# Wait for user input so the jobs can be quit afterwards.
scriptCancelled="false"
waitforcancel() {
    while :
    do
        if [ "$scriptCancelled" == "true" ]; then
            return
        fi

        sleep 1
    done
}

# The actual commands we want to execute.
mysql.server start &&\
redis-server &\
composer install &&\
php artisan serve &\
npm install &&\
npm run watch

# Trap the input and wait for the script to be cancelled.
waitforcancel
return 0
```

Now we can start our application's development environment with a single command, and not have to worry about cleaning up all the processes and ports that might get left open when we're done!

## Conclusion

This approach is a reasonably straightforward way to simplify your toolchain to just one place.

I've personally used it on a project that needed roughly 20 different commands to be run in to get all the services needed up and running for development, and it's significantly decreased my time required to get working on the application each day.

It's not a proper alternative to a solution like Docker where that's appropriate, but for many cases, I think this is good enough.
