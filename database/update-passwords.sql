-- ====================================================================
-- Update Passwords for Test Users
-- Password for ALL accounts: "password123"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ====================================================================

USE journeyhub;

-- Update admin user
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'admin@journeyhub.test';

-- Update Rahul Patel
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'rahul.patel@journeyhub.test';

-- Update all other users
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE role = 'user';

-- Verify the update
SELECT id, name, email, role, LEFT(password, 30) as password_preview 
FROM users 
WHERE email IN ('admin@journeyhub.test', 'rahul.patel@journeyhub.test')
ORDER BY id;
