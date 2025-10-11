<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

HTMLHelper::_('bootstrap.framework');

// Fallback CSS loading to ensure styles are applied
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('com_odoocontacts.contacts', 'media/com_odoocontacts/css/contacts.css', [], ['version' => 'auto']);

$user = Factory::getUser();

// Safe function to escape strings
function safeEscape($value, $default = '') {
    if (is_string($value) && !empty($value)) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    return $default;
}

// Safe function to get array value
function safeGet($array, $key, $default = '') {
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    }
    return $default;
}
?>

<style>
/* Backup inline styles to ensure the design is applied */
.odoo-contacts-component .contacts-ribbon {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border: 1px solid #dee2e6 !important;
    color: #495057 !important;
}
.odoo-contacts-component .contacts-table-container th {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border-bottom: 2px solid #dee2e6 !important;
}
.odoo-contacts-component .contacts-table-container td {
    padding: 10px 8px !important;
    border-color: #f1f3f4 !important;
}
.odoo-contacts-component .page-header h1 {
    color: #2c3e50 !important;
    font-size: 1.75rem !important;
}
</style>

<div class="odoo-contacts-component">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo Text::_('COM_ODOOCONTACTS_SUPPLIERS_TITLE'); ?></h1>
            <div class="user-info">
                <small class="text-muted">
                    <?php echo Text::_('COM_ODOOCONTACTS_USER'); ?>: <?php echo safeEscape($user->name); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Main Actions Ribbon -->
    <div class="contacts-ribbon">
        <div class="row align-items-center">
            <div class="col-md-7">
                <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=suppliers'); ?>" method="post" name="adminForm" id="adminForm">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="filter_search" id="filter_search" 
                               value="<?php echo htmlspecialchars($this->state->get('filter.search', '')); ?>" 
                               class="form-control" 
                               placeholder="<?php echo Text::_('COM_ODOOCONTACTS_SEARCH_SUPPLIERS'); ?>..." />
                        <button class="btn btn-outline-secondary" type="submit">
                            <?php echo Text::_('COM_ODOOCONTACTS_SEARCH'); ?>
                        </button>
                        <?php if (!empty($this->state->get('filter.search', ''))): ?>
                            <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=suppliers'); ?>" 
                               class="btn btn-outline-danger" title="<?php echo Text::_('COM_ODOOCONTACTS_CLEAR_SEARCH'); ?>">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="task" value="" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
            <div class="col-md-5 text-end">
                <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=supplier&layout=edit'); ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-plus"></i> <?php echo Text::_('COM_ODOOCONTACTS_NEW_SUPPLIER'); ?>
                </a>
                <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contacts'); ?>" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-users"></i> <?php echo Text::_('COM_ODOOCONTACTS_CONTACTS'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="contacts-table-container">
        <?php if (empty($this->items)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i>
                <?php echo Text::_('COM_ODOOCONTACTS_NO_SUPPLIERS_FOUND'); ?>
            </div>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php echo Text::_('COM_ODOOCONTACTS_ID'); ?></th>
                        <th style="width: 20%;"><?php echo Text::_('COM_ODOOCONTACTS_NAME'); ?></th>
                        <th style="width: 12%;"><?php echo Text::_('COM_ODOOCONTACTS_REFERENCE'); ?></th>
                        <th style="width: 18%;"><?php echo Text::_('COM_ODOOCONTACTS_EMAIL'); ?></th>
                        <th style="width: 12%;"><?php echo Text::_('COM_ODOOCONTACTS_PHONE'); ?></th>
                        <th style="width: 10%;"><?php echo Text::_('COM_ODOOCONTACTS_VAT'); ?></th>
                        <th style="width: 15%;"><?php echo Text::_('COM_ODOOCONTACTS_PAYMENT_TERMS'); ?></th>
                        <th style="width: 8%; text-align: center;"><?php echo Text::_('COM_ODOOCONTACTS_ACTIONS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item): ?>
                        <tr>
                            <td><?php echo safeEscape(safeGet($item, 'id', '0')); ?></td>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=supplier&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>">
                                    <strong><?php echo safeEscape(safeGet($item, 'name', 'Sin nombre')); ?></strong>
                                </a>
                            </td>
                            <td>
                                <?php 
                                $ref = safeGet($item, 'ref', '');
                                if (!empty($ref)): ?>
                                    <span class="badge bg-info"><?php echo safeEscape($ref); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $email = safeGet($item, 'email', '');
                                if (!empty($email)): ?>
                                    <a href="mailto:<?php echo safeEscape($email); ?>">
                                        <i class="fas fa-envelope text-primary"></i>
                                        <?php echo safeEscape($email); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="phone-numbers">
                                    <?php 
                                    $phone = safeGet($item, 'phone', '');
                                    $mobile = safeGet($item, 'mobile', '');
                                    
                                    if (!empty($phone)): ?>
                                        <a href="tel:<?php echo safeEscape($phone); ?>">
                                            <i class="fas fa-phone text-success"></i>
                                            <?php echo safeEscape($phone); ?>
                                        </a>
                                    <?php elseif (!empty($mobile)): ?>
                                        <a href="tel:<?php echo safeEscape($mobile); ?>">
                                            <i class="fas fa-mobile-alt text-success"></i>
                                            <?php echo safeEscape($mobile); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo safeEscape(safeGet($item, 'vat', '-')); ?></td>
                            <td>
                                <?php 
                                $paymentTerms = safeGet($item, 'payment_terms', '');
                                if (!empty($paymentTerms)): ?>
                                    <span class="badge bg-secondary"><?php echo safeEscape($paymentTerms); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=supplier&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>" 
                                       class="btn btn-outline-primary" 
                                       title="<?php echo Text::_('COM_ODOOCONTACTS_EDIT'); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination Controls -->
            <?php if ($this->pagination): ?>
                <div class="pagination-container mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="pagination-info">
                                <small class="text-muted">
                                    <?php 
                                    $total = count($this->items);
                                    $limit = $this->state->get('list.limit', 15);
                                    $start = $this->state->get('list.start', 0);
                                    $showing = $start + 1;
                                    $to = min($start + $limit, $start + $total);
                                    echo Text::sprintf('COM_ODOOCONTACTS_SHOWING_ITEMS', $showing, $to);
                                    ?>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="pagination-controls">
                                <?php echo $this->pagination->getPagesLinks(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Items Per Page Selector -->
            <div class="items-per-page-container mt-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=suppliers'); ?>" method="post" name="limitForm" id="limitForm">
                            <div class="input-group" style="max-width: 200px;">
                                <label class="input-group-text" for="limit"><?php echo Text::_('COM_ODOOCONTACTS_SHOW'); ?>:</label>
                                <select name="limit" id="limit" class="form-select" onchange="this.form.submit()">
                                    <option value="10" <?php echo ($this->state->get('list.limit', 15) == 10) ? 'selected' : ''; ?>>10</option>
                                    <option value="15" <?php echo ($this->state->get('list.limit', 15) == 15) ? 'selected' : ''; ?>>15</option>
                                    <option value="20" <?php echo ($this->state->get('list.limit', 15) == 20) ? 'selected' : ''; ?>>20</option>
                                    <option value="25" <?php echo ($this->state->get('list.limit', 15) == 25) ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo ($this->state->get('list.limit', 15) == 50) ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo ($this->state->get('list.limit', 15) == 100) ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

