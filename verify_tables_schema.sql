-- Table Schema Verification Script for Joomla 5.3.4
-- Run this FIRST to check what columns actually exist in your tables
-- Table prefix: joomla_ (replace with your actual prefix)

-- IMPORTANT: Replace 'joomla_' with your actual table prefix if different

-- Check extensions table structure (using DESCRIBE - more reliable)
DESCRIBE `joomla_extensions`;

-- Check menu table structure
DESCRIBE `joomla_menu`;

-- Check assets table structure
DESCRIBE `joomla_assets`;

-- Check if component already exists
SELECT 
    'EXISTING COMPONENT CHECK' AS 'Check',
    `extension_id` AS 'ID',
    `name` AS 'Name',
    `element` AS 'Element',
    `enabled` AS 'Enabled',
    `type` AS 'Type'
FROM `joomla_extensions`
WHERE `element` = 'com_odoocontacts' AND `type` = 'component'
LIMIT 1;

-- Summary
SELECT 
    'SUMMARY' AS 'Info',
    (SELECT COUNT(*) FROM `joomla_extensions` WHERE `element` = 'com_odoocontacts' AND `type` = 'component') AS 'Component_Exists',
    (SELECT COUNT(*) FROM `joomla_menu` WHERE `link` LIKE '%com_odoocontacts%') AS 'Menu_Items',
    (SELECT COUNT(*) FROM `joomla_assets` WHERE `name` = 'com_odoocontacts') AS 'Assets';

