<?php $title = 'Registrieren'; ?>
<div class="reactive-bg"></div>
<div class="login-card">
    <div class="brand-icon"><i class="fas fa-earth-europe"></i></div>
    <div class="brand-title">Vorbereitung</div>
    <div class="brand-subtitle">Erstelle dein Konto. Admins schalten den Zugriff frei.</div>

    <form id="registerForm">
        <?php echo csrfField(); ?>
        <div class="input-group">
            <label for="name">Vorname</label>
            <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" id="name" name="name" required>
            </div>
        </div>

        <div class="input-group">
            <label for="surname">Nachname</label>
            <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input type="text" id="surname" name="surname" required>
            </div>
        </div>

        <div class="input-group">
            <label for="email">E-Mail</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" required>
            </div>
        </div>

        <div class="input-group">
            <label for="phone">Telefon (optional)</label>
            <div class="input-wrapper">
                <i class="fas fa-phone"></i>
                <input type="text" id="phone" name="phone">
            </div>
        </div>

        <div class="input-group">
            <label for="password">Passwort</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
            </div>
        </div>

        <button class="btn primary w-full" type="submit">Registrieren</button>
        <div id="registerMessage" class="form-message"></div>
    </form>

    <div class="auth-switch">
        Bereits registriert? <a href="<?php echo url('login'); ?>">Login</a>
    </div>
</div>

<script>
const registerForm = document.getElementById('registerForm');
const registerMessage = document.getElementById('registerMessage');

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    registerMessage.textContent = '';

    const payload = {
        name: registerForm.name.value,
        surname: registerForm.surname.value,
        email: registerForm.email.value,
        phone: registerForm.phone.value,
        password: registerForm.password.value,
    };

    try {
        const res = await fetch('<?php echo url('auth/register'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            registerMessage.textContent = data.message || 'Registrierung fehlgeschlagen';
            registerMessage.classList.add('error');
            return;
        }

        registerMessage.textContent = data.message || 'Registrierung erfolgreich. Bitte auf Freischaltung warten.';
        registerMessage.classList.remove('error');
        registerForm.reset();
    } catch (err) {
        registerMessage.textContent = 'Netzwerkfehler. Bitte später erneut versuchen.';
        registerMessage.classList.add('error');
    }
});
</script>
