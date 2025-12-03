/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{php,html,js}", "!./node_modules/**"],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        bbcRed: '#b80000',
        bbcDark: '#1a1a1a',
        page: 'var(--bg-page)',
        'page-text': 'var(--text-page)',
        card: 'var(--bg-card)',
        'card-elevated': 'var(--bg-card-elevated)',
        'card-text': 'var(--text-card)',
        'border-color': 'var(--border-color)',
        'muted-bg': 'var(--bg-muted)',
        'muted-text': 'var(--text-muted)',
      },
      fontFamily: {
        sans: ['"Hind Siliguri"', 'sans-serif'],
      },
      boxShadow: {
        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'soft-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out forwards',
        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
        'zoom-in': 'zoomIn 0.3s ease-out forwards',
        'slide-in-right': 'slideInRight 0.3s ease-out forwards',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        zoomIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        slideInRight: {
          '0%': { transform: 'translateX(100%)' },
          '100%': { transform: 'translateX(0)' },
        },
      }
    },
  },
  plugins: [],
}