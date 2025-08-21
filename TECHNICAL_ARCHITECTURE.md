# Technical Architecture - Odoo Contacts Component

## Implementation Details

### 1. Odoo XML-RPC Integration

#### Connection Setup
```php
// OdooHelper.php - Connection configuration
private $config;

public function __construct()
{
    $this->config = ComponentHelper::getParams('com_odoocontacts');
}

private function executeOdooCall($xmlPayload)
{
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://grupoimpre.odoo.com/xmlrpc/2/object',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $xmlPayload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml',
            'X-Openerp-Session-Id: 2386bb5ae66c7fd9022feaf82148680c4cf4ce3b'
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode !== 200 || !$response) {
        return false;
    }

    $xml = simplexml_load_string($response);
    $json = json_encode($xml);
    return json_decode($json, true);
}
```

#### XML Payload Structure
```xml
<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param><value><string>grupoimpre</string></value></param>
      <param><value><int>2</int></value></param>
      <param><value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value></param>
      <param><value><string>res.partner</string></value></param>
      <param><value><string>search_read</string></value></param>
      <param><value><array><data/></array></value></param>
      <param>
         <value>
            <struct>
               <member>
                  <name>fields</name>
                  <value><array><data>...</data></array></value>
               </member>
            </struct>
         </value>
      </param>
   </params>
</methodCall>
```

### 2. Server-Side Search Implementation

#### Search Logic in ContactsModel
```php
// Apply search filter on server side
if (!empty($search)) {
    $searchLower = strtolower($search);
    $nameMatch = strpos(strtolower($normalizedContact['name']), $searchLower) !== false;
    $emailMatch = strpos(strtolower($normalizedContact['email']), $searchLower) !== false;
    $phoneMatch = strpos(strtolower($normalizedContact['phone']), $searchLower) !== false;
    $mobileMatch = strpos(strtolower($normalizedContact['mobile']), $searchLower) !== false;
    
    if ($nameMatch || $emailMatch || $phoneMatch || $mobileMatch) {
        $validContacts[] = $normalizedContact;
    }
} else {
    $validContacts[] = $normalizedContact;
}
```

### 3. Pagination Implementation

#### Model State Management
```php
protected function populateState($ordering = 'name', $direction = 'asc')
{
    $app = Factory::getApplication();
    
    // Get pagination request variables
    $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', 15, 'uint');
    $this->setState('list.limit', $limit);

    $limitstart = $app->input->get('limitstart', 0, 'uint');
    $this->setState('list.start', $limitstart);

    // Get the search filter
    $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
    $this->setState('filter.search', $search);

    // Set the ordering
    $this->setState('list.ordering', $ordering);
    $this->setState('list.direction', $direction);
}
```

#### Pagination Display
```php
// Template pagination display
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
```

## Code Patterns for Extension

### 1. Creating New Odoo Models

#### Step 1: Extend OdooHelper
```php
// Add new method to OdooHelper.php
public function getInvoicesByAgent($agentName, $page = 1, $limit = 20)
{
    $xmlPayload = $this->buildInvoiceXmlPayload($agentName, $page, $limit);
    $result = $this->executeOdooCall($xmlPayload);
    
    if (!$result) {
        return [];
    }
    
    return $this->parseInvoicesFromResults($result, $agentName);
}

private function buildInvoiceXmlPayload($agentName, $page, $limit)
{
    return '<?xml version="1.0"?>
    <methodCall>
        <methodName>execute_kw</methodName>
        <params>
            <param><value><string>grupoimpre</string></value></param>
            <param><value><int>2</int></value></param>
            <param><value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value></param>
            <param><value><string>account.move</string></value></param>
            <param><value><string>search_read</string></value></param>
            <param><value><array><data/></array></value></param>
            <param>
                <value>
                    <struct>
                        <member>
                            <name>fields</name>
                            <value>
                                <array>
                                    <data>
                                        <value><string>name</string></value>
                                        <value><string>partner_id</string></value>
                                        <value><string>amount_total</string></value>
                                        <value><string>state</string></value>
                                        <value><string>invoice_date</string></value>
                                    </data>
                                </array>
                            </value>
                        </member>
                    </struct>
                </value>
            </param>
        </params>
    </methodCall>';
}

private function parseInvoicesFromResults($result, $agentName)
{
    // Parse invoice data similar to contacts
    $invoices = [];
    
    if (!isset($result['params']['param']['value']['array']['data']['value'])) {
        return $invoices;
    }
    
    $values = $result['params']['param']['value']['array']['data']['value'];
    
    if (isset($values['struct'])) {
        $values = [$values];
    }
    
    foreach ($values as $value) {
        if (!isset($value['struct']['member'])) {
            continue;
        }
        
        $invoice = $this->extractInvoiceData($value);
        if ($invoice) {
            $invoices[] = $invoice;
        }
    }
    
    return $invoices;
}
```

