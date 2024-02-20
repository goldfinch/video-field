import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import autoprefixer from 'autoprefixer';

export default defineConfig(({ command, mode, ssrBuild }) => ({
  resolve: {
    alias: {},
  },

  build: {
    chunkSizeWarningLimit: 1500,
    emptyOutDir: true,
    outDir: '../dist',
    rollupOptions: {
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return '[name][extname]';
          }
          if (
            assetInfo.name.match(/(\.(woff2?|eot|ttf|otf)|font\.svg)(\?.*)?$/)
          ) {
            return 'fonts/[name][extname]';
          }
          if (assetInfo.name.match(/\.(jpg|png|svg)$/)) {
            return 'images/[name][extname]';
          }

          return 'js/[name][extname]';
        },
      },
    },
  },

  plugins: [
    laravel({
      input: ['src/video-styles.scss', 'src/video.js'],
      refresh: true,
    }),
  ],

  css: {
    postcss: {
      plugins: [autoprefixer],
    },
  },
}));
