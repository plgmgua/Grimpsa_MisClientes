-- Manual Database Installation Script for com_odoocontacts
-- Run this in phpMyAdmin or your database tool
-- Table prefix: joomla_

-- IMPORTANT: Replace 'joomla_' with your actual table prefix if different

-- Step 1: Insert component into extensions table
-- Note: Joomla 5 doesn't have 'system_data' column, so we exclude it
INSERT INTO `joomla_extensions` (
    `name`,
    `type`,
    `element`,
    `folder`,
    `client_id`,
    `enabled`,
    `access`,
    `protected`,
    `manifest_cache`,
    `params`,
    `custom_data`,
    `checked_out`,
    `checked_out_time`,
    `ordering`,
    `state`
) VALUES (
    'com_odoocontacts',
    'component',
    'com_odoocontacts',
    '',
    1,
    1,
    1,
    0,
    '{"name":"COM_ODOOCONTACTS","type":"component","creationDate":"2025-01-27","author":"Grimpsa","copyright":"Copyright (C) 2025 Grimpsa. All rights reserved.","authorEmail":"admin@grimpsa.com","authorUrl":"https://grimpsa.com","version":"1.2.6-STABLE","description":"COM_ODOOCONTACTS_XML_DESCRIPTION","group":""}',
    '{"odoo_url":"https://grupoimpre.odoo.com/xmlrpc/2/object","odoo_database":"grupoimpre","odoo_user_id":"2","odoo_api_key":"","contacts_per_page":"20","enable_debug":"0","ot_destination_url":"https://grimpsa_webserver.grantsolutions.cc/index.php/orden-de-trabajo"}',
    '',
    0,
    NULL,
    0,
    0
) ON DUPLICATE KEY UPDATE
    `manifest_cache` = VALUES(`manifest_cache`),
    `params` = VALUES(`params`),
    `enabled` = 1,
    `state` = 0;

-- Step 2: Get the extension_id (we'll need it for menu and assets)
SET @extension_id = (SELECT `extension_id` FROM `joomla_extensions` WHERE `element` = 'com_odoocontacts' AND `type` = 'component' LIMIT 1);

-- Step 3: Create component menu item in administrator menu
INSERT INTO `joomla_menu` (
    `menutype`,
    `title`,
    `alias`,
    `note`,
    `path`,
    `link`,
    `type`,
    `published`,
    `parent_id`,
    `level`,
    `component_id`,
    `checked_out`,
    `checked_out_time`,
    `browserNav`,
    `access`,
    `img`,
    `template_style_id`,
    `params`,
    `lft`,
    `rgt`,
    `home`,
    `language`,
    `client_id`
) VALUES (
    'main',
    'COM_ODOOCONTACTS',
    'com-odoocontacts',
    '',
    'com-odoocontacts',
    'index.php?option=com_odoocontacts',
    'component',
    1,
    1,
    1,
    @extension_id,
    0,
    NULL,
    0,
    1,
    'class:contact',
    0,
    '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}',
    0,
    0,
    0,
    '*',
    1
) ON DUPLICATE KEY UPDATE
    `component_id` = @extension_id,
    `published` = 1;

-- Step 4: Create asset entry for the component
INSERT INTO `joomla_assets` (
    `parent_id`,
    `lft`,
    `rgt`,
    `level`,
    `name`,
    `title`,
    `rules`
) VALUES (
    1,
    0,
    0,
    1,
    'com_odoocontacts',
    'COM_ODOOCONTACTS',
    '{}'
) ON DUPLICATE KEY UPDATE
    `title` = 'COM_ODOOCONTACTS',
    `rules` = '{}';

-- Step 5: Update asset hierarchy (set proper lft/rgt values)
-- Get parent asset (root)
SET @root_id = (SELECT `id` FROM `joomla_assets` WHERE `name` = 'root' LIMIT 1);
SET @component_asset_id = (SELECT `id` FROM `joomla_assets` WHERE `name` = 'com_odoocontacts' LIMIT 1);

-- Update parent_id and level (lft/rgt will be fixed by Joomla on next access)
-- Note: Setting lft/rgt to 0 is fine - Joomla will rebuild the tree automatically
UPDATE `joomla_assets`
SET 
    `parent_id` = @root_id,
    `level` = 1
WHERE `id` = @component_asset_id AND `parent_id` != @root_id;

-- Step 6: Verify installation
SELECT 
    'Component Extension' AS 'Check',
    `extension_id` AS 'ID',
    `name` AS 'Name',
    `element` AS 'Element',
    `enabled` AS 'Enabled',
    `version` AS 'Version'
FROM `joomla_extensions`
WHERE `element` = 'com_odoocontacts' AND `type` = 'component';

SELECT 
    'Menu Item' AS 'Check',
    `id` AS 'ID',
    `title` AS 'Title',
    `alias` AS 'Alias',
    `published` AS 'Published',
    `component_id` AS 'Component ID'
FROM `joomla_menu`
WHERE `component_id` = @extension_id;

SELECT 
    'Asset' AS 'Check',
    `id` AS 'ID',
    `name` AS 'Name',
    `title` AS 'Title',
    `parent_id` AS 'Parent ID'
FROM `joomla_assets`
WHERE `name` = 'com_odoocontacts';

-- If all three queries return results, the component is installed!