#### Step 2: Create Model
```php
// site/src/Model/InvoicesModel.php
namespace Grimpsa\Component\OdooContacts\Site\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Grimpsa\Component\OdooContacts\Site\Helper\OdooHelper;

class InvoicesModel extends ListModel
{
    public function getItems()
    {
        $user = Factory::getUser();
        
        if ($user->guest) {
            return [];
        }

        try {
            $helper = new OdooHelper();
            
            $limitstart = $this->getStart();
            $limit = $this->getState('list.limit', 15);
            $search = $this->getState('filter.search', '');
            
            $page = floor($limitstart / $limit) + 1;
            
            $invoices = $helper->getInvoicesByAgent($user->name, $page, $limit);
            
            if (!is_array($invoices)) {
                return [];
            }
            
            // Apply server-side search filtering
            $validInvoices = [];
            foreach ($invoices as $invoice) {
                if (is_array($invoice)) {
                    $normalizedInvoice = [
                        'id' => isset($invoice['id']) ? (string)$invoice['id'] : '0',
                        'name' => isset($invoice['name']) ? (string)$invoice['name'] : '',
                        'partner_id' => isset($invoice['partner_id']) ? (string)$invoice['partner_id'] : '',
                        'amount_total' => isset($invoice['amount_total']) ? (string)$invoice['amount_total'] : '',
                        'state' => isset($invoice['state']) ? (string)$invoice['state'] : '',
                        'invoice_date' => isset($invoice['invoice_date']) ? (string)$invoice['invoice_date'] : ''
                    ];
                    
                    // Apply search filter
                    if (!empty($search)) {
                        $searchLower = strtolower($search);
                        $nameMatch = strpos(strtolower($normalizedInvoice['name']), $searchLower) !== false;
                        $partnerMatch = strpos(strtolower($normalizedInvoice['partner_id']), $searchLower) !== false;
                        
                        if ($nameMatch || $partnerMatch) {
                            $validInvoices[] = $normalizedInvoice;
                        }
                    } else {
                        $validInvoices[] = $normalizedInvoice;
                    }
                }
            }
            
            return $validInvoices;
            
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage('Error connecting to Odoo: ' . $e->getMessage(), 'error');
            return [];
        }
    }
}
```

#### Step 3: Create View
```php
// site/src/View/Invoices/HtmlView.php
namespace Grimpsa\Component\OdooContacts\Site\View\Invoices;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        try {
            $this->items = $this->get('Items');
            $this->pagination = $this->get('Pagination');
            $this->state = $this->get('State');
            $this->params = ComponentHelper::getParams('com_odoocontacts');
        } catch (Exception $e) {
            $this->items = [];
            $this->pagination = null;
            $this->state = new \Joomla\Registry\Registry();
            $this->params = ComponentHelper::getParams('com_odoocontacts');
            
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        $this->addToolbar();
        $this->_prepareDocument();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $user = Factory::getUser();
        
        $this->document->setTitle(Text::_('COM_ODOOCONTACTS_INVOICES_TITLE'));
        
        HTMLHelper::_('bootstrap.framework');
        
        $wa = $this->document->getWebAssetManager();
        $wa->registerAndUseStyle('com_odoocontacts.invoices', 'media/com_odoocontacts/css/invoices.css', [], ['version' => 'auto']);
    }
}
```

### 2. Shared Configuration Pattern

#### Create Shared Configuration Component
```php
// components/com_sharedconfig/src/Helper/SharedConfigHelper.php
namespace Grimpsa\Component\SharedConfig\Site\Helper;

class SharedConfigHelper
{
    private static $odooConfig = null;
    
    public static function getOdooConfig()
    {
        if (self::$odooConfig === null) {
            self::$odooConfig = [
                'url' => 'https://grupoimpre.odoo.com/xmlrpc/2/object',
                'database' => 'grupoimpre',
                'user_id' => 2,
                'api_key' => '2386bb5ae66c7fd9022feaf82148680c4cf4ce3b'
            ];
        }
        
        return self::$odooConfig;
    }
    
    public static function getOdooUrl()
    {
        $config = self::getOdooConfig();
        return $config['url'];
    }
    
    public static function getOdooDatabase()
    {
        $config = self::getOdooConfig();
        return $config['database'];
    }
    
    public static function getOdooUserId()
    {
        $config = self::getOdooConfig();
        return $config['user_id'];
    }
    
    public static function getOdooApiKey()
    {
        $config = self::getOdooConfig();
        return $config['api_key'];
    }
}
```

