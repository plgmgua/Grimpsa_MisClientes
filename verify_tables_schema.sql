-- Table Schema Verification Script for Joomla 5
-- Run this FIRST to check what columns actually exist in your tables
-- Table prefix: joomla_ (replace with your actual prefix)

-- IMPORTANT: Replace 'joomla_' with your actual table prefix if different

-- Check extensions table structure
SELECT 
    'EXTENSIONS TABLE COLUMNS' AS 'Table Check',
    COLUMN_NAME AS 'Column Name',
    DATA_TYPE AS 'Data Type',
    IS_NULLABLE AS 'Nullable'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'joomla_extensions'
ORDER BY ORDINAL_POSITION;

-- Check menu table structure
SELECT 
    'MENU TABLE COLUMNS' AS 'Table Check',
    COLUMN_NAME AS 'Column Name',
    DATA_TYPE AS 'Data Type',
    IS_NULLABLE AS 'Nullable'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'joomla_menu'
ORDER BY ORDINAL_POSITION;

-- Check assets table structure
SELECT 
    'ASSETS TABLE COLUMNS' AS 'Table Check',
    COLUMN_NAME AS 'Column Name',
    DATA_TYPE AS 'Data Type',
    IS_NULLABLE AS 'Nullable'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'joomla_assets'
ORDER BY ORDINAL_POSITION;

-- Check if component already exists
SELECT 
    'EXISTING COMPONENT CHECK' AS 'Check',
    `extension_id` AS 'ID',
    `name` AS 'Name',
    `element` AS 'Element',
    `enabled` AS 'Enabled',
    `type` AS 'Type'
FROM `joomla_extensions`
WHERE `element` = 'com_odoocontacts' AND `type` = 'component';

-- Summary
SELECT 
    'SUMMARY' AS 'Info',
    (SELECT COUNT(*) FROM `joomla_extensions` WHERE `element` = 'com_odoocontacts' AND `type` = 'component') AS 'Component Exists',
    (SELECT COUNT(*) FROM `joomla_menu` WHERE `link` LIKE '%com_odoocontacts%') AS 'Menu Items',
    (SELECT COUNT(*) FROM `joomla_assets` WHERE `name` = 'com_odoocontacts') AS 'Assets';

