const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcrypt');
const path = require('path');
const dbPath = path.resolve(__dirname, 'database.sqlite');
const db = new sqlite3.Database(dbPath);

const newEmail = 'selmaniabde77@gmail.com';
const newPassword = 'Selmani@!1';
const hashedPassword = bcrypt.hashSync(newPassword, 10);

db.get("SELECT id FROM users WHERE email = ?", [newEmail], (err, row) => {
    if (err) {
        console.error("Error checking email:", err.message);
        db.close();
        process.exit(1);
        return;
    }
    if (row) {
        db.run("UPDATE users SET role='admin', isActive=1, accessB1=1, accessB2=1, deviceLimit=3, password=? WHERE email = ?",
            [hashedPassword, newEmail],
            function (err2) {
                if (err2) {
                    console.error("Error promoting user to admin:", err2.message);
                    process.exitCode = 1;
                } else {
                    console.log(`Existing user promoted to admin. Email: ${newEmail}`);
                }
                db.close();
            });
    } else {
        db.run("UPDATE users SET email = ?, password = ? WHERE role = 'admin'",
            [newEmail, hashedPassword],
            function (err3) {
                if (err3) {
                    console.error("Error updating admin credentials:", err3.message);
                    process.exitCode = 1;
                } else {
                    console.log(`Admin credentials updated. Email: ${newEmail}`);
                }
                db.close();
            });
    }
});
