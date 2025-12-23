<?php
$title = 'Startseite';
$user = $user ?? currentUser();
?>
<div class="reactive-bg"></div>

<nav class="navbar fade-in">
    <a href="<?php echo url('home'); ?>" class="logo">
        <i class="fas fa-earth-europe" style="color: var(--primary-color); margin-right: 10px;"></i>
        Vorbereitung
    </a>
    <div class="menu-container">
        <div class="nav-segment">
            <a class="btn ghost" href="<?php echo url('profile'); ?>" title="Profil">
                <i class="fas fa-user"></i>
            </a>
            <span class="chip"><?php echo e($user['name']); ?></span>
            <a class="btn" href="<?php echo url('logout'); ?>"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <div class="nav-links">
        <a href="<?php echo url('b1'); ?>" class="btn ghost">B1</a>
        <a href="<?php echo url('b2'); ?>" class="btn ghost">B2</a>
        <?php 
        $currentUser = currentUser();
        if ($currentUser && isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
            <a href="<?php echo url('admin'); ?>" class="btn ghost">Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="two-column-layout fade-in" style="animation-delay: 0.1s;">
    <div class="welcome-column">
        <div class="welcome-content">
            <h1 class="welcome-title">Willkommen, <?php echo e($user['name']); ?>!</h1>
            <p class="welcome-description">Wähle dein Niveau und starte mit echten telc B1/B2 Prüfungen.</p>
            <div class="welcome-actions">
                <a class="btn primary" href="<?php echo url('b1'); ?>">B1 Dashboard</a>
                <a class="btn primary" href="<?php echo url('b2'); ?>">B2 Dashboard</a>
            </div>
        </div>
    </div>
    <div class="stats-column">
        <div class="card glass">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 10px; color: var(--primary-color);"></i>Deine Statistiken</h3>
            </div>
            <div class="card-body grid-2">
                <div class="stat-item">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--b1-accent-start), var(--b1-accent-end));">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="muted">B1 Versuche</div>
                        <div class="value"><?php echo $stats['B1']['total_quizzes'] ?? 0; ?></div>
                        <div class="muted"><?php echo number_format($stats['B1']['average_percentage'] ?? 0, 1); ?>% Ø</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--b2-accent-start), var(--b2-accent-end));">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <div class="muted">B2 Versuche</div>
                        <div class="value"><?php echo $stats['B2']['total_quizzes'] ?? 0; ?></div>
                        <div class="muted"><?php echo number_format($stats['B2']['average_percentage'] ?? 0, 1); ?>% Ø</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass" style="margin-top: 16px;">
            <div class="card-header">
                <h3>Letzte Ergebnisse</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($recentResults)): ?>
                    <ul class="timeline">
                        <?php foreach ($recentResults as $result): ?>
                            <li>
                                <strong><?php echo e($result['quiz_level']); ?> - Quiz <?php echo e($result['quiz_id']); ?></strong>
                                <span><?php echo e($result['score']); ?>/<?php echo e($result['total_questions']); ?> • <?php echo e($result['completed_at']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="muted">Noch keine Ergebnisse. Starte ein Quiz!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
