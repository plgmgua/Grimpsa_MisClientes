-- SQL Script to Clean Up com_odoocontacts from Joomla Database
-- Run this in phpMyAdmin or your database tool
-- Table prefix: joomla_

-- Remove component entry from extensions table
DELETE FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts' 
AND `type` = 'component';

-- Remove any component menu items
DELETE FROM `joomla_menu` 
WHERE `component_id` IN (
    SELECT `extension_id` FROM `joomla_extensions` 
    WHERE `element` = 'com_odoocontacts'
);

-- Remove any component assets
DELETE FROM `joomla_assets` 
WHERE `name` LIKE 'com_odoocontacts%';

-- Remove any component modules (if any)
DELETE FROM `joomla_modules` 
WHERE `module` = 'mod_odoocontacts';

-- Remove any component plugins (if any)
DELETE FROM `joomla_extensions` 
WHERE `element` LIKE 'odoocontacts%' 
AND `type` = 'plugin';

-- Check if cleanup was successful
SELECT * FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts';

-- If the above query returns no results, the cleanup was successful

