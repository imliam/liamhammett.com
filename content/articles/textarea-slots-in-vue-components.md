---
title: Textarea Slots in Vue Components
slug: textarea-slots-in-vue-components
published_at: 2019-08-13
strapline: ""
synopsis: ""
tags:
    - Vue
---

If you've ever tried to build a custom component to wrap around the `<textarea>` element with Vue, you'll find that it's not as simple as you first think to keep the behaviour the same as the native element.

In this post I'm going to quickly show the problem that you'll likely run into, and a simple solution for it.

Let's take a look...

## The problem: `<slot />` is not evaluated

Let's imagine it contains a `<slot />` element like you'd do with any other Vue component.

```vue
<!-- CustomTextarea.vue -->

<template>
    <textarea><slot /></textarea>
</template>
```

And use it like any other:

```vue
<custom-textarea>Hello world</custom-textarea>
```

At this point, you might expect the textarea's value to be "Hello world", however you would be mistaken.

The contents of the `<textarea>` element become the text value of the element in the source code and don't get evaluated by Vue, so the form element's value is actually the string "".

## The solution: Binding with `v-model`

To work around this, instead of declaring a slot, we can opt to bind the textarea's data to a model, and setting the initial value of that from the `$slots.default` value within the component, if it exists.

```vue
<!-- CustomTextarea.vue -->

<template>
    <textarea v-model="currentValue"></textarea>
</template>

<script>
    export default {
        data() {
            return {
                currentValue: this.getDefaultValue()
            }
        },

        methods: {
            getDefaultValue() {
                if (this.$slots.default && this.$slots.default.length) {
                    return this.$slots.default[0].text
                }

                return ''
            }
        },
    }
</script>
```

Now the textarea's value is "Hello world" as we expect, and because it's bound as a model, the value of `currentValue` will update as you type or change the textarea's contents.

## One step further: Optionally allowing a prop

If desired, we could go one further than this and allow the textarea's value to be passed as a slot like the regular `<textarea>` element works, OR with a prop like other inputs and Vue components typically work.

To do this, we must allow a prop that's not required, but has a default value of an empty string. If no slot exists, we'll set the value to this prop's value.

```vue
<!-- CustomTextarea.vue -->

<template>
    <textarea v-model="currentValue"></textarea>
</template>

<script>
    export default {
        props: {
            value: {
                required: false,
                type: String,
                default: '',
            },
        },

        data() {
            return {
                currentValue: this.getDefaultValue()
            }
        },

        methods: {
            getDefaultValue() {
                if (this.$slots.default && this.$slots.default.length) {
                    return this.$slots.default[0].text
                }

                return this.value
            }
        },
    }
</script>
```

With this, we can now use our component like a regular `<textarea>` with the value within the element's opening and closing tags, or passing it as a property like `<input>` elements, and both will work.

```vue
<custom-textarea>Value passed as a slot.</custom-textarea>

<custom-textarea value="Value passed as an attribute." />
```

This gives flexibility to use it however you like depending on your preference, and you can now expand your component to do anything else you need around the textarea element itself.
