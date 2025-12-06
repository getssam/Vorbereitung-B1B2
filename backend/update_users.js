const sqlite3 = require('sqlite3').verbose();
const db = new sqlite3.Database('./database.sqlite');

console.log('Updating all users to have full access...');

const sql = `UPDATE users SET isActive = 1, accessB1 = 1, accessB2 = 1`;

db.run(sql, [], function (err) {
    if (err) {
        console.error('Error updating users:', err.message);
        process.exit(1);
    }
    console.log(`Updated ${this.changes} users. All users now have accessB1=1, accessB2=1, isActive=1.`);
    db.close();
});
