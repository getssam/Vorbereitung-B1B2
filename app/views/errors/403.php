<?php $title = '403 - Forbidden'; ?>
<div class="reactive-bg"></div>
<div class="error-page">
    <div class="icon"><i class="fas fa-lock"></i></div>
    <h1>403 - Forbidden</h1>
    <p><?php echo e($message ?? 'You don\'t have permission to access this resource.'); ?></p>
    <?php if (isset($debug) && ($_ENV['APP_DEBUG'] ?? 'false') === 'true'): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px; font-family: monospace; font-size: 0.85rem;">
            <strong>Debug Info:</strong><br>
            User ID: <?php echo e($debug['user_id'] ?? 'N/A'); ?><br>
            access_b1 (raw): <?php echo e(var_export($debug['access_b1_raw'] ?? 'N/A', true)); ?><br>
            access_b1 (type): <?php echo e($debug['access_b1_type'] ?? 'N/A'); ?><br>
            access_b1 (int): <?php echo e($debug['access_b1_int'] ?? 'N/A'); ?>
        </div>
    <?php endif; ?>
    <a class="btn primary" href="<?php echo url('home'); ?>">
        <i class="fas fa-home" style="margin-right: 8px;"></i>
        Zur Startseite
    </a>
</div>
