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
/* Backup inline styles to ensure the new design is applied */
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
        <div class="row align-items-center">
            <div class="col-md-7">
                <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=contacts'); ?>" method="post" name="adminForm" id="adminForm">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="filter_search" id="filter_search" 
                               value="<?php echo htmlspecialchars($this->state->get('filter.search', '')); ?>" 
                               class="form-control" 
                               placeholder="Buscar clientes..." />
                        <button class="btn btn-outline-secondary" type="submit">
                            Buscar
                        </button>
                        <?php if (!empty($this->state->get('filter.search', ''))): ?>
                            <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contacts'); ?>" 
                               class="btn btn-outline-danger" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="task" value="" />
                    <input type="hidden" name="limitstart" value="0" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
            <div class="col-md-5 text-end">
                <div class="btn-group" role="group">
                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=0'); ?>" 
                       class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </a>
                    <button type="button" class="btn btn-info" onclick="window.location.reload()">
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
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">ID</th>
                        <th width="35%">Cliente</th>
                        <th width="25%">Contacto</th>
                        <th width="20%">Teléfono</th>
                        <th width="16%" class="text-center">Acciones</th>
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
                                        <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>">
                                            <?php echo safeEscape(safeGet($item, 'name'), 'Sin nombre'); ?>
                                        </a>
                                    </strong>
                                    <?php 
                                    $type = safeGet($item, 'type');
                                    if (!empty($type) && $type !== 'contact'): 
                                    ?>
                                        <br><small class="badge bg-info"><?php echo safeEscape(ucfirst($type)); ?></small>
                                    <?php endif; ?>
                                    <?php 
                                    $vat = safeGet($item, 'vat');
                                    if (!empty($vat)): 
                                    ?>
                                        <br><small class="text-muted">NIT: <?php echo safeEscape($vat); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php 
                                    $email = safeGet($item, 'email');
                                    if (!empty($email)): 
                                    ?>
                                        <a href="mailto:<?php echo safeEscape($email); ?>">
                                            <i class="fas fa-envelope text-primary"></i>
                                            <?php echo safeEscape($email); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php 
                                    $phone = safeGet($item, 'phone');
                                    $mobile = safeGet($item, 'mobile');
                                    if (!empty($phone)): 
                                    ?>
                                        <a href="tel:<?php echo safeEscape($phone); ?>">
                                            <i class="fas fa-phone text-success"></i>
                                            <?php echo safeEscape($phone); ?>
                                        </a>
                                        <?php if (!empty($mobile)): ?>
                                            <br><a href="tel:<?php echo safeEscape($mobile); ?>">
                                                <i class="fas fa-mobile-alt text-success"></i>
                                                <?php echo safeEscape($mobile); ?>
                                            </a>
                                        <?php endif; ?>
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
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-warning" 
                                            onclick="openCotizacionWindow(<?php echo (int)safeGet($item, 'id', 0); ?>, '<?php echo addslashes(safeGet($item, 'name', 'Sin nombre')); ?>', '<?php echo addslashes(safeGet($item, 'vat', '')); ?>')" 
                                            title="Cotización">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-success" 
                                            onclick="openOTModal(<?php echo (int)safeGet($item, 'id', 0); ?>, '<?php echo addslashes(safeGet($item, 'name', 'Sin nombre')); ?>', '<?php echo addslashes(safeGet($item, 'vat', '')); ?>')" 
                                            title="Orden de Trabajo">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-info" 
                                            onclick="openOTEModal(<?php echo (int)safeGet($item, 'id', 0); ?>, '<?php echo addslashes(safeGet($item, 'name', 'Sin nombre')); ?>', '<?php echo addslashes(safeGet($item, 'vat', '')); ?>')" 
                                            title="Orden de Trabajo Externa">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    
                                    <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . (int)safeGet($item, 'id', 0)); ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Editar Cliente">
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
                                    echo "Mostrando {$showing} a {$to} contactos";
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
                        <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=contacts'); ?>" method="post" name="limitForm" id="limitForm">
                            <div class="input-group" style="max-width: 200px;">
                                <label class="input-group-text" for="limit">Mostrar:</label>
                                <select name="limit" id="limit" class="form-select" onchange="this.form.submit()">
                                    <option value="15" <?php echo ($this->state->get('list.limit') == 15) ? 'selected' : ''; ?>>15</option>
                                    <option value="30" <?php echo ($this->state->get('list.limit') == 30) ? 'selected' : ''; ?>>30</option>
                                    <option value="100" <?php echo ($this->state->get('list.limit') == 100) ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                            <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($this->state->get('filter.search', '')); ?>" />
                            <?php echo HTMLHelper::_('form.token'); ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- OT (Orden de Trabajo) Modal - Two Step Wizard -->
