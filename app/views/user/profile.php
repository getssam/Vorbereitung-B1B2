<?php
$title = 'Profil';
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

<div class="profile-container fade-in" style="animation-delay: 0.1s;">
    <div class="profile-header">
        <a href="<?php echo url('home'); ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Zurück</a>
        <h1>Mein Profil</h1>
        <p class="muted">Verwalte deine persönlichen Informationen und Passwort</p>
    </div>

    <div class="profile-content">
        <!-- Profile Information Card -->
        <div class="card glass">
            <div class="card-header">
                <h3><i class="fas fa-user" style="margin-right: 10px; color: var(--primary-color);"></i>Persönliche Informationen</h3>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="form-group">
                        <label for="name">Vorname</label>
                        <input type="text" id="name" name="name" value="<?php echo e($user['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="surname">Nachname</label>
                        <input type="text" id="surname" name="surname" value="<?php echo e($user['surname'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-Mail</label>
                        <input type="email" id="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Telefon (optional)</label>
                        <input type="text" id="phone" name="phone" value="<?php echo e($user['phone'] ?? ''); ?>">
                    </div>
                    <div id="profileMessage" class="form-message" style="display: none;"></div>
                    <button type="submit" class="btn primary">
                        <i class="fas fa-save" style="margin-right: 8px;"></i>
                        Änderungen speichern
                    </button>
                </form>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="card glass" style="margin-top: 24px;">
            <div class="card-header">
                <h3><i class="fas fa-lock" style="margin-right: 10px; color: var(--primary-color);"></i>Passwort ändern</h3>
            </div>
            <div class="card-body">
                <form id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Aktuelles Passwort</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Neues Passwort</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <small class="form-hint">Mindestens 6 Zeichen</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Passwort bestätigen</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    <div id="passwordMessage" class="form-message" style="display: none;"></div>
                    <button type="submit" class="btn primary">
                        <i class="fas fa-key" style="margin-right: 8px;"></i>
                        Passwort ändern
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form
    const profileForm = document.getElementById('profileForm');
    const profileMessage = document.getElementById('profileMessage');

    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('name').value,
            surname: document.getElementById('surname').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value || null
        };

        try {
            const response = await fetch('<?php echo url('api/user/profile'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showMessage(profileMessage, data.message, 'success');
                // Update user name in navbar
                const chip = document.querySelector('.chip');
                if (chip) {
                    chip.textContent = formData.name;
                }
            } else {
                showMessage(profileMessage, data.message, 'error');
            }
        } catch (error) {
            showMessage(profileMessage, 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.', 'error');
        }
    });

    // Password form
    const passwordForm = document.getElementById('passwordForm');
    const passwordMessage = document.getElementById('passwordMessage');

    passwordForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            current_password: document.getElementById('current_password').value,
            new_password: document.getElementById('new_password').value,
            confirm_password: document.getElementById('confirm_password').value
        };

        if (formData.new_password !== formData.confirm_password) {
            showMessage(passwordMessage, 'Die neuen Passwörter stimmen nicht überein.', 'error');
            return;
        }

        try {
            const response = await fetch('<?php echo url('api/user/password'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                showMessage(passwordMessage, data.message, 'success');
                passwordForm.reset();
            } else {
                showMessage(passwordMessage, data.message, 'error');
            }
        } catch (error) {
            showMessage(passwordMessage, 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.', 'error');
        }
    });

    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = 'form-message ' + type;
        element.style.display = 'block';
        
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
});
</script>

