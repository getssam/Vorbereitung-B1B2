const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bcrypt = require('bcrypt');

const dbPath = path.resolve(__dirname, 'database.sqlite');

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
            deviceLimit INTEGER DEFAULT 1
        )`);

        // Create default admin if not exists
        const adminEmail = 'admin@gmail.com';
        db.get("SELECT * FROM users WHERE email = ?", [adminEmail], (err, row) => {
            if (!row) {
                const passwordHash = bcrypt.hashSync('admin123', 10);
                db.run(`INSERT INTO users (name, surname, email, password, role, accessB1, accessB2, isActive, deviceLimit) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                    ['Admin', 'User', adminEmail, passwordHash, 'admin', 1, 1, 1, 3],
                    (err) => {
                        if (err) console.error(err.message);
                        else console.log('Default admin created.');
                    });
            }
        });
    });
}

module.exports = db;
