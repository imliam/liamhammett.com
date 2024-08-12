const plugin = require('tailwindcss/plugin')

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/**/*.php",
        "./content/**/*.md"
    ],
    theme: {
        extend: {
            fontFamily: {
                title: [
                    '"Ostrich Sans Rounded"',
                    '"Playfair Display"',
                    'sans-serif',
                ],
                handwritten: [
                    'Handlee',
                    '"Permanent Marker"',
                    '"Segoe Print"',
                    '"Bradley Hand"',
                    'Chilanka',
                    'TSCu_Comic',
                    'casual',
                    'cursive',
                ],
                fancy: [
                    'Spirax',
                    'cursive',
                ],
                mono: [
                    '"Operator Mono Lig"',
                    '"Operator Mono"',
                    '"Monaspace Neon"',
                    '"Dank Mono"',
                    '"JetBrains Mono"',
                    '"Fira Code Retina"',
                    '"Fira Code"',
                    '"Source Code Pro"',
                    'Menlo',
                    'Monaco',
                    'Consolas',
                    '"Courier New"',
                    'monospace'
                ],
            },
            boxShadow: theme => ({
                depth: '0 5px 10px rgba(0, 0, 0, 0.6)',
                popover: '0 0 0 1px rgba(40,45,50,0.05), 0 0 0 1px rgba(40,45,50,0.05), 0 2px 7px 1px rgba(40,45,50,0.16)',
                set: 'rgba(50,50,93,.1) 0 0 0 1px, rgba(50,50,93,.08) 0 2px 5px, rgba(0,0,0,.07) 0 1px 1.5px,rgba(0,0,0,.08) 0 1px 2px 0, transparent 0 0 0 0',
                none: 'none',
                solid: '0 0 0 2px currentColor',
                sq: '0.05em 0.05em 0 var(--tw-shadow-color)',
            }),
            textShadow: {
                sm: '0 1px 2px var(--tw-shadow-color)',
                DEFAULT: '0 2px 4px var(--tw-shadow-color)',
                lg: '0 8px 16px var(--tw-shadow-color)',
                sq: '0.05em 0.05em 0 var(--tw-shadow-color)',
              },
        },
    },
    plugins: [
        require("@tailwindcss/typography"),
        require("@tailwindcss/forms"),
        require('tailwind-container-break-out'),
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
              {
                'text-shadow': (value) => ({
                  textShadow: value,
                }),
              },
              { values: theme('textShadow') }
            )
          }),
    ],
};