<div class="modal fade" id="otModal" tabindex="-1" aria-labelledby="otModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="otModalLabel">
                    <i class="fas fa-truck"></i> Crear Orden de Trabajo <span id="otStepIndicator">(Paso 1 de 2)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 25px;">
                    <div id="otProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                        Paso 1: Dirección de Entrega
                    </div>
                </div>
                
                <!-- Step 1: Delivery Information -->
                <div id="otStep1" style="display: block;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Seleccione la dirección de entrega y agregue instrucciones.
                    </div>
                    
                    <!-- Client Information (Read-only) -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Cliente:</strong>
                                    <p id="otClientName" class="mb-2"></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>NIT:</strong>
                                    <p id="otClientVat" class="mb-2"></p>
                                </div>
                                <div class="col-md-12">
                                    <strong>Agente de Ventas:</strong>
                                    <p id="otAgentName" class="mb-0"><?php echo htmlspecialchars($user->name); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-shipping-fast"></i> Tipo de Entrega *
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="otDeliveryType" id="otDeliveryTypeDomicilio" value="domicilio" checked>
                            <label class="btn btn-outline-primary" for="otDeliveryTypeDomicilio">
                                <i class="fas fa-truck"></i> Entrega a Domicilio
                            </label>
                            
                            <input type="radio" class="btn-check" name="otDeliveryType" id="otDeliveryTypeRecoger" value="recoger">
                            <label class="btn btn-outline-success" for="otDeliveryTypeRecoger">
                                <i class="fas fa-store"></i> Recoger en Oficina
                            </label>
                        </div>
                        <div class="form-text">
                            Seleccione si desea entrega a domicilio o recoger en nuestras instalaciones.
                        </div>
                    </div>
                    
                    <!-- Delivery Address Container (shown only for domicilio) -->
                    <div id="otDeliveryAddressContainer">
                        <!-- Delivery Address Selection -->
                        <div class="mb-3">
                            <label for="otDeliveryAddress" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Dirección de Entrega
                            </label>
                            <select id="otDeliveryAddress" class="form-select">
                                <option value="">Seleccione una dirección...</option>
                            </select>
                            <div class="form-text">
                                Seleccione una dirección existente o ingrese una nueva abajo.
                            </div>
                        </div>
                    
                    <!-- Address Preview -->
                    <div id="otAddressPreview" class="card mb-3" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-location-arrow"></i> Dirección Seleccionada</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Calle:</strong> <span id="otPreviewStreet"></span></p>
                            <p class="mb-0"><strong>Ciudad:</strong> <span id="otPreviewCity"></span></p>
                        </div>
                    </div>
                    
                    <!-- OR Divider -->
                    <div class="text-center mb-3">
                        <span class="badge bg-secondary">O</span>
                    </div>
                    
                    <!-- Manual Delivery Address Input -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Ingresar Nueva Dirección de Entrega</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="otManualAddressName" class="form-label">Nombre de Dirección *</label>
                                        <input type="text" id="otManualAddressName" class="form-control" 
                                               placeholder="Ej: Bodega Central, Oficina Principal, etc." />
                                        <div class="form-text">
                                            Ingrese un nombre descriptivo para esta dirección.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="otManualStreet" class="form-label">Dirección *</label>
                                        <input type="text" id="otManualStreet" class="form-control" 
                                               placeholder="Calle, número, zona, etc." />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="otManualCity" class="form-label">Ciudad *</label>
                                        <input type="text" id="otManualCity" class="form-control" 
                                               placeholder="Ciudad" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="otSaveAddressToOdoo" value="1">
                                <label class="form-check-label" for="otSaveAddressToOdoo">
                                    <i class="fas fa-save"></i> Agregar dirección a cliente
                                </label>
                                <div class="form-text">
                                    Marque esta opción para guardar esta dirección como hija del cliente en Odoo.
                                </div>
                            </div>
                            <div id="otSaveAddressButtonContainer" style="display: none;">
                                <button type="button" class="btn btn-success btn-sm" onclick="saveDeliveryAddressNow()">
                                    <i class="fas fa-save"></i> Guardar Dirección
                                </button>
                            </div>
                        </div>
                    </div>
                    </div><!-- End Delivery Address Container -->
                    
                    <!-- Delivery Instructions -->
                    <div class="mb-3">
                        <label for="otDeliveryInstructions" class="form-label">
                            <i class="fas fa-clipboard-list"></i> Instrucciones de Entrega
                        </label>
                        <textarea id="otDeliveryInstructions" class="form-control" rows="4" 
                                  placeholder="Ingrese instrucciones especiales para la entrega..."></textarea>
                        <div class="form-text">
                            Opcional: Agregue cualquier instrucción especial para la entrega (horario, contacto, etc.)
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Contact Selection -->
                <div id="otStep2" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Seleccione o ingrese la persona de contacto para esta orden de trabajo.
                    </div>
                    
                    <!-- Contact Selection Dropdown -->
                    <div id="otContactDropdownSection" class="mb-3">
                        <label for="otContactSelect" class="form-label">
                            <i class="fas fa-user-tie"></i> Persona de Contacto
                        </label>
                        <select id="otContactSelect" class="form-select">
                            <option value="">Seleccione un contacto...</option>
                        </select>
                        <div class="form-text">
                            Seleccione una persona de contacto existente o ingrese una nueva abajo.
                        </div>
                    </div>
                    
                    <!-- OR Divider -->
                    <div class="text-center mb-3">
                        <span class="badge bg-secondary">O</span>
                    </div>
                    
                    <!-- Manual Contact Input -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-user-plus"></i> Ingresar Nuevo Contacto</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="otManualContactName" class="form-label">Nombre *</label>
                                        <input type="text" id="otManualContactName" class="form-control" 
                                               placeholder="Nombre de la persona de contacto" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="otManualContactPhone" class="form-label">Teléfono *</label>
                                        <input type="tel" id="otManualContactPhone" class="form-control" 
                                               placeholder="Teléfono de contacto" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="otSaveContactToOdoo" value="1">
                                <label class="form-check-label" for="otSaveContactToOdoo">
                                    <i class="fas fa-save"></i> Agregar contacto a cliente
                                </label>
                                <div class="form-text">
                                    Marque esta opción para guardar este contacto como hijo del cliente en Odoo.
                                </div>
                            </div>
                            <div id="otSaveContactButtonContainer" style="display: none;">
                                <button type="button" class="btn btn-success btn-sm" onclick="saveContactNow()">
                                    <i class="fas fa-save"></i> Guardar Contacto
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Preview -->
                    <div id="otContactPreview" class="card mb-3" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-address-card"></i> Contacto Seleccionado</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Nombre:</strong> <span id="otPreviewContactName"></span></p>
                            <p class="mb-0"><strong>Teléfono:</strong> <span id="otPreviewContactPhone"></span></p>
                        </div>
                    </div>
                    
                    <!-- Summary Card -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-clipboard-check"></i> Resumen de Orden de Trabajo</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Dirección de Entrega:</strong> <span id="otSummaryAddress"></span></p>
                            <p class="mb-0"><strong>Instrucciones:</strong> <span id="otSummaryInstructions"></span></p>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden fields for form data -->
                <input type="hidden" id="otClientId" value="" />
                <input type="hidden" id="otSelectedStreet" value="" />
                <input type="hidden" id="otSelectedCity" value="" />
                <input type="hidden" id="otSelectedContactName" value="" />
                <input type="hidden" id="otSelectedContactPhone" value="" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="otBtnBack" class="btn btn-outline-secondary" onclick="goToStep1()" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Atrás
                </button>
                <button type="button" id="otBtnNext" class="btn btn-primary" onclick="goToStep2()">
                    <i class="fas fa-arrow-right"></i> Siguiente
                </button>
                <button type="button" id="otBtnSubmit" class="btn btn-success" onclick="submitOT()" style="display: none;">
                    <i class="fas fa-check"></i> Crear Orden de Trabajo
                </button>
            </div>
        </div>
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

