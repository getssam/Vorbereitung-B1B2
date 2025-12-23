<?php $title = '404 - Seite nicht gefunden'; ?>
<div class="reactive-bg"></div>
<div class="error-page">
    <div class="error-card">
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h1>404 - Seite nicht gefunden</h1>
        <p>Die angeforderte Seite konnte nicht gefunden werden.</p>
        <?php if (!empty($url)): ?>
            <p class="muted">Pfad: <?php echo e($url); ?></p>
        <?php endif; ?>
        <a class="btn primary" href="<?php echo url('home'); ?>">
            <i class="fas fa-home" style="margin-right: 8px;"></i>
            Zur Startseite
        </a>
    </div>
</div>
