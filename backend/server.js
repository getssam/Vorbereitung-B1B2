require('dotenv').config();
const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const cors = require('cors');
const path = require('path');
const bcrypt = require('bcrypt');
const db = require('./database');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
    secret: process.env.SESSION_SECRET || 'fallback-secret-key', // Use .env or fallback
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 24 * 60 * 60 * 1000 } // 24 hours
}));

// Redirect root to login
app.get('/', (req, res) => {
    res.redirect('/home.html');
});

// Maintenance Mode State (In-memory for simplicity, resets on restart)
let maintenanceMode = false;

// Authentication Middleware
const requireLogin = (req, res, next) => {
    if (req.session.user) {
        next();
    } else {
        res.status(401).json({ success: false, message: 'Unauthorized' });
    }
};

const requireAdmin = (req, res, next) => {
    if (req.session.user && req.session.user.role === 'admin') {
        next();
    } else {
        res.status(403).json({ success: false, message: 'Forbidden' });
    }
};

// Maintenance Middleware
const checkMaintenance = (req, res, next) => {
    // Allow static assets (css, js, images) and admin routes even in maintenance
    const isAsset = req.path.startsWith('/css') || req.path.startsWith('/js') || req.path.startsWith('/images');
    const isAdminRoute = req.path.startsWith('/api/admin') || req.path === '/admin.html' || req.path === '/admin_login.html' || req.path === '/api/login';

    // Allow admin user to bypass
    const isAdminUser = req.session.user && req.session.user.role === 'admin';

    if (maintenanceMode && !isAsset && !isAdminRoute && !isAdminUser) {
        // If it's an API call, return error
        if (req.path.startsWith('/api')) {
            // Exception for login/status to allow admin login
            if (req.path === '/api/login' || req.path === '/api/maintenance-status') {
                return next();
            }
            return res.status(503).json({ success: false, message: 'Maintenance Mode' });
        }
        // If it's a page load, redirect to maintenance page
        if (req.path !== '/maintenance.html') {
            return res.redirect('/maintenance.html');
        }
    }
    next();
};

app.use(checkMaintenance);

app.get('/quiz/:file', (req, res, next) => {
    const file = req.params.file;
    if (!file.endsWith('.html')) return next();
    const fullPath = path.join(__dirname, '../quiz', file);
    fs.readFile(fullPath, 'utf8', (err, html) => {
        if (err) return next();
        const inject = `
<script>(function(){try{var t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
<style>
:root[data-theme="dark"] body{background-color:#0B1220;color:#CBD5E1;background-image:none}
:root[data-theme="dark"] .main-header{background-color:#1F2937;color:#F8FAFC}
:root[data-theme="dark"] .header{background-color:#1F2937;color:#F8FAFC}
:root[data-theme="dark"] .quiz-scroll-container,:root[data-theme="dark"] .right-column-content{background-color:#0F172A;color:#CBD5E1;box-shadow:none}
:root[data-theme="dark"] .question-box{background-color:#0F172A;border-color:#334155}
:root[data-theme="dark"] .question-card{background-color:#0F172A;border-color:#334155;color:#CBD5E1}
:root[data-theme="dark"] .question-text{color:#94A3B8}
:root[data-theme="dark"] .drop-target{background-color:#0F172A;border-color:#334155}
:root[data-theme="dark"] .draggable{background-color:#1E293B;color:#CBD5E1}
:root[data-theme="dark"] .quiz-button,:root[data-theme="dark"] .quiz-button-link{background-color:#1E293B !important;color:#F8FAFC !important;border-color:rgba(148,163,184,0.2)}
:root[data-theme="dark"] #result{background-color:#0F172A;color:#CBD5E1}
</style>`;
        const output = html.includes('</head>') ? html.replace('</head>', inject + '</head>') : inject + html;
        res.type('html').send(output);
    });
});

// Serve static files from the parent directory (frontend)
app.use(express.static(path.join(__dirname, '../')));

// --- API Routes ---

// Register (pending access)
app.post('/api/register', (req, res) => {
    const { name, surname, email, password, phone } = req.body;
    const passwordHash = bcrypt.hashSync(password, 10);

    const sql = `INSERT INTO users (name, surname, email, password, role, accessB1, accessB2, isActive, deviceLimit, phone) 
                 VALUES (?, ?, ?, ?, 'user', 0, 0, 0, 1, ?)`;

    db.run(sql, [name, surname, email, passwordHash, phone || null], function (err) {
        if (err) {
            console.error('Registration Error:', err.message);
            return res.json({ success: false, message: 'E-Mail wird bereits verwendet.' });
        }
        res.json({ success: true, message: 'Registrierung eingegangen. Ein Administrator wird Ihren Zugang freigeben.' });
    });
});

