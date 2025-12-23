<?php $title = 'Login'; ?>
<div class="reactive-bg"></div>
<div class="login-card">
    <div class="brand-icon"><i class="fas fa-earth-europe"></i></div>
    <div class="brand-title">Vorbereitung</div>
    <div class="brand-subtitle">Melde dich an, um deine Prüfungen fortzusetzen.</div>

    <form id="loginForm">
        <?php echo csrfField(); ?>
        <div class="input-group">
            <label for="email">E-Mail</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
        </div>

        <div class="input-group">
            <label for="password">Passwort</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Passwort" required>
            </div>
        </div>

        <button class="btn primary w-full" type="submit">Anmelden</button>
        <div id="loginMessage" class="form-message"></div>
    </form>

    <div class="auth-switch">
        Kein Konto? <a href="<?php echo url('register'); ?>">Registrieren</a>
    </div>
</div>

<script>
const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    loginMessage.textContent = '';

    const payload = {
        email: loginForm.email.value,
        password: loginForm.password.value
    };

    try {
        const res = await fetch('<?php echo url('auth/login'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            loginMessage.textContent = data.message || 'Login fehlgeschlagen';
            loginMessage.classList.add('error');
            return;
        }

        window.location.href = data.data.redirect;
    } catch (err) {
        loginMessage.textContent = 'Netzwerkfehler. Bitte später erneut versuchen.';
        loginMessage.classList.add('error');
    }
});
</script>