<!-- OTE (Orden de Trabajo Externa) Modal - Three Step Wizard with Supplier Selection -->
<div class="modal fade" id="oteModal" tabindex="-1" aria-labelledby="oteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="oteModalLabel">
                    <i class="fas fa-external-link-alt"></i> Crear Orden de Trabajo Externa <span id="oteStepIndicator">(Paso 1 de 3)</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 25px;">
                    <div id="oteProgressBar" class="progress-bar bg-info" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                        Paso 1: Proveedor
                    </div>
                </div>
                
                <!-- Hidden fields to store client info -->
                <input type="hidden" id="oteClientId" value="">
                <input type="hidden" id="oteClientName" value="">
                <input type="hidden" id="oteClientVat" value="">
                
                <!-- Step 0: Supplier Selection -->
                <div id="oteStep0" style="display: block;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Seleccione el proveedor para esta orden de trabajo externa.
                    </div>
                    
                    <!-- Client Information (Read-only) -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Cliente:</strong>
                                    <p id="oteClientNameDisplay" class="mb-2"></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>NIT:</strong>
                                    <p id="oteClientVatDisplay" class="mb-2"></p>
                                </div>
                                <div class="col-md-12">
                                    <strong>Agente de Ventas:</strong>
                                    <p id="oteAgentName" class="mb-0"><?php echo htmlspecialchars($user->name); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Supplier Selection -->
                    <div class="mb-4">
                        <label for="oteSupplierSelect" class="form-label">
                            <i class="fas fa-industry"></i> Seleccionar Proveedor *
                        </label>
                        <select id="oteSupplierSelect" class="form-select" required>
                            <option value="">-- Seleccione un proveedor --</option>
                        </select>
                        <div class="form-text">
                            Seleccione el proveedor que realizará el trabajo externo.
                        </div>
                        <div id="oteSupplierLoading" class="text-center mt-2" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-info" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando proveedores...
                        </div>
                    </div>
                </div>
                
                <!-- Step 1 & 2 will reuse OT modal structure -->
                <!-- These divs will be populated dynamically -->
                <div id="oteStep1" style="display: none;">
                    <!-- Will be populated from OT Step 1 -->
                </div>
                
                <div id="oteStep2" style="display: none;">
                    <!-- Will be populated from OT Step 2 -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" id="oteBackBtn" class="btn btn-outline-secondary" onclick="oteGoBack()" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Atrás
                </button>
                <button type="button" id="oteNextBtn" class="btn btn-info" onclick="oteGoToNextStep()">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
                <button type="button" id="oteSubmitBtn" class="btn btn-success" onclick="submitOTE()" style="display: none;">
                    <i class="fas fa-check"></i> Crear Orden de Trabajo Externa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// OT Modal Variables
var otChildContacts = [];
var otDestinationUrl = '<?php echo $this->params->get('ot_destination_url', 'https://grimpsa_webserver.grantsolutions.cc/index.php/orden-de-trabajo'); ?>';
var cotizacionDestinationUrl = '<?php echo $this->params->get('cotizacion_destination_url', 'https://grimpsa_webserver.grantsolutions.cc/index.php/cotizacion'); ?>';
var oteDestinationUrl = '<?php echo $this->params->get('ote_destination_url', 'https://grimpsa_webserver.grantsolutions.cc/index.php/orden-de-trabajo-externa'); ?>';
var otDebugMode = <?php echo $this->params->get('enable_debug', 0) ? 'true' : 'false'; ?>;

// Open Cotización in new tab
function openCotizacionWindow(clientId, clientName, clientVat) {
    var agentName = '<?php echo addslashes($user->name); ?>';
    
    // Build URL with parameters
    var url = cotizacionDestinationUrl;
    url += '?client_id=' + encodeURIComponent(clientId);
    url += '&contact_name=' + encodeURIComponent(clientName);
    url += '&contact_vat=' + encodeURIComponent(clientVat);
    url += '&x_studio_agente_de_ventas=' + encodeURIComponent(agentName);
    
    // Open in new tab
    window.open(url, '_blank');
}

// OTE Modal Variables
var oteSuppliers = [];
var oteCurrentStep = 0;
var oteSelectedSupplier = null;
var oteClientData = {};

// Open OTE Modal - Step 0: Supplier Selection
function openOTEModal(clientId, clientName, clientVat) {
    if (otDebugMode) console.log('Opening OTE Modal for client:', clientId, clientName);
    
    // Store client information
    oteClientData = {
        id: clientId,
        name: clientName,
        vat: clientVat
    };
    
    document.getElementById('oteClientId').value = clientId;
    document.getElementById('oteClientName').value = clientName;
    document.getElementById('oteClientVat').value = clientVat;
    document.getElementById('oteClientNameDisplay').textContent = clientName;
    document.getElementById('oteClientVatDisplay').textContent = clientVat || 'N/A';
    
    // Reset to step 0
    oteCurrentStep = 0;
    oteSelectedSupplier = null;
    
    // Show Step 0, hide others
    document.getElementById('oteStep0').style.display = 'block';
    document.getElementById('oteStep1').style.display = 'none';
    document.getElementById('oteStep2').style.display = 'none';
    
    // Update progress bar
    oteUpdateProgress();
    
    // Reset and fetch suppliers
    document.getElementById('oteSupplierSelect').innerHTML = '<option value="">-- Seleccione un proveedor --</option>';
    loadOTESuppliers();
    
    // Show modal
    var oteModal = new bootstrap.Modal(document.getElementById('oteModal'));
    oteModal.show();
}

