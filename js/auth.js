/**
 * German Quiz Master - Authentication & Logic (Supabase Version)
 * Replaces Node.js Backend with Supabase
 */

const Auth = {
    init: async function () {
        // Wait for DOM to be ready to ensure scripts are loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
            return;
        }

        this.checkSession();
        this.setupAutoLogout();

        // Listen for auth state changes
        if (supabaseClient) {
            supabaseClient.auth.onAuthStateChange((event, session) => {
                if (event === 'SIGNED_IN') {
                    // Update UI or redirect if needed
                    console.log('User signed in:', session.user.email);
                } else if (event === 'SIGNED_OUT') {
                    // Redirect to home if on protected page
                    const path = window.location.pathname;
                    if (path.includes('admin.html') || path.includes('quiz/')) {
                        window.location.href = 'home.html';
                    }
                }
            });
        }
    },

    // --- User Management ---

    getUsers: async function () {
        try {
            const { data, error } = await supabaseClient
                .from('users')
                .select('*');

            if (error) throw error;
            return data;
        } catch (e) {
            console.error('Error fetching users:', e);
            return [];
        }
    },

    register: async function (userData) {
        try {
            // 1. Sign up with Supabase Auth
            const { data: authData, error: authError } = await supabaseClient.auth.signUp({
                email: userData.email,
                password: userData.password
            });

            if (authError) throw authError;

            // 2. Create user profile in 'users' table
            // Note: Ideally this should be done via a Trigger in Supabase
            // But for now we do it client-side. RLS must allow insert.
            const { error: dbError } = await supabaseClient
                .from('users')
                .insert([{
                    email: userData.email, // Link by email (or use auth_id if you prefer)
                    name: userData.name,
                    surname: userData.surname,
                    role: 'user', // Default role
                    accessB1: 0,
                    accessB2: 0,
                    isActive: 0, // Wait for admin approval? Or 1 if auto-approve
                    deviceLimit: 1,
                    phone: userData.phone,
                    auth_id: authData.user.id // Link to Auth User
                }]);

            if (dbError) {
                // If DB insert fails, we might want to cleanup auth user or show error
                console.error('Error creating profile:', dbError);
                return { success: false, message: 'Fehler beim Erstellen des Profils.' };
            }

            return { success: true, message: 'Registrierung erfolgreich! Bitte warten Sie auf die Freischaltung.' };
        } catch (e) {
            console.error('Registration failed:', e);
            return { success: false, message: e.message || 'Registrierung fehlgeschlagen.' };
        }
    },

    addUser: async function (userData) {
        // Admin function to add user directly (creates auth user + profile)
        // Note: Creating auth user from client requires the user to confirm email or auto-confirm enabled
        // Admin usually shouldn't create users this way on client side without Service Key
        // But we can try the same flow as register
        return this.register(userData);
    },

    deleteUser: async function (email) {
        try {
            // Delete from public.users
            const { error } = await supabaseClient
                .from('users')
                .delete()
                .eq('email', email);

            if (error) throw error;

            // Note: Deleting from auth.users cannot be done from client side easily
            // You would need an Edge Function or delete manually in Dashboard
            console.log('User profile deleted. Note: Auth user must be deleted in Supabase Dashboard.');

            return { success: true };
        } catch (e) {
            console.error('Error deleting user:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateUserStatus: async function (email, status) {
        try {
            const { error } = await supabaseClient
                .from('users')
                .update({ isActive: status })
                .eq('email', email);

            if (error) throw error;
            return { success: true };
        } catch (e) {
            console.error('Error updating user status:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateUserAccess: async function (email, accessB1, accessB2) {
        try {
            const { error } = await supabaseClient
                .from('users')
                .update({ accessB1, accessB2 })
                .eq('email', email);

            if (error) throw error;
            return { success: true };
        } catch (e) {
            console.error('Error updating user access:', e);
            return { success: false, message: 'Server error' };
        }
    },

    updateDeviceLimit: async function (email, limit) {
        try {
            const { error } = await supabaseClient
                .from('users')
                .update({ deviceLimit: limit })
                .eq('email', email);

            if (error) throw error;
            return { success: true };
        } catch (e) {
            console.error('Error updating device limit:', e);
            return { success: false, message: 'Server error' };
        }
    },

    // --- Session Management ---

    loginWithGitHub: async function () {
        try {
            const { data, error } = await supabaseClient.auth.signInWithOAuth({
                provider: 'github',
                options: {
                    redirectTo: window.location.origin + '/home.html',
                },
            });
            if (error) throw error;
            return { success: true };
        } catch (e) {
            console.error('GitHub Login error:', e);
            return { success: false, message: e.message };
        }
    },

    login: async function (email, password) {
        try {
            const { data, error } = await supabaseClient.auth.signInWithPassword({
                email,
                password
            });

            if (error) throw error;

            // Fetch user profile to get role and status
            const { data: profile, error: profileError } = await supabaseClient
                .from('users')
                .select('*')
                .eq('email', email)
                .single();

            if (profileError || !profile) {
                // Profile missing?
                return { success: false, message: 'Benutzerprofil nicht gefunden.' };
            }

            if (!profile.isActive && profile.role !== 'admin') {
                await supabaseClient.auth.signOut();
                return { success: false, message: 'Ihr Konto ist noch nicht aktiviert.' };
            }

            // Store role for local checks
            localStorage.setItem('quiz_user_role', profile.role);

            // Return user object similar to old API
            return {
                success: true,
                user: profile
            };
        } catch (e) {
            console.error('Login error:', e);
            let msg = 'Login fehlgeschlagen. Überprüfen Sie Ihre Daten.';
            if (e.message) {
                if (e.message.includes('Invalid login credentials')) msg = 'Falsches Passwort oder E-Mail.';
                else if (e.message.includes('Email not confirmed')) msg = 'E-Mail noch nicht bestätigt.';
                else msg = e.message;
            }
            return { success: false, message: msg };
        }
    },

    logout: async function () {
        try {
            await supabaseClient.auth.signOut();
        } catch (e) {
            console.error('Logout error:', e);
        }
        localStorage.removeItem('quiz_user_role');
        window.location.href = 'home.html';
    },

    getCurrentUser: async function () {
        try {
            const { data: { session } } = await supabaseClient.auth.getSession();
            if (!session) return null;

            // Fetch full profile
            // We cache this to avoid too many requests? For now, fetch fresh.
            const { data: profile } = await supabaseClient
                .from('users')
                .select('*')
                .eq('email', session.user.email)
                .single();

            return profile;
        } catch (e) {
            return null;
        }
    },

    // --- Helper for Path Resolution ---
    resolvePath: function (filename) {
        // If we are in the 'quiz/' directory, go up one level
        if (window.location.pathname.includes('/quiz/')) {
            return '../' + filename;
        }
        // Otherwise, assume we are in root
        return filename;
    },

    checkSession: async function () {
        const user = await this.getCurrentUser();
        const path = window.location.pathname;

        // Pages that are accessible without login
        const isPublicPage = path.endsWith('login.html') ||
            path.endsWith('register.html') ||
            path.endsWith('home.html') ||
            path.endsWith('admin_login.html') ||
            path.endsWith('/') ||
            path.endsWith('maintenance.html');

        // Pages that should redirect if already logged in (e.g. login/register)
        const isGuestPage = path.endsWith('login.html') ||
            path.endsWith('register.html') ||
            path.endsWith('admin_login.html');

        // Check Maintenance Mode
        const maintenance = await this.checkMaintenance();
        if (maintenance && user?.role !== 'admin' && !path.endsWith('maintenance.html') && !path.endsWith('admin_login.html')) {
            window.location.href = this.resolvePath('maintenance.html');
            return;
        }

        if (!user) {
            // Not logged in
            if (!isPublicPage) {
                // Trying to access protected page -> Redirect to login
                window.location.href = this.resolvePath('login.html');
            }
        } else {
            // Logged in
            if (isGuestPage) {
                // On a guest page -> Redirect to dashboard
                if (user.role === 'admin') {
                    window.location.href = this.resolvePath('admin.html');
                } else {
                    window.location.href = this.resolvePath('home.html');
                }
            }
        }
    },

    requireAdmin: async function () {
        const user = await this.getCurrentUser();
        if (!user || user.role !== 'admin') {
            window.location.href = this.resolvePath('admin_login.html');
        }
    },

    // --- Exam Access Control ---

    checkExamAccess: async function (examLevel) {
        const user = await this.getCurrentUser();
        if (!user) return false;

        // Check specific access field
        if (examLevel === 'B1' && user.accessB1) return true;
        if (examLevel === 'B2' && user.accessB2) return true;
        if (user.role === 'admin') return true;

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
            window.location.href = this.resolvePath('login.html');
            return;
        }

        // Check Access
        const hasAccess = await this.checkExamAccess(examLevel);
        if (!hasAccess) {
            alert('Sie haben keinen Zugriff auf diese Prüfung. Bitte kontaktieren Sie den Administrator.');
            return;
        }

        // If user has access, redirect to the exam page ONLY if not already there
        const examPage = examLevel === 'B1' ? 'index1.html' : 'index2.html';
        const currentPage = window.location.pathname.split('/').pop();

        if (currentPage !== examPage && !window.location.pathname.includes('/quiz/')) {
            window.location.href = this.resolvePath(examPage);
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
        };

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
            const { data, error } = await supabaseClient
                .from('settings')
                .select('value')
                .eq('key', 'maintenance_mode')
                .single();

            if (error || !data) return false;
            return data.value === 'true'; // Stored as string or boolean
        } catch (e) {
            return false;
        }
    },

    toggleMaintenance: async function (enabled) {
        try {
            const { error } = await supabaseClient
                .from('settings')
                .upsert({ key: 'maintenance_mode', value: enabled.toString() });

            if (error) throw error;
            return { success: true };
        } catch (e) {
            console.error('Error toggling maintenance:', e);
            return { success: false, message: 'Server error' };
        }
    }
};

// Initialize on load
Auth.init();

// Theme (Keep existing theme logic)
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
