const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('./database.sqlite');

const email = 'testuser05@test.com';

db.get("SELECT * FROM users WHERE email = ?", [email], (err, row) => {
    if (err) {
        console.error(err);
        process.exit(1);
    }
    if (row) {
        console.log(`User found: ${JSON.stringify(row)}`);
    } else {
        console.log('User not found');
    }
});
