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

<div class="odoo-contacts-component">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Mis Clientes</h1>
            <div class="user-info">
                <small class="text-muted">
                    Agente de Ventas: <?php echo safeEscape($user->name); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Main Actions Ribbon -->
    <div class="contacts-ribbon">
        <div class="row">
            <div class="col-md-8">
                <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=contacts'); ?>" method="post" name="adminForm" id="adminForm">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="filter_search" id="filter_search" 
                               value="" 
                               class="form-control" 
                               placeholder="Buscar por nombre, email, teléfono..." />
                        <button class="btn btn-outline-secondary" type="submit">
                            Buscar
                        </button>
                    </div>
                    <input type="hidden" name="task" value="" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=0'); ?>" 
                       class="btn btn-success btn-lg">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </a>
                    <button type="button" class="btn btn-info btn-lg" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="contacts-table-container">
        <?php if (empty($this->items) || !is_array($this->items)): ?>
            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle"></i> No se Encontraron Contactos</h4>
                <p>Aún no tienes contactos. Crea tu primer contacto para comenzar.</p>
                <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit'); ?>" 
                   class="btn btn-primary">
                    Crear Tu Primer Cliente
                </a>
            </div>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%" class="text-center">ID</th>
                        <th width="30%">Nombre</th>
                        <th width="25%">Email</th>
                        <th width="20%">Teléfono</th>
                        <th width="20%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item): ?>
                        <?php if (!is_array($item)) continue; ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo (int)safeGet($item, 'id', 0); ?></span>
                            </td>
                            <td>
                                <div class="contact-name">
                                    <strong>
                                        <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo safeEscape(safeGet($item, 'name'), 'Sin nombre'); ?>
                                        </a>
                                    </strong>
                                    <?php 
                                    $type = safeGet($item, 'type');
                                    if (!empty($type) && $type !== 'contact'): 
                                    ?>
                                        <br><small class="badge bg-info"><?php echo safeEscape(ucfirst($type)); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php 
                                $vat = safeGet($item, 'vat');
                                if (!empty($vat)): 
                                ?>
                                    <br><small class="text-muted"><strong>NIT:</strong> <?php echo safeEscape($vat); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $email = safeGet($item, 'email');
                                if (!empty($email)): 
                                ?>
                                    <a href="mailto:<?php echo safeEscape($email); ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope text-primary"></i>
                                        <?php echo safeEscape($email); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $phone = safeGet($item, 'phone');
                                $mobile = safeGet($item, 'mobile');
                                if (!empty($phone)): 
                                ?>
                                    <a href="tel:<?php echo safeEscape($phone); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone text-success"></i>
                                        <?php echo safeEscape($phone); ?>
                                    </a>
                                    <?php if (!empty($mobile)): ?>
                                        <br><a href="tel:<?php echo safeEscape($mobile); ?>" class="text-decoration-none">
                                            <i class="fas fa-mobile-alt text-success"></i>
                                            <?php echo safeEscape($mobile); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php elseif (!empty($mobile)): ?>
                                    <a href="tel:<?php echo safeEscape($mobile); ?>" class="text-decoration-none">
                                        <i class="fas fa-mobile-alt text-success"></i>
                                        <?php echo safeEscape($mobile); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Edit Contact Button -->
                                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Editar Cliente">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- Delete Button -->
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            onclick="deleteContact(<?php echo (int)safeGet($item, 'id', 0); ?>, '<?php echo addslashes(safeGet($item, 'name', 'Sin nombre')); ?>')" 
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar este contacto?</p>
                <p><strong id="deleteContactName"></strong></p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="post" style="display: inline;">
                    <input type="hidden" name="task" value="contact.delete" />
                    <input type="hidden" name="id" id="deleteContactId" value="" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteContact(contactId, contactName) {
    document.getElementById('deleteContactId').value = contactId;
    document.getElementById('deleteContactName').textContent = contactName;
    
    // Initialize Bootstrap modal
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Set form action to current page
document.addEventListener('DOMContentLoaded', function() {
    var deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.action = window.location.href.split('?')[0] + '?option=com_odoocontacts&view=contacts';
    }
});
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

.contacts-ribbon {
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.contacts-ribbon .form-control {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.contacts-ribbon .input-group-text {
    background-color: white;
    border: none;
    color: #667eea;
}

.contacts-ribbon .btn {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    font-weight: 500;
}

.contacts-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.contacts-table-container table {
    margin-bottom: 0;
}

.contacts-table-container th {
    background-color: #343a40;
    color: white;
    font-weight: 600;
    border: none;
    padding: 15px 12px;
    font-size: 0.9rem;
}

.contacts-table-container td {
    padding: 15px 12px;
    vertical-align: middle;
    border-color: #dee2e6;
}

.contacts-table-container tbody tr:hover {
    background-color: #f8f9fa;
}

.contact-name {
    line-height: 1.4;
}

.contact-name a:hover {
    color: #007bff !important;
    text-decoration: underline !important;
}

.badge {
    font-size: 0.75rem;
}

.alert-info {
    text-align: center;
    padding: 40px;
}

.alert-info h4 {
    color: #0c5460;
    margin-bottom: 15px;
}
</style>