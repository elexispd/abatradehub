import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design tokens from designs/DESIGN.md (Stitch export).
 * Views must use these tokens (bg-primary, text-on-surface, text-headline-md, ...)
 * and never hard-coded hex values.
 */

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/src/Features/SupportPagination/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/**/*.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                'background': '#fff8f5',
                'on-background': '#1f1b17',
                'surface': '#fff8f5',
                'surface-bright': '#fff8f5',
                'surface-dim': '#e2d8d2',
                'surface-tint': '#a14009',
                'surface-variant': '#ebe1da',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#fcf2eb',
                'surface-container': '#f6ece5',
                'surface-container-high': '#f1e6e0',
                'surface-container-highest': '#ebe1da',
                'on-surface': '#1f1b17',
                'on-surface-variant': '#57423a',
                'inverse-surface': '#352f2b',
                'inverse-on-surface': '#f9efe8',
                'outline': '#8a7268',
                'outline-variant': '#dec0b5',

                'primary': '#9f3e07',
                'on-primary': '#ffffff',
                'primary-container': '#c05621',
                'on-primary-container': '#fffeff',
                'inverse-primary': '#ffb596',
                'primary-fixed': '#ffdbcd',
                'primary-fixed-dim': '#ffb596',
                'on-primary-fixed': '#360f00',
                'on-primary-fixed-variant': '#7d2d00',

                'secondary': '#2c694e',
                'on-secondary': '#ffffff',
                'secondary-container': '#aeeecb',
                'on-secondary-container': '#316e52',
                'secondary-fixed': '#b1f0ce',
                'secondary-fixed-dim': '#95d4b3',
                'on-secondary-fixed': '#002114',
                'on-secondary-fixed-variant': '#0e5138',

                'tertiary': '#7f5420',
                'on-tertiary': '#ffffff',
                'tertiary-container': '#9b6c36',
                'on-tertiary-container': '#fffeff',
                'tertiary-fixed': '#ffddbb',
                'tertiary-fixed-dim': '#f5bc7e',
                'on-tertiary-fixed': '#2b1700',
                'on-tertiary-fixed-variant': '#653e0b',

                'error': '#ba1a1a',
                'on-error': '#ffffff',
                'error-container': '#ffdad6',
                'on-error-container': '#93000a',
            },

            fontFamily: {
                // Plus Jakarta Sans is loaded from Google Fonts in the layout; the rest is the fallback stack.
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },

            fontSize: {
                'display-lg': ['40px', { lineHeight: '48px', letterSpacing: '-0.02em', fontWeight: '700' }],
                'display-lg-mobile': ['30px', { lineHeight: '38px', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-lg': ['28px', { lineHeight: '36px', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-md': ['22px', { lineHeight: '30px', letterSpacing: '-0.005em', fontWeight: '600' }],
                'headline-sm': ['18px', { lineHeight: '26px', fontWeight: '600' }],
                'body-lg': ['16px', { lineHeight: '26px', fontWeight: '400' }],
                'body-md': ['14px', { lineHeight: '22px', fontWeight: '400' }],
                'body-sm': ['12px', { lineHeight: '18px', fontWeight: '400' }],
                'label-lg': ['14px', { lineHeight: '20px', letterSpacing: '0.01em', fontWeight: '600' }],
                'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.02em', fontWeight: '600' }],
                'label-sm': ['11px', { lineHeight: '14px', letterSpacing: '0.04em', fontWeight: '700' }],
            },

            borderRadius: {
                DEFAULT: '0.25rem',
                lg: '0.5rem',
                xl: '0.75rem',
                full: '9999px',
            },

            // Elevation levels from DESIGN.md (warm-tinted ambient shadows).
            boxShadow: {
                'header': '0 1px 8px rgba(42, 37, 33, 0.05)',
                'footer': '0 -1px 8px rgba(42, 37, 33, 0.03)',
                'raised': '0 4px 16px -2px rgba(42, 37, 33, 0.06)',
                'overlay': '0 12px 32px -4px rgba(42, 37, 33, 0.12)',
            },

            spacing: {
                'gutter': '1.25rem',
                'gutter-mobile': '0.75rem',
                'margin': '2rem',
                'margin-mobile': '1rem',
                'space-xs': '0.25rem',
                'space-sm': '0.5rem',
                'space-md': '1rem',
                'space-lg': '1.5rem',
                'space-xl': '2.5rem',
            },

            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0%)' },
                    '100%': { transform: 'translateX(-50%)' },
                },
            },
            animation: {
                marquee: 'marquee 35s linear infinite',
            },
        },
    },

    plugins: [forms],
};
