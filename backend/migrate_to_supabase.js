const sqlite3 = require('sqlite3').verbose();
const { Client } = require('pg');
const path = require('path');

// CONFIGURATION
const SUPABASE_DB_URL = 'postgresql://postgres:A02D7MNJ4HF@db.ecljdstmedvnfsxbiwkf.supabase.co:5432/postgres'; // REPLACE [YOUR-PASSWORD]
const SQLITE_DB_PATH = path.resolve(__dirname, 'database.sqlite');

// 1. Connect to SQLite
const sqliteDb = new sqlite3.Database(SQLITE_DB_PATH, sqlite3.OPEN_READONLY, (err) => {
    if (err) {
        console.error('Error opening SQLite DB:', err.message);
        process.exit(1);
    }
    console.log('Connected to SQLite.');
});

// 2. Connect to Supabase Postgres
const pgClient = new Client({
    connectionString: SUPABASE_DB_URL,
    ssl: { rejectUnauthorized: false }
});

async function migrate() {
    try {
        await pgClient.connect();
        console.log('Connected to Supabase Postgres.');

        // Get all users from SQLite
        sqliteDb.all("SELECT * FROM users", [], async (err, rows) => {
            if (err) throw err;

            console.log(`Found ${rows.length} users in SQLite.`);

            for (const user of rows) {
                console.log(`Migrating: ${user.email}...`);

                try {
                    // Insert into public.users
                    // Note: We are NOT migrating passwords because they are bcrypt hashed 
                    // and we can't insert them into auth.users without Service Role Key.
                    // Users will have to Sign Up / Reset Password.
                    const query = `
                        INSERT INTO public.users (email, name, surname, role, "accessB1", "accessB2", "isActive", "deviceLimit", phone)
                        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
                        ON CONFLICT (email) DO UPDATE SET
                            name = EXCLUDED.name,
                            surname = EXCLUDED.surname,
                            "accessB1" = EXCLUDED."accessB1",
                            "accessB2" = EXCLUDED."accessB2",
                            "isActive" = EXCLUDED."isActive",
                            "deviceLimit" = EXCLUDED."deviceLimit",
                            phone = EXCLUDED.phone;
                    `;

                    const values = [
                        user.email,
                        user.name,
                        user.surname,
                        user.role,
                        user.accessB1,
                        user.accessB2,
                        user.isActive,
                        user.deviceLimit,
                        user.phone
                    ];

                    await pgClient.query(query, values);
                    console.log(`  -> Success: ${user.email}`);

                } catch (pgErr) {
                    console.error(`  -> Failed: ${user.email}`, pgErr.message);
                }
            }

            console.log('\nMigration Complete!');
            console.log('IMPORTANT: Users must Register again with the same email to set a new password.');
            pgClient.end();
            process.exit(0);
        });

    } catch (e) {
        console.error('Migration Error:', e);
        pgClient.end();
        process.exit(1);
    }
}

migrate();
