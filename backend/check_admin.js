const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('./database.sqlite');

db.all("SELECT * FROM users WHERE role = 'admin'", [], (err, rows) => {
    if (err) {
        throw err;
    }
    console.log("Admin Users Found:", rows.length);
    rows.forEach(row => {
        console.log(`- ${row.email} (Active: ${row.isActive})`);
    });
    db.close();
});
