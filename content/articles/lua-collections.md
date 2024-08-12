---
title: Lua Collections
slug: lua-collections
published_at: 2018-03-04
strapline: ""
synopsis: ""
tags:
    - Lua
---

When working on Lua code bases, I frequently find that the code gets more difficult to read as the project gets more complex, partially due to how almost everything in Lua is an iterable table which allows for some extremely powerful functionality.

Introducing Lua Collections.

<https://github.com/imliam/Lua-Collections>

[collections.lua](https://github.com/ImLiam/Lua-Collections/blob/master/collections.lua) is a port of the extremely robust collections found in [Laravel](https://laravel.com/docs/master/collections) directly to Lua, with some minor adjustments to account for the fact that Lua behaves slightly differently (such as no strict comparisons).

It allows you to take a typical table and use appropriately named methods to perform common functions, such as pagination, shuffling the order of the items, and getting the sum of all items.

There is also a lot of functionality based on using higher order functions, allowing you to pass closures to certain methods to customise what you need to do even further.

For an example of some of the functionality and fluidity this can open up when writing code, see this example below:

```lua
collect({'Cat', 'Dog', 'Mouse', 'Elephant', 'Hamster', 'Lion'})
        :shuffle()
        :map(function(key, value)
            return key, value:upper()
        end)
        :append('Coyote')
        :split(3)
        :all()

--[[
    {
        {'DOG', 'CAT', 'LION'},
        {'MOUSE', 'HAMSTER', 'ELEPHANT'},
        {'Coyote'}
    }
]]
```

You can include the [collections.lua](https://github.com/ImLiam/Lua-Collections/blob/master/collections.lua) file directly in your project or install it with LuaRocks, there are installation instructions in the repository.

You can learn a lot about using collections and higher-order functions to your full advantage in your code in Adam Wathan's course "[Refactoring to Collections](https://adamwathan.me/refactoring-to-collections/)" which I highly recommend.
