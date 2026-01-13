<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

$config = ComponentHelper::getParams('com_odoocontacts');
$odooUrl = $config->get('odoo_url', 'https://grupoimpre.odoo.com/xmlrpc/2/object');
$odooDb = $config->get('odoo_db') ?: $config->get('odoo_database', 'grupoimpre');
$odooUserId = $config->get('odoo_user_id') ?: $config->get('odoo_username', '2');
$odooApiKey = $config->get('odoo_api_key', '');
// Use default API key if config is empty (password fields may appear empty even if set)
if (empty($odooApiKey)) {
    $odooApiKey = '2386bb5ae66c7fd9022feaf82148680c4cf4ce3b';
}

$testResult = $this->testResult;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST'); ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_CONFIG'); ?></h4>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th width="30%"><?php echo Text::_('COM_ODOOCONTACTS_CONFIG_ODOO_URL_LABEL'); ?></th>
                                <td><?php echo htmlspecialchars($odooUrl ?: Text::_('JNONE')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo Text::_('COM_ODOOCONTACTS_CONFIG_ODOO_DB_LABEL'); ?></th>
                                <td><?php echo htmlspecialchars($odooDb ?: Text::_('JNONE')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo Text::_('COM_ODOOCONTACTS_CONFIG_ODOO_USER_ID_LABEL'); ?></th>
                                <td><?php echo htmlspecialchars($odooUserId ?: Text::_('JNONE')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo Text::_('COM_ODOOCONTACTS_CONFIG_ODOO_API_KEY_LABEL'); ?></th>
                                <td><?php echo $odooApiKey ? '••••••••' . substr($odooApiKey, -4) : Text::_('JNONE'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <h4><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_RESULT'); ?></h4>
                    
                    <?php if ($testResult): ?>
                        <?php if ($testResult['success']): ?>
                            <div class="alert alert-success">
                                <h5 class="alert-heading">
                                    <i class="fas fa-check-circle"></i> 
                                    <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_SUCCESS'); ?>
                                </h5>
                                <p class="mb-0"><?php echo htmlspecialchars($testResult['message']); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h5 class="alert-heading">
                                    <i class="fas fa-times-circle"></i> 
                                    <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_FAILED'); ?>
                                </h5>
                                <p class="mb-0"><strong><?php echo Text::_('COM_ODOOCONTACTS_ERROR'); ?>:</strong> <?php echo htmlspecialchars($testResult['message']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_NO_RESULT'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=connectiontest'); ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> 
                        <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_RETRY'); ?>
                    </a>
                    <a href="<?php echo Route::_('index.php?option=com_config&view=component&component=com_odoocontacts'); ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-cog"></i> 
                        <?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_EDIT_CONFIG'); ?>
                    </a>
                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts'); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> 
                        <?php echo Text::_('JTOOLBAR_BACK'); ?>
                    </a>
                </div>

                <div class="mt-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_TROUBLESHOOTING'); ?></h5>
                            <ul>
                                <li><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_TIP_1'); ?></li>
                                <li><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_TIP_2'); ?></li>
                                <li><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_TIP_3'); ?></li>
                                <li><?php echo Text::_('COM_ODOOCONTACTS_CONNECTION_TEST_TIP_4'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

