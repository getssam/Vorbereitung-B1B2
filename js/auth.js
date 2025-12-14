/**
 * German Quiz Master - Authentication & Logic
 * Uses Node.js Backend API
 */

const API = {
    BASE: '/api',
    USERS: '/api/users',
    REGISTER: '/api/register',
    LOGIN: '/api/login',
    LOGOUT: '/api/logout',
    ME: '/api/me',
    MAINTENANCE: '/api/maintenance',
    MAINTENANCE_STATUS: '/api/maintenance-status',
    PING: '/ping'
};

const BACKEND_URL = window.location.hostname.endsWith('github.io') ? 'https://YOUR-BACKEND.up.railway.app' : '';
const apiFetch = (path, options = {}) => {
    const url = BACKEND_URL ? `${BACKEND_URL}${path}` : path;
    return fetch(url, { credentials: 'include', ...options });
};

const Auth = {
    init: function () {
        this.checkSession();
        this.setupAutoLogout();
    },

    // --- User Management ---

    getUsers: async function () {
        try {
            const response = await apiFetch(API.USERS);
            if (!response.ok) throw new Error('Network response was not ok');
            const users = await response.json();
            return users;
        } catch (e) {
            console.error('Error fetching users:', e);
            return [];
        }
    },

    register: async function (user) {
        try {
            const response = await apiFetch(API.REGISTER, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(user)
            });
            return await response.json();
        } catch (e) {
            console.error('Registration failed:', e);
            return { success: false, message: 'Server error. Bitte versuchen Sie es später erneut.' };
        }
    },

    addUser: async function (user) {
        try {
            const response = await apiFetch(API.USERS, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(user)
            });
            return await response.json();
        } catch (e) {
            console.error('Error adding user:', e);
            return { success: false, message: 'Server error' };
        }
    },

    deleteUser: async function (email) {
        try {
            const response = await apiFetch(`${API.USERS}/${email}`, {
                method: 'DELETE'
            });
            return await response.json();
        } catch (e) {
            console.error('Error deleting user:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateUserStatus: async function (email, status) {
        try {
            const response = await apiFetch(`${API.USERS}/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ isActive: status })
            });
            return await response.json();
        } catch (e) {
            console.error('Error updating user status:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateUserAccess: async function (email, accessB1, accessB2) {
        try {
            const response = await apiFetch(`${API.USERS}/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accessB1, accessB2 })
            });
            return await response.json();
        } catch (e) {
            console.error('Error updating user access:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateDeviceLimit: async function (email, limit) {
        try {
            const response = await apiFetch(`${API.USERS}/${email}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deviceLimit: limit })
            });
            return await response.json();
        } catch (e) {
            console.error('Error updating device limit:', e);
            return { success: false, message: 'Server error' };
        }
    },

    // --- Session Management ---

    login: async function (email, password) {
        try {
            const response = await apiFetch(API.LOGIN, {
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
            console.error('Login error:', e);
            return { success: false, message: 'Verbindungsfehler zum Server.' };
        }
    },

    logout: async function () {
        try {
            await apiFetch(API.LOGOUT, { method: 'POST' });
        } catch (e) {
            console.error('Logout error:', e);
        }
        localStorage.removeItem('quiz_user_role');
        window.location.href = 'home.html';
    },

    getCurrentUser: async function () {
        try {
            const response = await apiFetch(API.ME);
            if (!response.ok) return null;
            const result = await response.json();
            return result.success ? result.user : null;
        } catch (e) {
            // Quietly fail for current user check (e.g. network issue or not logged in)
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
            window.location.href = 'admin_login.html';
        }
    },

    // --- Exam Access Control ---

    checkExamAccess: async function (examLevel) {
        const user = await this.getCurrentUser();
        if (!user) return false;

        // Grant access to everyone logged in
        return true;
    },

    requireExamAccess: async function (examLevel) {
        const user = await this.getCurrentUser();

        // First check if user is logged in
        if (!user) {
            // Save the requested exam page for redirect after login
            const requestedPage = examLevel === 'B1' ? 'index1.html' : 'index2.html';
            localStorage.setItem('quiz_redirect_after_login', requestedPage);
            localStorage.setItem('quiz_requested_exam', examLevel);
            window.location.href = 'login.html';
            return;
        }



        // If user has access, redirect to the exam page ONLY if not already there
        const examPage = examLevel === 'B1' ? 'index1.html' : 'index2.html';
        const currentPage = window.location.pathname.split('/').pop();

        if (currentPage !== examPage) {
            window.location.href = examPage;
        }
    },

    // --- Settings ---

    getSettings: function () {
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
            // We throttle this to not spam the server on every mousemove
            // But for simplicity in this version, we trust the debounce of the event listener roughly
        };

        // Throttled ping would be better, but we leave as is for now or just ping in interval
        // Actually the original code pinged on every resetTimer which is on every mousemove. 
        // That is bad. Let's fix it to only ping periodically.

        if (!this._pingInterval) {
            this._pingInterval = setInterval(() => {
                apiFetch(API.PING).catch(() => { });
            }, 60 * 1000); // Ping every minute
        }

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
            const response = await apiFetch(API.MAINTENANCE_STATUS);
            const result = await response.json();
            return result.maintenance;
        } catch (e) {
            return false;
        }
    },

    toggleMaintenance: async function (enabled) {
        try {
            const response = await apiFetch(API.MAINTENANCE, {
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

// Theme
const Theme = {
    get: function () {
        return localStorage.getItem('theme') || 'light';
    },
    set: function (t) {
        localStorage.setItem('theme', t);
        document.documentElement.setAttribute('data-theme', t);
    }
};

function updateThemeToggleIcon() {
    const el = document.getElementById('theme-toggle');
    if (!el) return;
    const isDark = Theme.get() === 'dark';
    el.innerHTML = isDark ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
}

function toggleTheme() {
    const next = Theme.get() === 'dark' ? 'light' : 'dark';
    Theme.set(next);
    updateThemeToggleIcon();
}

(function () {
    Theme.set(Theme.get());
    updateThemeToggleIcon();
    document.addEventListener('DOMContentLoaded', function () {
        const t = document.getElementById('theme-toggle');
        if (t) t.addEventListener('click', toggleTheme);
    });
})();

// Reactive background
(function () {
    function ensureLayer() {
        if (!document.querySelector('.reactive-bg')) {
            const layer = document.createElement('div');
            layer.className = 'reactive-bg';
            document.body.appendChild(layer);
        }
    }
    function onMove(e) {
        const x = e.clientX + 'px';
        const y = e.clientY + 'px';
        const root = document.documentElement;
        root.style.setProperty('--mouse-x', x);
        root.style.setProperty('--mouse-y', y);
    }
    document.addEventListener('DOMContentLoaded', () => {
        ensureLayer();
        window.addEventListener('mousemove', onMove);
        window.addEventListener('touchmove', (ev) => {
            const t = ev.touches && ev.touches[0];
            if (t) onMove({ clientX: t.clientX, clientY: t.clientY });
        }, { passive: true });
    });
})();
