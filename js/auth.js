/**
 * German Quiz Master - Authentication & Logic
 * Uses Node.js Backend API
 */

const Auth = {
    init: function () {
        this.checkSession();
        this.setupAutoLogout();
    },

    // --- User Management ---

    getUsers: async function () {
        try {
            const response = await fetch('/api/users');
            const users = await response.json();
            return users;
        } catch (e) {
            console.error(e);
            return [];
        }
    },

    register: async function (user) {
        try {
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(user)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    addUser: async function (user) {
        try {
            const response = await fetch('/api/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(user)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    deleteUser: async function (email) {
        try {
            const response = await fetch(`/api/users/${email}`, {
                method: 'DELETE'
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    updateUserStatus: async function (email, status) {
        try {
            const response = await fetch(`/api/users/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ isActive: status })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    updateUserAccess: async function (email, accessB1, accessB2) {
        try {
            const response = await fetch(`/api/users/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accessB1, accessB2 })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    updateDeviceLimit: async function (email, limit) {
        try {
            const response = await fetch(`/api/users/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deviceLimit: limit })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    // --- Session Management ---

    login: async function (email, password) {
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const result = await response.json();
            if (result.success) {
                // Store minimal user info for synchronous checks if needed, 
                // but rely on server session for security.
                localStorage.setItem('quiz_user_role', result.user.role);
            }
            return result;
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    },

    logout: async function () {
        await fetch('/api/logout', { method: 'POST' });
        localStorage.removeItem('quiz_user_role');
        window.location.href = 'home.html';
    },

    getCurrentUser: async function () {
        try {
            const response = await fetch('/api/me');
            const result = await response.json();
            return result.success ? result.user : null;
        } catch (e) {
            return null;
        }
    },

    checkSession: async function () {
        const user = await this.getCurrentUser();
        const path = window.location.pathname;

        // Pages that are accessible without login
        const isPublicPage = path.endsWith('login.html') ||
            path.endsWith('register.html') ||
            path.endsWith('home.html') ||
            path.endsWith('admin_login.html') ||
            path === '/';

        // Pages that should redirect if already logged in (e.g. login/register)
        const isGuestPage = path.endsWith('login.html') ||
            path.endsWith('register.html') ||
            path.endsWith('admin_login.html');

        if (!user) {
            // Not logged in
            if (!isPublicPage) {
                // Trying to access protected page -> Redirect to login
                window.location.href = 'login.html';
            }
        } else {
            // Logged in
            if (isGuestPage) {
                // On a guest page -> Redirect to dashboard
                if (user.role === 'admin') {
                    window.location.href = 'admin.html';
                } else {
                    window.location.href = 'home.html';
                }
            }
        }
    },

    requireAdmin: async function () {
        const user = await this.getCurrentUser();
        if (!user || user.role !== 'admin') {
            window.location.href = 'home.html';
        }
    },

    // --- Exam Access Control ---

    checkExamAccess: async function (examLevel) {
        const user = await this.getCurrentUser();
        if (!user) return false;

        if (examLevel === 'B1') {
            return user.accessB1 === true;
        } else if (examLevel === 'B2') {
            return user.accessB2 === true;
        }
        return false;
    },

    requireExamAccess: async function (examLevel) {
        const user = await this.getCurrentUser();

        // First check if user is logged in
        if (!user) {
            // Save the requested exam page for redirect after login
            const requestedPage = examLevel === 'B1' ? 'index1.html' : 'index2.html';
            localStorage.setItem('quiz_redirect_after_login', requestedPage);
            localStorage.setItem('quiz_requested_exam', examLevel);

            alert('Bitte melden Sie sich an, um auf die Prüfung zuzugreifen.');
            window.location.href = 'login.html';
            return;
        }

        // REMOVED ACCESS CHECK - Open to all logged in users
        /* 
        const hasAccess = examLevel === 'B1' ? user.accessB1 : user.accessB2;

        if (!hasAccess) {
            alert(`Sie haben keinen Zugang zur Prüfung ${examLevel}. Bitte kontaktieren Sie den Administrator.`);
            window.location.href = 'home.html';
            return;
        }
        */

        // If user has access, redirect to the exam page ONLY if not already there
        const examPage = examLevel === 'B1' ? 'index1.html' : 'index2.html';
        const currentPage = window.location.pathname.split('/').pop();

        if (currentPage !== examPage) {
            window.location.href = examPage;
        }
    },

    // --- Settings ---

    getSettings: function () {
        // Settings still local for now, or could be moved to DB
        return JSON.parse(localStorage.getItem('quiz_settings') || '{"logoutTimer": 15}');
    },

    saveSettings: function (newSettings) {
        const current = this.getSettings();
        const updated = { ...current, ...newSettings };
        localStorage.setItem('quiz_settings', JSON.stringify(updated));
        this.setupAutoLogout();
    },

    // --- Auto Logout ---

    setupAutoLogout: function () {
        // Only run if we think we are logged in (quick check)
        // Real check happens via ping

        const settings = this.getSettings();
        const timeoutMinutes = settings.logoutTimer || 15;
        const timeoutMs = timeoutMinutes * 60 * 1000;

        let logoutTimer;
        let warningTimer;

        const resetTimers = () => {
            clearTimeout(logoutTimer);
            clearTimeout(warningTimer);

            // Warning 30s before
            warningTimer = setTimeout(() => {
                this.showWarning();
            }, timeoutMs - 30000);

            // Logout
            logoutTimer = setTimeout(() => {
                this.logout();
            }, timeoutMs);

            this.hideWarning();

            // Send ping to server to keep session alive
            fetch('/ping').catch(() => { });
        };

        // Events to reset timer
        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, resetTimers);
        });

        resetTimers();
    },

    showWarning: function () {
        let warningBox = document.getElementById('auth-warning-box');
        if (!warningBox) {
            warningBox = document.createElement('div');
            warningBox.id = 'auth-warning-box';
            warningBox.style.cssText = `
                position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
                background: #ff4444; color: white; padding: 15px 25px;
                border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 10000; font-family: sans-serif; font-weight: bold;
                display: none;
            `;
            warningBox.textContent = 'Sie werden in 30 Sekunden automatisch abgemeldet.';
            document.body.appendChild(warningBox);
        }
        warningBox.style.display = 'block';
    },

    hideWarning: function () {
        const warningBox = document.getElementById('auth-warning-box');
        if (warningBox) warningBox.style.display = 'none';
    },

    // --- Maintenance Mode ---

    checkMaintenance: async function () {
        try {
            const response = await fetch('/api/maintenance-status');
            const result = await response.json();
            return result.maintenance;
        } catch (e) {
            return false;
        }
    },

    toggleMaintenance: async function (enabled) {
        try {
            const response = await fetch('/api/maintenance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ enabled })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: 'Server error' };
        }
    }
};

// Initialize on load
Auth.init();
