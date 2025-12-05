const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcrypt');
const db = new sqlite3.Database('./database.sqlite');

const newPassword = 'admin123';
const hashedPassword = bcrypt.hashSync(newPassword, 10);

db.run("UPDATE users SET password = ? WHERE email = 'admin@gmail.com'", [hashedPassword], function (err) {
    if (err) {
        console.error("Error updating password:", err.message);
    } else {
        console.log(`Password for admin@gmail.com reset to: ${newPassword}`);
    }
    db.close();
});
