<?php
$appConfig = require __DIR__ . '/../../../config/app.php';
$pageTitle = isset($title) ? $title . ' | ' . $appConfig['name'] : $appConfig['name'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo url('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php echo $content; ?>
    <script>
        // Reactive background mouse tracking
        document.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;
            document.documentElement.style.setProperty('--mouse-x', x + '%');
            document.documentElement.style.setProperty('--mouse-y', y + '%');
        });
    </script>
</body>
</html>
