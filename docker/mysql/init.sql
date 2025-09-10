-- MySQL initialization script
-- This script runs when the MySQL container starts for the first time
-- It sets up our database and user permissions

-- Create the main database for our personal finance application
-- We're calling it 'personal_finance_db' to be descriptive
CREATE DATABASE IF NOT EXISTS personal_finance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a dedicated user for our application
-- This is more secure than using the root user
CREATE USER IF NOT EXISTS 'finance_user'@'%' IDENTIFIED BY 'secure_password_2024';

-- Grant all privileges on our database to our application user
-- This allows Laravel to create tables, insert data, etc.
GRANT ALL PRIVILEGES ON personal_finance_db.* TO 'finance_user'@'%';

-- Create a test database for running our automated tests
-- Tests should never use the main database to avoid data corruption
CREATE DATABASE IF NOT EXISTS personal_finance_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges on the test database too
GRANT ALL PRIVILEGES ON personal_finance_test.* TO 'finance_user'@'%';

-- Apply all the privilege changes
FLUSH PRIVILEGES;

-- Optional: Set some MySQL settings for better performance with financial data
-- These settings optimize MySQL for applications that need precise decimal calculations

-- Set default timezone (adjust based on your location)
SET GLOBAL time_zone = '+00:00';

-- Improve performance for applications with many small transactions
-- (which is typical for personal finance apps)
SET GLOBAL innodb_buffer_pool_size = 128M;

-- Enable strict mode to prevent data truncation issues
-- This is especially important for financial data where precision matters
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';