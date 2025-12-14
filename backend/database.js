const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bcrypt = require('bcrypt');

const dbPath = process.env.DATABASE_PATH ? path.resolve(process.env.DATABASE_PATH) : path.resolve(__dirname, 'database.sqlite');

const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error('Error opening database ' + dbPath + ': ' + err.message);
    } else {
        console.log('Connected to the SQLite database.');
        initDatabase();
    }
});

function initDatabase() {
    db.serialize(() => {
        db.run(`CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            surname TEXT,
            email TEXT UNIQUE,
            password TEXT,
            role TEXT DEFAULT 'user',
            accessB1 INTEGER DEFAULT 0,
            accessB2 INTEGER DEFAULT 0,
            isActive INTEGER DEFAULT 0,
            deviceLimit INTEGER DEFAULT 1,
            phone TEXT
        )`);

        // Ensure 'phone' column exists for existing databases
        db.all("PRAGMA table_info(users)", [], (err, rows) => {
            if (!err) {
                const hasPhone = rows.some(r => r.name === 'phone');
                if (!hasPhone) {
                    db.run("ALTER TABLE users ADD COLUMN phone TEXT");
                }
            }
        });

        // Create default admin if not exists
        const adminEmail = process.env.ADMIN_EMAIL || 'admin@gmail.com';
        db.get("SELECT * FROM users WHERE email = ?", [adminEmail], (err, row) => {
            if (!row) {
                const adminPassword = process.env.ADMIN_PASSWORD || 'admin123';
                const passwordHash = bcrypt.hashSync(adminPassword, 10);
                db.run(`INSERT INTO users (name, surname, email, password, role, accessB1, accessB2, isActive, deviceLimit, phone) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                    ['Admin', 'User', adminEmail, passwordHash, 'admin', 1, 1, 1, 3, null],
                    (err) => {
                        if (err) console.error(err.message);
                        else console.log('Default admin created.');
                    });
            }
        });
    });
}

module.exports = db;