// Load OTE Suppliers via AJAX
function loadOTESuppliers() {
    document.getElementById('oteSupplierLoading').style.display = 'block';
    
    fetch('index.php?option=com_odoocontacts&task=supplier.getOTESuppliers&<?php echo Session::getFormToken(); ?>=1')
        .then(response => response.json())
        .then(data => {
            document.getElementById('oteSupplierLoading').style.display = 'none';
            
            if (data.success && data.suppliers) {
                oteSuppliers = data.suppliers;
                var select = document.getElementById('oteSupplierSelect');
                
                if (data.suppliers.length === 0) {
                    select.innerHTML = '<option value="">No hay proveedores OTE disponibles</option>';
                    select.disabled = true;
                } else {
                    data.suppliers.forEach(function(supplier) {
                        var option = document.createElement('option');
                        option.value = supplier.id;
                        option.textContent = supplier.name + ' (' + (supplier.ref || 'Sin ref') + ')';
                        option.dataset.supplierName = supplier.name;
                        select.appendChild(option);
                    });
                }
                
                if (otDebugMode) console.log('Loaded', data.suppliers.length, 'OTE suppliers');
            } else {
                alert('Error al cargar proveedores: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            document.getElementById('oteSupplierLoading').style.display = 'none';
            console.error('Error loading suppliers:', error);
            alert('Error al cargar proveedores. Por favor intente de nuevo.');
        });
}

// OTE Go to Next Step
function oteGoToNextStep() {
    if (oteCurrentStep === 0) {
        // Validate supplier selection
        var supplierSelect = document.getElementById('oteSupplierSelect');
        if (!supplierSelect.value) {
            alert('Por favor seleccione un proveedor');
            return;
        }
        
        // Store selected supplier
        var selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
        oteSelectedSupplier = {
            id: supplierSelect.value,
            name: selectedOption.dataset.supplierName
        };
        
        if (otDebugMode) console.log('Selected supplier:', oteSelectedSupplier);
        
        // Move to Step 1 (Delivery) - Reuse OT logic
        oteCurrentStep = 1;
        oteShowStep(1);
        
        // Initialize delivery addresses (reuse OT function)
        populateDeliveryAddresses(oteClientData.id);
        
    } else if (oteCurrentStep === 1) {
        // Validate and move to Step 2 (Contact) - Same as OT
        if (oteValidateStep1()) {
            oteCurrentStep = 2;
            oteShowStep(2);
            
            // Initialize contact persons (reuse OT function)
            populateContactPersons(oteClientData.id);
        }
    }
}

// OTE Go Back
function oteGoBack() {
    if (oteCurrentStep > 0) {
        oteCurrentStep--;
        oteShowStep(oteCurrentStep);
    }
}

// OTE Show Step
function oteShowStep(step) {
    // Hide all steps
    document.getElementById('oteStep0').style.display = 'none';
    document.getElementById('oteStep1').style.display = 'none';
    document.getElementById('oteStep2').style.display = 'none';
    
    // Show current step
    if (step === 0) {
        document.getElementById('oteStep0').style.display = 'block';
    } else if (step === 1) {
        // Copy OT Step 1 content
        var otStep1Content = document.getElementById('otStep1').cloneNode(true);
        otStep1Content.id = 'oteStep1Content';
        document.getElementById('oteStep1').innerHTML = '';
        document.getElementById('oteStep1').appendChild(otStep1Content);
        
        // Populate client information in the cloned content
        var clientNameEl = document.getElementById('oteStep1').querySelector('#otClientName');
        var clientVatEl = document.getElementById('oteStep1').querySelector('#otClientVat');
        
        if (clientNameEl) {
            clientNameEl.textContent = oteClientData.name;
        }
        if (clientVatEl) {
            clientVatEl.textContent = oteClientData.vat || 'N/A';
        }
        
        if (otDebugMode) {
            console.log('OTE Step 1 - Populated client info:', oteClientData.name, oteClientData.vat);
        }
        
        document.getElementById('oteStep1').style.display = 'block';
    } else if (step === 2) {
        // Copy OT Step 2 content
        var otStep2Content = document.getElementById('otStep2').cloneNode(true);
        otStep2Content.id = 'oteStep2Content';
        document.getElementById('oteStep2').innerHTML = '';
        document.getElementById('oteStep2').appendChild(otStep2Content);
        
        // Populate client information in the cloned content (if it exists in step 2)
        var clientNameEl2 = document.getElementById('oteStep2').querySelector('#otClientName');
        var clientVatEl2 = document.getElementById('oteStep2').querySelector('#otClientVat');
        
        if (clientNameEl2) {
            clientNameEl2.textContent = oteClientData.name;
        }
        if (clientVatEl2) {
            clientVatEl2.textContent = oteClientData.vat || 'N/A';
        }
        
        if (otDebugMode) {
            console.log('OTE Step 2 - Populated client info:', oteClientData.name, oteClientData.vat);
        }
        
        document.getElementById('oteStep2').style.display = 'block';
    }
    
    oteUpdateProgress();
}

// OTE Update Progress
function oteUpdateProgress() {
    var stepIndicator = document.getElementById('oteStepIndicator');
    var progressBar = document.getElementById('oteProgressBar');
    var backBtn = document.getElementById('oteBackBtn');
    var nextBtn = document.getElementById('oteNextBtn');
    var submitBtn = document.getElementById('oteSubmitBtn');
    
    if (oteCurrentStep === 0) {
        stepIndicator.textContent = '(Paso 1 de 3)';
        progressBar.style.width = '33%';
        progressBar.setAttribute('aria-valuenow', '33');
        progressBar.textContent = 'Paso 1: Proveedor';
        backBtn.style.display = 'none';
        nextBtn.style.display = 'inline-block';
        submitBtn.style.display = 'none';
    } else if (oteCurrentStep === 1) {
        stepIndicator.textContent = '(Paso 2 de 3)';
        progressBar.style.width = '66%';
        progressBar.setAttribute('aria-valuenow', '66');
        progressBar.textContent = 'Paso 2: Dirección de Entrega';
        backBtn.style.display = 'inline-block';
        nextBtn.style.display = 'inline-block';
        submitBtn.style.display = 'none';
    } else if (oteCurrentStep === 2) {
        stepIndicator.textContent = '(Paso 3 de 3)';
        progressBar.style.width = '100%';
        progressBar.setAttribute('aria-valuenow', '100');
        progressBar.textContent = 'Paso 3: Contacto';
        backBtn.style.display = 'inline-block';
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-block';
    }
}

// OTE Validate Step 1 (Delivery)
function oteValidateStep1() {
    // Check elements within the OTE modal context (oteStep1)
    var oteStep1Container = document.getElementById('oteStep1');
    if (!oteStep1Container) {
        alert('Error: No se puede validar el formulario');
        return false;
    }
    
    // Find delivery type radio buttons within OTE step 1
    var deliveryTypeRadio = oteStep1Container.querySelector('input[name="otDeliveryType"]:checked');
    if (!deliveryTypeRadio) {
        alert('Por favor seleccione un tipo de entrega');
        return false;
    }
    
    var deliveryType = deliveryTypeRadio.value;
    
    if (otDebugMode) console.log('OTE Step 1 validation - deliveryType:', deliveryType);
    
    if (deliveryType === 'recoger') {
        if (otDebugMode) console.log('OTE Step 1 validation - Pickup selected, valid');
        return true; // No address validation needed for pickup
    }
    
    // Find delivery select and manual inputs within OTE step 1
    var deliverySelect = oteStep1Container.querySelector('#otDeliveryAddress');
    var manualName = oteStep1Container.querySelector('#otManualAddressName');
    var manualStreet = oteStep1Container.querySelector('#otManualStreet');
    
    if (otDebugMode) {
        console.log('OTE Step 1 validation - deliverySelect:', deliverySelect);
        console.log('OTE Step 1 validation - deliverySelect.value:', deliverySelect ? deliverySelect.value : 'null');
        console.log('OTE Step 1 validation - manualName:', manualName);
        console.log('OTE Step 1 validation - manualStreet:', manualStreet);
    }
    
    // Check if an existing address is selected
    if (deliverySelect && deliverySelect.value) {
        if (otDebugMode) console.log('OTE Step 1 validation - Existing address selected, valid');
        return true;
    }
    
    // Check if manual address is filled
    if (manualName && manualStreet && manualName.value && manualStreet.value) {
        if (otDebugMode) console.log('OTE Step 1 validation - Manual address filled, valid');
        return true;
    }
    
    alert('Por favor seleccione o ingrese una dirección de entrega');
    return false;
}

// Submit OTE with all data
function submitOTE() {
    if (otDebugMode) console.log('Submitting OTE...');
    
    // Get all data from stored client data
    var clientId = oteClientData.id;
    var clientName = oteClientData.name;
    var clientVat = oteClientData.vat;
    var agentName = '<?php echo addslashes($user->name); ?>';
    var supplierName = oteSelectedSupplier.name;
    
    // Get containers for OTE steps
    var oteStep1Container = document.getElementById('oteStep1');
    var oteStep2Container = document.getElementById('oteStep2');
    
    if (!oteStep1Container || !oteStep2Container) {
        alert('Error: No se puede acceder a los datos del formulario');
        return;
    }
    
    // Get delivery type from OTE step 1
    var deliveryTypeRadio = oteStep1Container.querySelector('input[name="otDeliveryType"]:checked');
    var deliveryType = deliveryTypeRadio ? deliveryTypeRadio.value : 'domicilio';
    
    if (otDebugMode) console.log('OTE Submit - deliveryType:', deliveryType);
    
    // Get delivery address from OTE step 1
    var deliveryStreet = '', deliveryCity = '';
    if (deliveryType === 'recoger') {
        deliveryStreet = 'Recoger en Oficina';
        deliveryCity = '';
    } else {
        var deliverySelect = oteStep1Container.querySelector('#otDeliveryAddress');
        if (deliverySelect && deliverySelect.value) {
            var selectedOption = deliverySelect.options[deliverySelect.selectedIndex];
            deliveryStreet = selectedOption.dataset.street || '';
            deliveryCity = selectedOption.dataset.city || '';
            if (otDebugMode) console.log('OTE Submit - Selected address:', deliveryStreet, deliveryCity);
        } else {
            var manualStreet = oteStep1Container.querySelector('#otManualStreet');
            var manualCity = oteStep1Container.querySelector('#otManualCity');
            deliveryStreet = manualStreet ? (manualStreet.value || '') : '';
            deliveryCity = manualCity ? (manualCity.value || '') : '';
            if (otDebugMode) console.log('OTE Submit - Manual address:', deliveryStreet, deliveryCity);
        }
    }
    
    // Merge street and city
    var deliveryAddress = deliveryStreet;
    if (deliveryCity && deliveryType !== 'recoger') {
        deliveryAddress += ', ' + deliveryCity;
    }
    
    // Get delivery instructions from OTE step 1
    var deliveryInstructionsField = oteStep1Container.querySelector('#otDeliveryInstructions');
    var deliveryInstructions = deliveryInstructionsField ? (deliveryInstructionsField.value || '') : '';
    
    // Get contact person from OTE step 2
    var contactName = '', contactPhone = '';
    var contactSelect = oteStep2Container.querySelector('#otContactPerson');
    if (contactSelect && contactSelect.value) {
        var selectedContact = contactSelect.options[contactSelect.selectedIndex];
        contactName = selectedContact.dataset.contactName || '';
        contactPhone = selectedContact.dataset.contactPhone || '';
        if (otDebugMode) console.log('OTE Submit - Selected contact:', contactName, contactPhone);
    } else {
        var manualContactName = oteStep2Container.querySelector('#otManualContactName');
        var manualContactPhone = oteStep2Container.querySelector('#otManualContactPhone');
        contactName = manualContactName ? (manualContactName.value || '') : '';
        contactPhone = manualContactPhone ? (manualContactPhone.value || '') : '';
        if (otDebugMode) console.log('OTE Submit - Manual contact:', contactName, contactPhone);
    }
    
    // Build URL with all parameters including supplier_name
    var url = oteDestinationUrl;
    url += '?supplier_name=' + encodeURIComponent(supplierName);
    url += '&client_id=' + encodeURIComponent(clientId);
    url += '&contact_name=' + encodeURIComponent(clientName);
    url += '&contact_vat=' + encodeURIComponent(clientVat);
    url += '&x_studio_agente_de_ventas=' + encodeURIComponent(agentName);
    url += '&tipo_entrega=' + encodeURIComponent(deliveryType);
    url += '&delivery_address=' + encodeURIComponent(deliveryAddress);
    url += '&instrucciones_entrega=' + encodeURIComponent(deliveryInstructions);
    url += '&contact_person_name=' + encodeURIComponent(contactName);
    url += '&contact_person_phone=' + encodeURIComponent(contactPhone);
    
    if (otDebugMode) console.log('OTE URL:', url);
    
    // Open in new tab
    window.open(url, '_blank');
    
    // Close modal
    var oteModal = bootstrap.Modal.getInstance(document.getElementById('oteModal'));
    oteModal.hide();
}

// Open OT Modal
function openOTModal(clientId, clientName, clientVat) {
    // Set client information
    document.getElementById('otClientId').value = clientId;
    document.getElementById('otClientName').textContent = clientName;
    document.getElementById('otClientVat').textContent = clientVat || 'N/A';
    
    // Reset delivery type to domicilio
    document.getElementById('otDeliveryTypeDomicilio').checked = true;
    document.getElementById('otDeliveryAddressContainer').style.display = 'block';
    
    // Clear previous selections - delivery address
    document.getElementById('otDeliveryAddress').innerHTML = '<option value="">Cargando direcciones...</option>';
    document.getElementById('otManualAddressName').value = '';
    document.getElementById('otManualStreet').value = '';
    document.getElementById('otManualCity').value = '';
    document.getElementById('otSaveAddressToOdoo').checked = false;
    document.getElementById('otDeliveryInstructions').value = '';
    document.getElementById('otAddressPreview').style.display = 'none';
    document.getElementById('otSaveAddressButtonContainer').style.display = 'none';
    
    // Clear previous selections - contact
    document.getElementById('otContactSelect').innerHTML = '<option value="">Seleccione un contacto...</option>';
    document.getElementById('otManualContactName').value = '';
    document.getElementById('otManualContactPhone').value = '';
    document.getElementById('otSaveContactToOdoo').checked = false;
    document.getElementById('otContactPreview').style.display = 'none';
    document.getElementById('otSaveContactButtonContainer').style.display = 'none';
    
    // Reset to Step 1
    document.getElementById('otStep1').style.display = 'block';
    document.getElementById('otStep2').style.display = 'none';
    document.getElementById('otBtnNext').style.display = 'inline-block';
    document.getElementById('otBtnBack').style.display = 'none';
    document.getElementById('otBtnSubmit').style.display = 'none';
    document.getElementById('otProgressBar').style.width = '50%';
    document.getElementById('otProgressBar').textContent = 'Paso 1: Dirección de Entrega';
    document.getElementById('otStepIndicator').textContent = '(Paso 1 de 2)';
    
    // Load child contacts and parent contact via AJAX
    loadChildContacts(clientId, clientName);
    
    // Show modal
    var otModal = new bootstrap.Modal(document.getElementById('otModal'));
    otModal.show();
}

// Load child contacts for the selected client
function loadChildContacts(clientId, parentName) {
    // Make AJAX call to get child contacts and parent contact info
    Promise.all([
        fetch('<?php echo Route::_("index.php?option=com_odoocontacts&task=contact.getChildContacts&format=json"); ?>&id=' + clientId),
        fetch('<?php echo Route::_("index.php?option=com_odoocontacts&task=contact.getParentContact&format=json"); ?>&id=' + clientId)
    ]).then(responses => Promise.all(responses.map(r => r.json())))
      .then(data => {
          otChildContacts = data[0].data || [];
          var parentContact = data[1].data || null;
          
          // Add parent contact to the list with special flag
          if (parentContact) {
              parentContact.isParent = true;
          }
          
          populateDeliveryAddresses(parentContact);
          populateContactPersons(parentContact);
      })
      .catch(error => {
          if (otDebugMode) console.error('Error loading contacts:', error);
          otChildContacts = [];
          populateDeliveryAddresses(null);
          populateContactPersons(null);
      });
}

// Populate delivery address dropdown
function populateDeliveryAddresses(parentContact) {
    var select = document.getElementById('otDeliveryAddress');
    select.innerHTML = '<option value="">Seleccione una dirección...</option>';
    
    if (otChildContacts.length === 0) {
        select.innerHTML = '<option value="">No hay direcciones disponibles - use campos manuales abajo</option>';
        return;
    }
    
    // Sort: delivery addresses first, then others
    var deliveryAddresses = otChildContacts.filter(c => c.type === 'delivery');
    var otherAddresses = otChildContacts.filter(c => c.type !== 'delivery');
    
    // Add delivery addresses
    if (deliveryAddresses.length > 0) {
        var deliveryGroup = document.createElement('optgroup');
        deliveryGroup.label = 'Direcciones de Entrega';
        deliveryAddresses.forEach(function(contact) {
            var option = document.createElement('option');
            option.value = contact.id;
            option.textContent = contact.name + ' - ' + (contact.street || 'Sin dirección');
            option.dataset.street = contact.street || '';
            option.dataset.city = contact.city || '';
            deliveryGroup.appendChild(option);
        });
        select.appendChild(deliveryGroup);
    }
    
    // Add other addresses if no delivery addresses exist
    if (deliveryAddresses.length === 0 && otherAddresses.length > 0) {
        var otherGroup = document.createElement('optgroup');
        otherGroup.label = 'Otras Direcciones';
        otherAddresses.forEach(function(contact) {
            var option = document.createElement('option');
            option.value = contact.id;
            option.textContent = contact.name + ' - ' + (contact.street || 'Sin dirección');
            option.dataset.street = contact.street || '';
            option.dataset.city = contact.city || '';
            otherGroup.appendChild(option);
        });
        select.appendChild(otherGroup);
    }
    
    // Handle address selection - clear manual inputs when dropdown is used
    select.addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Clear manual inputs
            document.getElementById('otManualAddressName').value = '';
            document.getElementById('otManualStreet').value = '';
            document.getElementById('otManualCity').value = '';
            document.getElementById('otSaveAddressToOdoo').checked = false;
            
            // Set selected address data
            document.getElementById('otSelectedStreet').value = selectedOption.dataset.street;
            document.getElementById('otSelectedCity').value = selectedOption.dataset.city;
            document.getElementById('otPreviewStreet').textContent = selectedOption.dataset.street || 'N/A';
            document.getElementById('otPreviewCity').textContent = selectedOption.dataset.city || 'N/A';
            document.getElementById('otAddressPreview').style.display = 'block';
        } else {
            document.getElementById('otAddressPreview').style.display = 'none';
        }
    });
}

