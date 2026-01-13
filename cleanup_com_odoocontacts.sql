-- SQL Script to Clean Up com_odoocontacts from Joomla Database
-- Run this in phpMyAdmin or your database tool
-- Table prefix: joomla_

-- IMPORTANT: Replace 'joomla_' with your actual table prefix if different

-- Step 1: Remove component menu items first (they reference extension_id)
DELETE FROM `joomla_menu` 
WHERE `component_id` IN (
    SELECT `extension_id` FROM `joomla_extensions` 
    WHERE `element` = 'com_odoocontacts' 
    AND `type` = 'component'
);

-- Step 2: Remove component entry from extensions table
DELETE FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts' 
AND `type` = 'component';

-- Step 3: Remove any component assets
DELETE FROM `joomla_assets` 
WHERE `name` LIKE 'com_odoocontacts%';

-- Step 4: Remove any component modules (if any)
DELETE FROM `joomla_modules` 
WHERE `module` = 'mod_odoocontacts';

-- Step 5: Remove any component plugins (if any)
DELETE FROM `joomla_extensions` 
WHERE `element` LIKE 'odoocontacts%' 
AND `type` = 'plugin';

-- Step 6: Verify cleanup was successful
-- These queries should return no results if cleanup was successful
SELECT 
    'Remaining Extensions' AS 'Check',
    `extension_id` AS 'ID',
    `name` AS 'Name',
    `element` AS 'Element',
    `type` AS 'Type'
FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts';

SELECT 
    'Remaining Menu Items' AS 'Check',
    `id` AS 'ID',
    `title` AS 'Title',
    `link` AS 'Link'
FROM `joomla_menu` 
WHERE `link` LIKE '%com_odoocontacts%';

SELECT 
    'Remaining Assets' AS 'Check',
    `id` AS 'ID',
    `name` AS 'Name'
FROM `joomla_assets` 
WHERE `name` LIKE 'com_odoocontacts%';

-- If all three queries above return no results, the cleanup was successful!
