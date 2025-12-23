<?php $title = 'Admin Login'; ?>
<div class="reactive-bg"></div>
<div class="login-card">
    <div class="brand-icon"><i class="fas fa-shield-alt"></i></div>
    <div class="brand-title">Admin Panel</div>
    <div class="brand-subtitle">Administrator access only.</div>

    <form id="adminLoginForm">
        <?php echo csrfField(); ?>
        <div class="input-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" required>
            </div>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
            </div>
        </div>

        <button class="btn primary w-full" type="submit">Login</button>
        <div id="adminLoginMessage" class="form-message"></div>
    </form>

    <div class="auth-switch">
        Back to <a href="<?php echo url('login'); ?>">User Login</a>
    </div>
</div>

<script>
const adminLoginForm = document.getElementById('adminLoginForm');
const adminLoginMessage = document.getElementById('adminLoginMessage');

adminLoginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    adminLoginMessage.textContent = '';

    const payload = {
        email: adminLoginForm.email.value,
        password: adminLoginForm.password.value
    };

    try {
        const res = await fetch('<?php echo url('admin/auth'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            adminLoginMessage.textContent = data.message || 'Login failed';
            adminLoginMessage.classList.add('error');
            return;
        }

        window.location.href = data.data.redirect;
    } catch (err) {
        adminLoginMessage.textContent = 'Network error. Please try again later.';
        adminLoginMessage.classList.add('error');
    }
});
</script>