// Login
app.post('/api/login', (req, res) => {
    const { email, password } = req.body;

    db.get("SELECT * FROM users WHERE email = ?", [email], (err, user) => {
        if (err || !user) {
            return res.json({ success: false, message: 'Ungültige E-Mail oder Passwort.' });
        }

        if (!bcrypt.compareSync(password, user.password)) {
            return res.json({ success: false, message: 'Ungültige E-Mail oder Passwort.' });
        }

        if (!user.isActive) {
            return res.json({ success: false, message: 'Ihr Konto ist noch nicht aktiviert. Bitte warten Sie auf die Freigabe durch den Administrator.' });
        }

        // Create session
        const sessionUser = {
            id: user.id,
            name: user.name,
            surname: user.surname,
            email: user.email,
            role: user.role,
            accessB1: !!user.accessB1,
            accessB2: !!user.accessB2,
            deviceLimit: user.deviceLimit
        };

        req.session.user = sessionUser;
        res.json({ success: true, user: sessionUser });
    });
});

// Logout
app.post('/api/logout', (req, res) => {
    req.session.destroy();
    res.json({ success: true });
});

// Get Current User
app.get('/api/me', (req, res) => {
    if (req.session.user) {
        // Refresh user data from DB to get latest access rights
        db.get("SELECT * FROM users WHERE id = ?", [req.session.user.id], (err, user) => {
            if (user) {
                const sessionUser = {
                    id: user.id,
                    name: user.name,
                    surname: user.surname,
                    email: user.email,
                    role: user.role,
                    accessB1: !!user.accessB1,
                    accessB2: !!user.accessB2,
                    deviceLimit: user.deviceLimit
                };
                req.session.user = sessionUser; // Update session
                res.json({ success: true, user: sessionUser });
            } else {
                res.json({ success: false });
            }
        });
    } else {
        res.json({ success: false });
    }
});

// Ping (Keep Alive)
app.get('/ping', (req, res) => {
    if (req.session.user) {
        req.session.touch(); // Extend session
        res.sendStatus(200);
    } else {
        res.sendStatus(401);
    }
});

// --- Admin Routes ---

// Get All Users
app.get('/api/users', requireAdmin, (req, res) => {
    db.all("SELECT id, name, surname, email, role, accessB1, accessB2, isActive, deviceLimit, phone FROM users", [], (err, rows) => {
        if (err) {
            return res.status(500).json({ success: false, message: err.message });
        }
        // Convert 1/0 to boolean for frontend consistency
        const users = rows.map(u => ({
            ...u,
            accessB1: !!u.accessB1,
            accessB2: !!u.accessB2,
            isActive: !!u.isActive
        }));
        res.json(users);
    });
});

// Add User (Admin)
app.post('/api/users', requireAdmin, (req, res) => {
    const { name, surname, email, password, deviceLimit } = req.body;
    const passwordHash = bcrypt.hashSync(password, 10);

    // Admin created users are active by default
    const sql = `INSERT INTO users (name, surname, email, password, role, accessB1, accessB2, isActive, deviceLimit) 
                 VALUES (?, ?, ?, ?, 'user', 0, 0, 1, ?)`;

    db.run(sql, [name, surname, email, passwordHash, deviceLimit || 1], function (err) {
        if (err) {
            return res.json({ success: false, message: 'E-Mail wird bereits verwendet.' });
        }
        res.json({ success: true });
    });
});

// Update User Status/Access
app.put('/api/users/:email', requireAdmin, (req, res) => {
    const email = req.params.email;
    const updates = req.body;

    // Build dynamic query
    const fields = [];
    const values = [];

    if (updates.isActive !== undefined) {
        fields.push("isActive = ?");
        values.push(updates.isActive ? 1 : 0);
    }
    if (updates.accessB1 !== undefined) {
        fields.push("accessB1 = ?");
        values.push(updates.accessB1 ? 1 : 0);
    }
    if (updates.accessB2 !== undefined) {
        fields.push("accessB2 = ?");
        values.push(updates.accessB2 ? 1 : 0);
    }
    if (updates.deviceLimit !== undefined) {
        fields.push("deviceLimit = ?");
        values.push(updates.deviceLimit);
    }

    if (fields.length === 0) return res.json({ success: true });

    values.push(email);
    const sql = `UPDATE users SET ${fields.join(', ')} WHERE email = ?`;

    db.run(sql, values, function (err) {
        if (err) return res.json({ success: false, message: err.message });
        res.json({ success: true });
    });
});

// Delete User
app.delete('/api/users/:email', requireAdmin, (req, res) => {
    const email = req.params.email;

    // Check if trying to delete self or last admin
    if (email === req.session.user.email) {
        return res.json({ success: false, message: 'Cannot delete yourself.' });
    }

    db.run("DELETE FROM users WHERE email = ?", [email], function (err) {
        if (err) return res.json({ success: false, message: err.message });
        res.json({ success: true });
    });
});

// --- Maintenance Routes ---

app.get('/api/maintenance-status', (req, res) => {
    res.json({ maintenance: maintenanceMode });
});

app.post('/api/maintenance', requireAdmin, (req, res) => {
    const { enabled } = req.body;
    maintenanceMode = !!enabled;
    console.log(`Maintenance mode set to: ${maintenanceMode}`);
    res.json({ success: true, maintenance: maintenanceMode });
});

// Start Server
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
