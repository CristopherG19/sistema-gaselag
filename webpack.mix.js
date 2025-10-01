const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/dbf-viewer.js', 'public/js') // Agregar esta línea
   .postCss('resources/css/app.css', 'public/css', [
       //
   ]);