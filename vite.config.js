/**
 * إعداد Vite: يربط Laravel بملفات CSS/JS في resources/
 * الأمر المعتاد أثناء التطوير: npm run dev — ثم @vite() في القوالب يحقن المسارات الساخنة.
 */
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
       laravel({
            input: [
                'resources/css/global.css',
                'resources/css/header.css',
                'resources/css/footer.css',
                'resources/js/app.js', 
                'resources/js/bootstrap.js', 
                'resources/css/service-construction.css',
                'resources/css/about.css',
                'resources/css/careers.css',
                'resources/css/contact.css',
                'resources/css/index.css',
                'resources/css/services.css',
                'resources/css/login.css',
                'resources/css/news.css',
                'resources/css/projects.css',
                'resources/css/services.css',
                'resources/css/tenders.css',
                'resources/css/service-details.css',
                'resources/css/project-details.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