// Populate contact persons dropdown (type = 'contact' + parent)
function populateContactPersons(parentContact) {
    var select = document.getElementById('otContactSelect');
    select.innerHTML = '<option value="">Seleccione un contacto...</option>';
    
    var hasContacts = false;
    
    // Add parent contact first
    if (parentContact) {
        var parentGroup = document.createElement('optgroup');
        parentGroup.label = 'Contacto Principal';
        var option = document.createElement('option');
        option.value = parentContact.id || 0;
        option.textContent = 'Contacto Principal - ' + (parentContact.name || 'Sin nombre');
        option.dataset.name = parentContact.name || '';
        option.dataset.phone = parentContact.phone || parentContact.mobile || '';
        parentGroup.appendChild(option);
        select.appendChild(parentGroup);
        hasContacts = true;
    }
    
    // Filter only 'contact' type from children
    var contactPersons = otChildContacts.filter(c => c.type === 'contact');
    
    if (contactPersons.length > 0) {
        var childGroup = document.createElement('optgroup');
        childGroup.label = 'Contactos Adicionales';
        contactPersons.forEach(function(contact) {
            var option = document.createElement('option');
            option.value = contact.id;
            option.textContent = contact.name;
            option.dataset.name = contact.name || '';
            option.dataset.phone = contact.phone || contact.mobile || '';
            childGroup.appendChild(option);
        });
        select.appendChild(childGroup);
        hasContacts = true;
    }
    
    if (!hasContacts) {
        select.innerHTML = '<option value="">No hay personas de contacto - use campos manuales abajo</option>';
        return;
    }
    
    // Handle contact selection - clear manual inputs when dropdown is used
    select.addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // Clear manual inputs
            document.getElementById('otManualContactName').value = '';
            document.getElementById('otManualContactPhone').value = '';
            document.getElementById('otSaveContactToOdoo').checked = false;
            
            // Set selected contact data
            document.getElementById('otSelectedContactName').value = selectedOption.dataset.name;
            document.getElementById('otSelectedContactPhone').value = selectedOption.dataset.phone;
            document.getElementById('otPreviewContactName').textContent = selectedOption.dataset.name || 'N/A';
            document.getElementById('otPreviewContactPhone').textContent = selectedOption.dataset.phone || 'N/A';
            document.getElementById('otContactPreview').style.display = 'block';
        } else {
            document.getElementById('otContactPreview').style.display = 'none';
        }
    });
}

