-- Nikolozi Gagua
-- C00303433
-- This file sets up the database for the blacklist manager app. I’ve added more sample companies to make testing easier and show off the app’s features.

-- Create the database
CREATE DATABASE blacklist_manager;
USE blacklist_manager;

-- This table stores manager accounts
CREATE TABLE User (
    Username VARCHAR(50) PRIMARY KEY,
    Password VARCHAR(255) NOT NULL, -- Using VARCHAR(255) for hashed passwords
    Role ENUM('manager', 'other') NOT NULL
);

-- This table holds company details
CREATE TABLE Company (
    Company_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Address VARCHAR(255),
    CreditLimit DECIMAL(10,2),
    AmountOwed DECIMAL(10,2),
    NumberOfTimesPreviouslyBlacklisted INT DEFAULT 0,
    BLflag TINYINT(1) DEFAULT 0, -- 1 if blacklisted, 0 if not
    DeleteFlag TINYINT(1) DEFAULT 0 -- 1 if deleted, 0 if active
);

-- This table tracks blacklist records
CREATE TABLE Blacklist (
    BL_ID INT AUTO_INCREMENT PRIMARY KEY,
    Company_ID INT,
    DateBlacklisted DATE,
    AmountOwed DECIMAL(10,2),
    FOREIGN KEY (Company_ID) REFERENCES Company(Company_ID)
);

-- This table logs actions like adding or removing from blacklist
CREATE TABLE AuditLog (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    Action VARCHAR(100) NOT NULL,
    Company_ID INT,
    PerformedBy VARCHAR(50),
    PerformedAt DATETIME,
    FOREIGN KEY (Company_ID) REFERENCES Company(Company_ID)
);

-- Add some indexes to make queries faster
CREATE INDEX idx_blflag ON Company(BLflag);
CREATE INDEX idx_deleteflag ON Company(DeleteFlag);
CREATE INDEX idx_dateblacklisted ON Blacklist(DateBlacklisted);

-- Sample data for testing
-- Add a test user (password is hashed, generate your own with password_hash())
INSERT INTO User (Username, Password, Role) 
VALUES ('test_manager', '$2y$10$examplehash1234567890abcdef', 'manager');

-- Add 5 sample companies to play with
INSERT INTO Company (Name, Address, CreditLimit, AmountOwed, BLflag, DeleteFlag, NumberOfTimesPreviouslyBlacklisted) 
VALUES 
    ('Apple', '123  St, Dublin', 15000.00, 8000.00, 0, 0, 0), -- Not blacklisted
    ('Amazon', '456 Ave, Cork', 20000.00, 12000.00, 1, 0, 1), -- Blacklisted
    ('Ebay', '789  Rd, Galway', 10000.00, 3000.00, 0, 0, 0), -- Not blacklisted
    ('Dell', '321 Ln, Limerick', 25000.00, 18000.00, 1, 0, 2), -- Blacklisted
    ('Facebook', '654  St, Belfast', 12000.00, 5000.00, 0, 0, 0); -- Not blacklisted

-- Add blacklist records for the blacklisted companies
INSERT INTO Blacklist (Company_ID, DateBlacklisted, AmountOwed)
VALUES 
    (2, '2025-03-01', 12000.00),    
    (4, '2025-02-15', 18000.00),   
    (4, '2024-11-10', 15000.00); -- Previous blacklist record for Dell