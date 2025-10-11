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
use Joomla\CMS\Factory;

HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('behavior.formvalidator');

use Joomla\CMS\Session\Session;

// Get the application input object
$app = Factory::getApplication();
$input = $app->input;

// Safe function to escape strings
function safeEscape($value, $default = '') {
    if (is_string($value) && !empty($value)) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    return $default;
}

// Safe function to get object property
function safeGetProperty($object, $property, $default = '') {
    if (is_object($object) && property_exists($object, $property)) {
        return safeEscape($object->$property, $default);
    } elseif (is_array($object) && isset($object[$property])) {
        return safeEscape($object[$property], $default);
    }
    return $default;
}

$isNew = (!isset($this->item->id) || (int)$this->item->id === 0);
$user = Factory::getUser();
?>

<div class="odoo-contacts-component">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>
                    <?php if ($isNew): ?>
                        <?php echo Text::_('COM_ODOOCONTACTS_SUPPLIER_NEW'); ?>
                    <?php else: ?>
                        <?php echo safeGetProperty($this->item, 'name', Text::_('COM_ODOOCONTACTS_SUPPLIER')); ?>
                    <?php endif; ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=suppliers'); ?>">
                                <?php echo Text::_('COM_ODOOCONTACTS_SUPPLIERS'); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo $isNew ? Text::_('COM_ODOOCONTACTS_SUPPLIER_NEW') : safeGetProperty($this->item, 'name', Text::_('COM_ODOOCONTACTS_SUPPLIER')); ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <?php if (!$isNew): ?>
            <div class="contact-actions">
                <button type="button" id="editModeBtn" class="btn btn-primary" onclick="toggleEditMode('toggle')" style="display: inline-block;">
                    <i class="fas fa-edit"></i> <?php echo Text::_('COM_ODOOCONTACTS_EDIT'); ?>
                </button>
                <button type="button" id="viewModeBtn" class="btn btn-secondary" onclick="toggleEditMode('toggle')" style="display: none;">
                    <i class="fas fa-eye"></i> <?php echo Text::_('COM_ODOOCONTACTS_VIEW'); ?>
                </button>
                <button type="button" class="btn btn-danger ms-2" onclick="deleteSupplier(<?php echo (int)($this->item->id ?? 0); ?>, '<?php echo addslashes(safeGetProperty($this->item, 'name', Text::_('COM_ODOOCONTACTS_SUPPLIER'))); ?>')">
                    <i class="fas fa-trash"></i> <?php echo Text::_('COM_ODOOCONTACTS_DELETE'); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=supplier&layout=edit&id=' . (int) ($this->item->id ?? 0)); ?>" 
          method="post" name="adminForm" id="adminForm" class="form-validate">
        
        <div class="contact-form-container">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo Text::_('COM_ODOOCONTACTS_BASIC_INFO'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="jform_name" class="form-label">
                                            <?php echo Text::_('COM_ODOOCONTACTS_NAME'); ?> *
                                        </label>
                                        <input type="text" name="jform[name]" id="jform_name" 
                                               value="<?php echo safeGetProperty($this->item, 'name'); ?>" 
                                               class="form-control required" required
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="jform_ref" class="form-label">
                                            <?php echo Text::_('COM_ODOOCONTACTS_REFERENCE'); ?>
                                        </label>
                                        <input type="text" name="jform[ref]" id="jform_ref" 
                                               value="<?php echo safeGetProperty($this->item, 'ref'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                        <small class="form-text text-muted">
                                            <?php echo Text::_('COM_ODOOCONTACTS_REFERENCE_HELP'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jform_vat" class="form-label">
                                            <?php echo Text::_('COM_ODOOCONTACTS_VAT'); ?>
                                        </label>
                                        <input type="text" name="jform[vat]" id="jform_vat" 
                                               value="<?php echo safeGetProperty($this->item, 'vat'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jform_payment_terms" class="form-label">
                                            <?php echo Text::_('COM_ODOOCONTACTS_PAYMENT_TERMS'); ?>
                                        </label>
                                        <input type="text" name="jform[payment_terms]" id="jform_payment_terms" 
                                               value="<?php echo safeGetProperty($this->item, 'payment_terms'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                        <small class="form-text text-muted">
                                            <?php echo Text::_('COM_ODOOCONTACTS_PAYMENT_TERMS_HELP'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo Text::_('COM_ODOOCONTACTS_CONTACT_INFO'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jform_email" class="form-label">
                                            <i class="fas fa-envelope"></i> <?php echo Text::_('COM_ODOOCONTACTS_EMAIL'); ?>
                                        </label>
                                        <input type="email" name="jform[email]" id="jform_email" 
                                               value="<?php echo safeGetProperty($this->item, 'email'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jform_phone" class="form-label">
                                            <i class="fas fa-phone"></i> <?php echo Text::_('COM_ODOOCONTACTS_PHONE'); ?>
                                        </label>
                                        <input type="tel" name="jform[phone]" id="jform_phone" 
                                               value="<?php echo safeGetProperty($this->item, 'phone'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jform_mobile" class="form-label">
                                            <i class="fas fa-mobile-alt"></i> <?php echo Text::_('COM_ODOOCONTACTS_MOBILE'); ?>
                                        </label>
                                        <input type="tel" name="jform[mobile]" id="jform_mobile" 
                                               value="<?php echo safeGetProperty($this->item, 'mobile'); ?>" 
                                               class="form-control" 
                                               <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo Text::_('COM_ODOOCONTACTS_ADDRESS'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="jform_street" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo Text::_('COM_ODOOCONTACTS_STREET'); ?>
                                </label>
                                <input type="text" name="jform[street]" id="jform_street" 
                                       value="<?php echo safeGetProperty($this->item, 'street'); ?>" 
                                       class="form-control" 
                                       <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                            </div>

                            <div class="mb-3">
                                <label for="jform_city" class="form-label">
                                    <i class="fas fa-city"></i> <?php echo Text::_('COM_ODOOCONTACTS_CITY'); ?>
                                </label>
                                <input type="text" name="jform[city]" id="jform_city" 
                                       value="<?php echo safeGetProperty($this->item, 'city'); ?>" 
                                       class="form-control" 
                                       <?php echo (!$isNew) ? 'readonly' : ''; ?> />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Sidebar -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo Text::_('COM_ODOOCONTACTS_ACTIONS'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="formActions" style="display: none;">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success" onclick="Joomla.submitbutton('supplier.save')">
                                        <i class="fas fa-save"></i> <?php echo Text::_('COM_ODOOCONTACTS_SAVE'); ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="Joomla.submitbutton('supplier.cancel')">
                                        <i class="fas fa-times"></i> <?php echo Text::_('COM_ODOOCONTACTS_CANCEL'); ?>
                                    </button>
                                </div>
                            </div>
                            <?php if (!$isNew): ?>
                            <div class="supplier-info mt-3">
                                <small class="text-muted">
                                    <strong><?php echo Text::_('COM_ODOOCONTACTS_SUPPLIER_ID'); ?>:</strong> <?php echo (int)($this->item->id ?? 0); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="id" value="<?php echo (int) ($this->item->id ?? 0); ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>

<script>
let editMode = false; // Always start in view mode

// Initialize form state when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isNew): ?>
    // For new suppliers, enable edit mode immediately
    editMode = true;
    toggleEditMode();
    <?php else: ?>
    // For existing suppliers, start in view mode
    editMode = false;
    toggleEditMode();
    <?php endif; ?>
});

// Force view mode for existing suppliers after all page loading is complete
window.addEventListener('load', function() {
    <?php if (!$isNew): ?>
    // Double-check: force view mode for existing suppliers
    setTimeout(function() {
        editMode = false;
        toggleEditMode();
    }, 100); // Small delay to ensure DOM is fully ready
    <?php endif; ?>
});

function toggleEditMode() {
    // Only toggle if called from button click, otherwise use current editMode value
    if (arguments.length > 0 && arguments[0] === 'toggle') {
        editMode = !editMode;
    }
    
    const formElements = document.querySelectorAll('#adminForm input, #adminForm textarea, #adminForm select');
    const editBtn = document.getElementById('editModeBtn');
    const viewBtn = document.getElementById('viewModeBtn');
    const formActions = document.getElementById('formActions');
    
    formElements.forEach(function(element) {
        if (editMode) {
            element.removeAttribute('readonly');
            element.removeAttribute('disabled');
        } else {
            if (element.tagName.toLowerCase() === 'select') {
                element.setAttribute('disabled', 'disabled');
            } else {
                element.setAttribute('readonly', 'readonly');
            }
        }
    });
    
    if (editMode) {
        if (editBtn) editBtn.style.display = 'none';
        if (viewBtn) viewBtn.style.display = 'inline-block';
        if (formActions) formActions.style.display = 'block';
    } else {
        if (editBtn) editBtn.style.display = 'inline-block';
        if (viewBtn) viewBtn.style.display = 'none';
        if (formActions) formActions.style.display = 'none';
    }
}

// Delete supplier function
function deleteSupplier(supplierId, supplierName) {
    if (confirm('<?php echo Text::_('COM_ODOOCONTACTS_SUPPLIER_DELETE_CONFIRM'); ?>: "' + supplierName + '"?\n\n<?php echo Text::_('COM_ODOOCONTACTS_DELETE_WARNING'); ?>')) {
        // Create and submit delete form
        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = '<?php echo Route::_("index.php?option=com_odoocontacts&view=suppliers"); ?>';
        
        // Add form fields
        const fields = {
            'task': 'supplier.delete',
            'id': supplierId,
            '<?php echo Session::getFormToken(); ?>': '1'
        };
        
        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            deleteForm.appendChild(input);
        }
        
        document.body.appendChild(deleteForm);
        deleteForm.submit();
    }
}

Joomla.submitbutton = function(task) {
    if (task == 'supplier.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
        Joomla.submitform(task, document.getElementById('adminForm'));
    } else {
        alert('<?php echo Text::_('COM_ODOOCONTACTS_FILL_REQUIRED_FIELDS'); ?>');
    }
};
</script>

<style>
.odoo-contacts-component {
    margin: 20px 0;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #dee2e6;
}

.page-header h1 {
    color: #495057;
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.contact-form-container {
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 20px;
}

.card-title {
    color: #495057;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-body {
    padding: 20px;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
}

.form-control[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.contact-actions {
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .contact-form-container .row > div {
        margin-bottom: 20px;
    }
    
    .contact-actions {
        margin-top: 15px;
        justify-content: center;
    }
}
</style>