// Setup manual input listeners
document.addEventListener('DOMContentLoaded', function() {
    // Delivery type toggle handler
    var deliveryTypeDomicilio = document.getElementById('otDeliveryTypeDomicilio');
    var deliveryTypeRecoger = document.getElementById('otDeliveryTypeRecoger');
    var deliveryAddressContainer = document.getElementById('otDeliveryAddressContainer');
    
    if (deliveryTypeDomicilio && deliveryTypeRecoger && deliveryAddressContainer) {
        deliveryTypeDomicilio.addEventListener('change', function() {
            if (this.checked) {
                deliveryAddressContainer.style.display = 'block';
            }
        });
        
        deliveryTypeRecoger.addEventListener('change', function() {
            if (this.checked) {
                deliveryAddressContainer.style.display = 'none';
            }
        });
    }
    
    // Clear delivery dropdown when manual address inputs are used
    var manualAddressFields = ['otManualAddressName', 'otManualStreet', 'otManualCity'];
    manualAddressFields.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                if (this.value) {
                    document.getElementById('otDeliveryAddress').value = '';
                    document.getElementById('otAddressPreview').style.display = 'none';
                }
                // Show/hide save button based on checkbox and manual inputs
                toggleSaveAddressButton();
            });
        }
    });
    
    // Toggle save button when checkbox changes
    var saveAddressCheckbox = document.getElementById('otSaveAddressToOdoo');
    if (saveAddressCheckbox) {
        saveAddressCheckbox.addEventListener('change', toggleSaveAddressButton);
    }
    
    // Clear contact dropdown when manual contact inputs are used
    var manualNameInput = document.getElementById('otManualContactName');
    var manualPhoneInput = document.getElementById('otManualContactPhone');
    
    if (manualNameInput) {
        manualNameInput.addEventListener('input', function() {
            if (this.value) {
                document.getElementById('otContactSelect').value = '';
                document.getElementById('otContactPreview').style.display = 'none';
            }
            toggleSaveContactButton();
        });
    }
    
    if (manualPhoneInput) {
        manualPhoneInput.addEventListener('input', function() {
            if (this.value) {
                document.getElementById('otContactSelect').value = '';
                document.getElementById('otContactPreview').style.display = 'none';
            }
            toggleSaveContactButton();
        });
    }
    
    // Toggle save button when checkbox changes
    var saveContactCheckbox = document.getElementById('otSaveContactToOdoo');
    if (saveContactCheckbox) {
        saveContactCheckbox.addEventListener('change', toggleSaveContactButton);
    }
});

