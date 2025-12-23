-- Seed Data for Vorbereitung B1/B2 Quiz Platform
-- Creates default admin user

-- =====================================================
-- Default Admin User
-- =====================================================
-- Email: admin@example.com
-- Password: admin123
-- IMPORTANT: Change this password immediately after first login!

INSERT INTO `users` (
  `name`,
  `surname`,
  `email`,
  `password`,
  `phone`,
  `role`,
  `is_active`,
  `access_b1`,
  `access_b2`,
  `device_limit`
) VALUES (
  'Admin',
  'User',
  'admin@example.com',
  '$2y$10$odUM5tgeUmFfMvSTFq3qs.syspf4IZ64JyOXVRkWCY6NErbd6UDMy', -- password_hash('admin123', PASSWORD_BCRYPT)
  NULL,
  'admin',
  1,
  1,
  1,
  5
);

-- =====================================================
-- Sample Test User (Pending Approval)
-- =====================================================
-- Email: testuser@example.com
-- Password: test123

INSERT INTO `users` (
  `name`,
  `surname`,
  `email`,
  `password`,
  `phone`,
  `role`,
  `is_active`,
  `access_b1`,
  `access_b2`,
  `device_limit`
) VALUES (
  'Test',
  'User',
  'testuser@example.com',
  '$2y$10$9YH2TBKpF5YT0jh5vF8KOuFjdIDH4WJKXBmZRQ3MQqBr3xYP7p5Bm', -- password_hash('test123', PASSWORD_BCRYPT)
  '+49123456789',
  'user',
  0, -- Pending approval
  0,
  0,
  1
);