#### Base Odoo Helper Class
```php
// components/com_sharedodoo/src/Helper/BaseOdooHelper.php
namespace Grimpsa\Component\SharedOdoo\Site\Helper;

use Grimpsa\Component\SharedConfig\Site\Helper\SharedConfigHelper;

abstract class BaseOdooHelper
{
    protected $config;
    
    public function __construct()
    {
        $this->config = SharedConfigHelper::getOdooConfig();
    }
    
    protected function executeOdooCall($xmlPayload)
    {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->config['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $xmlPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml',
                'X-Openerp-Session-Id: ' . $this->config['api_key']
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200 || !$response) {
            return false;
        }

        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        return json_decode($json, true);
    }
    
    protected function buildBasicXmlPayload($model, $method, $fields = [])
    {
        $fieldsXml = '';
        if (!empty($fields)) {
            $fieldsXml = '<member><name>fields</name><value><array><data>';
            foreach ($fields as $field) {
                $fieldsXml .= '<value><string>' . htmlspecialchars($field) . '</string></value>';
            }
            $fieldsXml .= '</data></array></value></member>';
        }
        
        return '<?xml version="1.0"?>
        <methodCall>
            <methodName>execute_kw</methodName>
            <params>
                <param><value><string>' . $this->config['database'] . '</string></value></param>
                <param><value><int>' . $this->config['user_id'] . '</int></value></param>
                <param><value><string>' . $this->config['api_key'] . '</string></value></param>
                <param><value><string>' . $model . '</string></value></param>
                <param><value><string>' . $method . '</string></value></param>
                <param><value><array><data/></array></value></param>
                <param><value><struct>' . $fieldsXml . '</struct></value></param>
            </params>
        </methodCall>';
    }
    
    abstract protected function getModelName();
    abstract protected function getFields();
}
```

### 3. Template Pattern for New Components

#### Invoice List Template
```php
<!-- site/tmpl/invoices/default.php -->
<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

HTMLHelper::_('bootstrap.framework');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('com_odoocontacts.invoices', 'media/com_odoocontacts/css/invoices.css', [], ['version' => 'auto']);

$user = Factory::getUser();
?>

<div class="odoo-invoices-component">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Mis Facturas</h1>
            <div class="user-info">
                <small class="text-muted">
                    Agente de Ventas: <?php echo htmlspecialchars($user->name); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Search and Actions -->
    <div class="invoices-ribbon">
        <div class="row align-items-center">
            <div class="col-md-7">
                <form action="<?php echo Route::_('index.php?option=com_odoocontacts&view=invoices'); ?>" method="post" name="adminForm" id="adminForm">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="filter_search" id="filter_search" 
                               value="<?php echo htmlspecialchars($this->state->get('filter.search', '')); ?>" 
                               class="form-control" 
                               placeholder="Buscar facturas..." />
                        <button class="btn btn-outline-secondary" type="submit">
                            Buscar
                        </button>
                        <?php if (!empty($this->state->get('filter.search', ''))): ?>
                            <a href="<?php echo Route::_('index.php?option=com_odoocontacts&view=invoices'); ?>" 
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
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="invoices-table-container">
        <?php if (empty($this->items) || !is_array($this->items)): ?>
            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle"></i> No se Encontraron Facturas</h4>
                <p>No hay facturas disponibles.</p>
            </div>
        <?php else: ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="10%">Número</th>
                        <th width="30%">Cliente</th>
                        <th width="20%">Monto</th>
                        <th width="20%">Estado</th>
                        <th width="20%">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['partner_id']); ?></td>
                            <td><?php echo htmlspecialchars($item['amount_total']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $item['state'] === 'posted' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($item['state']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($item['invoice_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
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
                                    echo "Mostrando {$showing} a {$to} facturas";
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
        <?php endif; ?>
    </div>
</div>
```

## Error Handling Patterns

### 1. Odoo API Error Handling
```php
protected function handleOdooError($response, $operation)
{
    if (!$response) {
        Log::add("Odoo API {$operation} failed - No response", Log::ERROR, 'com_odoocontacts');
        return false;
    }
    
    if (isset($response['fault'])) {
        $faultCode = $response['fault']['value']['struct']['member'][0]['value']['int'] ?? 'unknown';
        $faultString = $response['fault']['value']['struct']['member'][1]['value']['string'] ?? 'Unknown error';
        
        Log::add("Odoo API {$operation} failed - Code: {$faultCode}, Message: {$faultString}", Log::ERROR, 'com_odoocontacts');
        return false;
    }
    
    return true;
}
```

### 2. Model Error Handling
```php
public function getItems()
{
    try {
        // Implementation
        return $items;
    } catch (Exception $e) {
        Log::add("Error in getItems: " . $e->getMessage(), Log::ERROR, 'com_odoocontacts');
        Factory::getApplication()->enqueueMessage('Error loading data: ' . $e->getMessage(), 'error');
        return [];
    }
}
```

## Performance Optimization Patterns

### 1. Caching Implementation
```php
protected function getCachedData($key, $callback, $ttl = 300)
{
    $cache = Factory::getCache('com_odoocontacts');
    $cache->setLifeTime($ttl);
    
    $data = $cache->get($key);
    
    if ($data === false) {
        $data = $callback();
        $cache->store($data, $key);
    }
    
    return $data;
}
```

### 2. Lazy Loading
```php
protected function lazyLoadContacts($agentName, $page, $limit)
{
    $cacheKey = "contacts_{$agentName}_{$page}_{$limit}";
    
    return $this->getCachedData($cacheKey, function() use ($agentName, $page, $limit) {
        $helper = new OdooHelper();
        return $helper->getContactsByAgent($agentName, $page, $limit, '');
    }, 300); // 5 minutes cache
}
```

This technical architecture provides a solid foundation for extending the Odoo Contacts component to other modules while maintaining consistency, performance, and reliability.