// Toggle save address button visibility
function toggleSaveAddressButton() {
    var checkbox = document.getElementById('otSaveAddressToOdoo');
    var name = document.getElementById('otManualAddressName').value.trim();
    var street = document.getElementById('otManualStreet').value.trim();
    var city = document.getElementById('otManualCity').value.trim();
    var buttonContainer = document.getElementById('otSaveAddressButtonContainer');
    
    if (otDebugMode) {
        console.log('Toggle Address Button - Checkbox:', checkbox.checked, 'Name:', name, 'Street:', street, 'City:', city);
    }
    
    // Show button only if checkbox is checked AND all manual fields have values
    if (checkbox.checked && name && street && city) {
        if (otDebugMode) console.log('Showing address save button');
        buttonContainer.style.display = 'block';
    } else {
        if (otDebugMode) console.log('Hiding address save button');
        buttonContainer.style.display = 'none';
    }
}

// Toggle save contact button visibility
function toggleSaveContactButton() {
    var checkbox = document.getElementById('otSaveContactToOdoo');
    var name = document.getElementById('otManualContactName').value.trim();
    var phone = document.getElementById('otManualContactPhone').value.trim();
    var buttonContainer = document.getElementById('otSaveContactButtonContainer');
    
    if (otDebugMode) {
        console.log('Toggle Contact Button - Checkbox:', checkbox.checked, 'Name:', name, 'Phone:', phone);
    }
    
    // Show button only if checkbox is checked AND all manual fields have values
    if (checkbox.checked && name && phone) {
        if (otDebugMode) console.log('Showing contact save button');
        buttonContainer.style.display = 'block';
    } else {
        if (otDebugMode) console.log('Hiding contact save button');
        buttonContainer.style.display = 'none';
    }
}

// Navigate to Step 2
function goToStep2() {
    // Check delivery type
    var deliveryType = document.querySelector('input[name="otDeliveryType"]:checked').value;
    var deliveryStreet = '';
    var deliveryCity = '';
    
    if (deliveryType === 'recoger') {
        // Pickup option - no address validation needed
        deliveryStreet = 'Recoger en Oficina';
        deliveryCity = '';
        
        // Store for later use
        document.getElementById('otSelectedStreet').value = deliveryStreet;
        document.getElementById('otSelectedCity').value = '';
    } else {
        // Delivery to address - validate address selection
        var deliverySelect = document.getElementById('otDeliveryAddress');
        var manualAddressName = document.getElementById('otManualAddressName').value.trim();
        var manualStreet = document.getElementById('otManualStreet').value.trim();
        var manualCity = document.getElementById('otManualCity').value.trim();
        
        // Validate: either dropdown OR manual inputs
        if (deliverySelect.value) {
            // Using dropdown
            deliveryStreet = document.getElementById('otSelectedStreet').value;
            deliveryCity = document.getElementById('otSelectedCity').value;
        } else if (manualAddressName && manualStreet && manualCity) {
            // Using manual input
            deliveryStreet = manualStreet;
            deliveryCity = manualCity;
            
            // Store for later use
            document.getElementById('otSelectedStreet').value = manualStreet;
            document.getElementById('otSelectedCity').value = manualCity;
        } else {
            alert('Por favor seleccione una dirección de entrega o ingrese los campos manualmente (nombre, dirección y ciudad).');
            return;
        }
    }
    
    // Update summary
    var deliveryAddress = deliveryStreet + (deliveryCity ? ', ' + deliveryCity : '');
    var instructions = document.getElementById('otDeliveryInstructions').value || 'Ninguna';
    
    document.getElementById('otSummaryAddress').textContent = deliveryAddress;
    document.getElementById('otSummaryInstructions').textContent = instructions;
    
    // Hide Step 1, Show Step 2
    document.getElementById('otStep1').style.display = 'none';
    document.getElementById('otStep2').style.display = 'block';
    
    // Update buttons
    document.getElementById('otBtnNext').style.display = 'none';
    document.getElementById('otBtnBack').style.display = 'inline-block';
    document.getElementById('otBtnSubmit').style.display = 'inline-block';
    
    // Update progress bar
    document.getElementById('otProgressBar').style.width = '100%';
    document.getElementById('otProgressBar').textContent = 'Paso 2: Persona de Contacto';
    document.getElementById('otStepIndicator').textContent = '(Paso 2 de 2)';
}

// Navigate back to Step 1
function goToStep1() {
    // Hide Step 2, Show Step 1
    document.getElementById('otStep2').style.display = 'none';
    document.getElementById('otStep1').style.display = 'block';
    
    // Update buttons
    document.getElementById('otBtnNext').style.display = 'inline-block';
    document.getElementById('otBtnBack').style.display = 'none';
    document.getElementById('otBtnSubmit').style.display = 'none';
    
    // Update progress bar
    document.getElementById('otProgressBar').style.width = '50%';
    document.getElementById('otProgressBar').textContent = 'Paso 1: Dirección de Entrega';
    document.getElementById('otStepIndicator').textContent = '(Paso 1 de 2)';
}

