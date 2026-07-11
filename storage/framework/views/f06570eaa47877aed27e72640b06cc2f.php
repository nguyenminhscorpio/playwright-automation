<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e($title ?? 'FlashMind'); ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400" rel="stylesheet">

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="auth-body">
        <div class="auth-shell">

            
            <aside class="auth-panel">
                <div class="auth-panel__inner">
                    <div class="auth-panel__logo">
                        <span class="material-symbols-outlined">auto_stories</span>
                    </div>
                    <h1 class="auth-panel__brand">FlashMind</h1>
                    <p class="auth-panel__tagline">The smarter way to learn anything — powered by spaced repetition.</p>

                    <ul class="auth-panel__features">
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">bolt</span></span>
                            <span>Intelligent spaced repetition scheduling</span>
                        </li>
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">trending_up</span></span>
                            <span>Track streaks, goals and mastery progress</span>
                        </li>
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">upload_file</span></span>
                            <span>Import Anki decks in seconds</span>
                        </li>
                    </ul>
                </div>

                
                <div class="auth-panel__blob auth-panel__blob--1"></div>
                <div class="auth-panel__blob auth-panel__blob--2"></div>
            </aside>

            
            <main class="auth-main">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

        </div>
    </body>
</html>
<?php /**PATH C:\Users\admin\Documents\mine\vibe-coding\resources\views/layouts/auth.blade.php ENDPATH**/ ?>