// Submit OT Form
function submitOT() {
    var clientId = document.getElementById('otClientId').value;
    var contactSelect = document.getElementById('otContactSelect');
    var manualName = document.getElementById('otManualContactName').value.trim();
    var manualPhone = document.getElementById('otManualContactPhone').value.trim();
    
    var contactName = '';
    var contactPhone = '';
    
    // Determine if using dropdown or manual input
    if (contactSelect.value) {
        // Using dropdown
        contactName = document.getElementById('otSelectedContactName').value;
        contactPhone = document.getElementById('otSelectedContactPhone').value;
    } else if (manualName && manualPhone) {
        // Using manual input
        contactName = manualName;
        contactPhone = manualPhone;
    } else {
        alert('Por favor seleccione un contacto o ingrese nombre y teléfono manualmente.');
        return;
    }
    
    // Get all form data
    var clientName = document.getElementById('otClientName').textContent;
    var clientVat = document.getElementById('otClientVat').textContent;
    var deliveryStreet = document.getElementById('otSelectedStreet').value;
    var deliveryCity = document.getElementById('otSelectedCity').value;
    var instructions = document.getElementById('otDeliveryInstructions').value;
    var agentName = document.getElementById('otAgentName').textContent;
    var deliveryType = document.querySelector('input[name="otDeliveryType"]:checked').value;
    
    // Merge street and city into single delivery address
    var deliveryAddress = deliveryStreet;
    if (deliveryCity) {
        deliveryAddress += (deliveryStreet ? ', ' : '') + deliveryCity;
    }
    
    // Build URL with all parameters including contact info and delivery type
    var url = otDestinationUrl;
    url += '?client_id=' + encodeURIComponent(clientId);
    url += '&contact_name=' + encodeURIComponent(clientName);
    url += '&contact_vat=' + encodeURIComponent(clientVat);
    url += '&x_studio_agente_de_ventas=' + encodeURIComponent(agentName);
    url += '&tipo_entrega=' + encodeURIComponent(deliveryType);
    url += '&delivery_address=' + encodeURIComponent(deliveryAddress);
    url += '&instrucciones_entrega=' + encodeURIComponent(instructions);
    url += '&contact_person_name=' + encodeURIComponent(contactName);
    url += '&contact_person_phone=' + encodeURIComponent(contactPhone);
    
    // Open URL in same window
    window.location.href = url;
}

// Save delivery address to Odoo now (synchronous with feedback)
function saveDeliveryAddressNow() {
    var clientId = document.getElementById('otClientId').value;
    var addressName = document.getElementById('otManualAddressName').value.trim();
    var street = document.getElementById('otManualStreet').value.trim();
    var city = document.getElementById('otManualCity').value.trim();
    var agentName = document.getElementById('otAgentName').textContent;
    
    // Validate inputs
    if (!addressName || !street || !city) {
        showNotification('Por favor complete todos los campos de dirección', 'warning');
        return;
    }
    
    // Disable button while saving
    var saveBtn = event.target;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    var formData = new FormData();
    formData.append('parent_id', clientId);
    formData.append('name', addressName);
    formData.append('street', street);
    formData.append('city', city);
    formData.append('type', 'delivery');
    formData.append('x_studio_agente_de_ventas', agentName);
    formData.append('<?php echo Session::getFormToken(); ?>', '1');
    
    fetch('<?php echo Route::_("index.php?option=com_odoocontacts&task=contact.saveDeliveryAddressAsync&format=json"); ?>', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              showNotification('Dirección guardada exitosamente', 'success');
              
              // Add new address to dropdown
              var select = document.getElementById('otDeliveryAddress');
              var option = document.createElement('option');
              option.value = data.address_id || 'new';
              option.textContent = addressName + ' - ' + street;
              option.dataset.street = street;
              option.dataset.city = city;
              option.selected = true;
              select.appendChild(option);
              
              // Set hidden fields
              document.getElementById('otSelectedStreet').value = street;
              document.getElementById('otSelectedCity').value = city;
              
              // Show preview
              document.getElementById('otPreviewStreet').textContent = street;
              document.getElementById('otPreviewCity').textContent = city;
              document.getElementById('otAddressPreview').style.display = 'block';
              
              // Clear manual inputs and hide button
              document.getElementById('otManualAddressName').value = '';
              document.getElementById('otManualStreet').value = '';
              document.getElementById('otManualCity').value = '';
              document.getElementById('otSaveAddressToOdoo').checked = false;
              document.getElementById('otSaveAddressButtonContainer').style.display = 'none';
          } else {
              showNotification('Error al guardar: ' + (data.message || 'Error desconocido') + '. Por favor contacte a soporte.', 'danger');
          }
          
          // Re-enable button
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Dirección';
      })
      .catch(error => {
          showNotification('Error de conexión. Por favor contacte a soporte.', 'danger');
          if (otDebugMode) console.error('Error saving delivery address:', error);
          
          // Re-enable button
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Dirección';
      });
}

// Save contact to Odoo now (synchronous with feedback)
function saveContactNow() {
    var clientId = document.getElementById('otClientId').value;
    var contactName = document.getElementById('otManualContactName').value.trim();
    var contactPhone = document.getElementById('otManualContactPhone').value.trim();
    var agentName = document.getElementById('otAgentName').textContent;
    
    // Validate inputs
    if (!contactName || !contactPhone) {
        showNotification('Por favor complete nombre y teléfono del contacto', 'warning');
        return;
    }
    
    // Disable button while saving
    var saveBtn = event.target;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    var formData = new FormData();
    formData.append('parent_id', clientId);
    formData.append('name', contactName);
    formData.append('phone', contactPhone);
    formData.append('type', 'contact');
    formData.append('x_studio_agente_de_ventas', agentName);
    formData.append('<?php echo Session::getFormToken(); ?>', '1');
    
    fetch('<?php echo Route::_("index.php?option=com_odoocontacts&task=contact.saveChildContactAsync&format=json"); ?>', {
        method: 'POST',
        body: formData
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              showNotification('Contacto guardado exitosamente', 'success');
              
              // Add new contact to dropdown
              var select = document.getElementById('otContactSelect');
              var option = document.createElement('option');
              option.value = data.contact_id || 'new';
              option.textContent = contactName;
              option.dataset.name = contactName;
              option.dataset.phone = contactPhone;
              option.selected = true;
              select.appendChild(option);
              
              // Set hidden fields
              document.getElementById('otSelectedContactName').value = contactName;
              document.getElementById('otSelectedContactPhone').value = contactPhone;
              
              // Show preview
              document.getElementById('otPreviewContactName').textContent = contactName;
              document.getElementById('otPreviewContactPhone').textContent = contactPhone;
              document.getElementById('otContactPreview').style.display = 'block';
              
              // Clear manual inputs and hide button
              document.getElementById('otManualContactName').value = '';
              document.getElementById('otManualContactPhone').value = '';
              document.getElementById('otSaveContactToOdoo').checked = false;
              document.getElementById('otSaveContactButtonContainer').style.display = 'none';
          } else {
              showNotification('Error al guardar: ' + (data.message || 'Error desconocido') + '. Por favor contacte a soporte.', 'danger');
          }
          
          // Re-enable button
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Contacto';
      })
      .catch(error => {
          showNotification('Error de conexión. Por favor contacte a soporte.', 'danger');
          if (otDebugMode) console.error('Error saving contact:', error);
          
          // Re-enable button
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Contacto';
      });
}

// Show temporary notification
function showNotification(message, type) {
    // Create notification element
    var notification = document.createElement('div');
    notification.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.innerHTML = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 150);
    }, 4000);
}

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
    
    // Handle search form submission
    var searchForm = document.getElementById('adminForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Clear any existing limitstart to start from first page when searching
            var limitstartInput = searchForm.querySelector('input[name="limitstart"]');
            if (limitstartInput) {
                limitstartInput.value = '0';
            }
        });
    }
    
    // Handle search input enter key
    var searchInput = document.getElementById('filter_search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('adminForm').submit();
            }
        });
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