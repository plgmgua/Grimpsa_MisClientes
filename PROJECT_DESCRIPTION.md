# Odoo Contacts Component - Project Description

## Overview

The **Odoo Contacts Component** is a Joomla 5.0 component that provides a customer management system integrated with Odoo ERP. It allows sales agents to view, create, edit, and manage their client contacts through a web-based interface while keeping data synchronized with the main Odoo ERP system.

## Project Structure

```
com_odoocontacts/
├── admin/                          # Administration files
│   ├── forms/                      # Form definitions
│   ├── language/                   # Admin language files
│   ├── services/                   # Service providers
│   ├── src/                        # Admin source code
│   └── tmpl/                       # Admin templates
├── media/                          # Frontend assets
│   ├── css/                        # Stylesheets
│   └── js/                         # JavaScript files
├── site/                           # Frontend files
│   ├── forms/                      # Form definitions
│   ├── language/                   # Frontend language files
│   ├── src/                        # Frontend source code
│   └── tmpl/                       # Frontend templates
└── odoocontacts.xml               # Component manifest
```

## Core Architecture

### 1. MVC Pattern Implementation

**Models** (`site/src/Model/`):
- `ContactsModel.php` - Handles contact listing with pagination and search
- `ContactModel.php` - Handles individual contact CRUD operations

**Views** (`site/src/View/`):
- `Contacts/HtmlView.php` - Displays contact list
- `Contact/HtmlView.php` - Displays individual contact details

**Controllers** (`site/src/Controller/`):
- `ContactsController.php` - Handles contact list actions
- `ContactController.php` - Handles individual contact actions

### 2. Odoo Integration Layer

**OdooHelper** (`site/src/Helper/OdooHelper.php`):
- Core integration class for Odoo XML-RPC API
- Handles authentication and API calls
- Provides methods for contact operations
- **Key Methods**:
  - `getContactsByAgent($agentName, $page, $limit, $search)` - Get contacts for specific agent
  - `getContactsCountByAgent($agentName, $search)` - Get contact count
  - `executeOdooCall($xmlPayload)` - Execute XML-RPC calls
  - `parseContactsFromAllResults($result, $agentName)` - Parse API responses

## Key Features

### 1. Contact Management
- **List View**: Display contacts with pagination (15/30/100 items per page)
- **Search**: Server-side filtering across name, email, phone, mobile
- **CRUD Operations**: Create, read, update, delete contacts
- **Agent Filtering**: Contacts filtered by sales agent

### 2. User Interface
- **Responsive Design**: Bootstrap-based responsive layout
- **Professional Styling**: Sober colors, compact table design
- **Search Interface**: Real-time search with clear functionality
- **Pagination**: Navigation controls with item counters

### 3. Odoo Integration
- **XML-RPC API**: Direct integration with Odoo ERP
- **Authentication**: API key-based authentication
- **Data Synchronization**: Real-time data from Odoo
- **Error Handling**: Graceful fallbacks and error messages

## Configuration

### Component Parameters
```xml
<config>
    <fields name="params">
        <fieldset name="component">
            <field name="odoo_url" type="text" default="https://grupoimpre.odoo.com/xmlrpc/2/object"/>
            <field name="odoo_database" type="text" default="grupoimpre"/>
            <field name="odoo_user_id" type="text" default="2"/>
            <field name="odoo_api_key" type="password" default="2386bb5ae66c7fd9022feaf82148680c4cf4ce3b"/>
            <field name="contacts_per_page" type="number" default="15"/>
            <field name="enable_debug" type="radio" default="0"/>
        </fieldset>
    </fields>
</config>
```

## Odoo API Integration

### Connection Details
- **Endpoint**: `https://grupoimpre.odoo.com/xmlrpc/2/object`
- **Database**: `grupoimpre`
- **User ID**: `2`
- **API Key**: `2386bb5ae66c7fd9022feaf82148680c4cf4ce3b`

### API Methods Used
1. **search_read**: Retrieve contacts with fields
2. **search_count**: Get total count of records
3. **create**: Create new contacts
4. **write**: Update existing contacts
5. **unlink**: Delete contacts

### Data Fields Mapped
- `name` - Contact name
- `x_studio_agente_de_ventas` - Sales agent field
- `email` - Email address
- `phone` - Phone number
- `mobile` - Mobile number
- `vat` - NIT/VAT number
- `street` - Address
- `city` - City
- `type` - Contact type

## Extending for Other Components

### 1. Creating New Odoo-Integrated Components

**Step 1: Create Component Structure**
```bash
com_newcomponent/
├── admin/
├── media/
├── site/
└── newcomponent.xml
```

**Step 2: Reuse OdooHelper**
```php
// In your new component's helper
use Grimpsa\Component\OdooContacts\Site\Helper\OdooHelper;

class NewComponentHelper extends OdooHelper
{
    public function getDataFromOdoo($params)
    {
        // Use existing connection methods
        return $this->executeOdooCall($xmlPayload);
    }
}
```

**Step 3: Extend OdooHelper for New Models**
```php
// Add new methods to OdooHelper
public function getInvoicesByAgent($agentName, $page = 1, $limit = 20)
{
    $xmlPayload = '<?xml version="1.0"?>
    <methodCall>
        <methodName>execute_kw</methodName>
        <params>
            <param><value><string>grupoimpre</string></value></param>
            <param><value><int>2</int></value></param>
            <param><value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value></param>
            <param><value><string>account.move</string></value></param> <!-- Invoice model -->
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
                                    </data>
                                </array>
                            </value>
                        </member>
                    </struct>
                </value>
            </param>
        </params>
    </methodCall>';
    
    return $this->executeOdooCall($xmlPayload);
}
```

### 2. Shared Configuration

**Create Shared Configuration Class**
```php
// components/com_sharedconfig/src/Helper/SharedConfigHelper.php
class SharedConfigHelper
{
    private static $odooConfig = [
        'url' => 'https://grupoimpre.odoo.com/xmlrpc/2/object',
        'database' => 'grupoimpre',
        'user_id' => 2,
        'api_key' => '2386bb5ae66c7fd9022feaf82148680c4cf4ce3b'
    ];
    
    public static function getOdooConfig()
    {
        return self::$odooConfig;
    }
}
```

### 3. Common Odoo Operations

**Base Odoo Helper Class**
```php
// components/com_sharedodoo/src/Helper/BaseOdooHelper.php
abstract class BaseOdooHelper
{
    protected $config;
    
    public function __construct()
    {
        $this->config = SharedConfigHelper::getOdooConfig();
    }
    
    protected function executeOdooCall($xmlPayload)
    {
        // Common XML-RPC execution logic
    }
    
    protected function buildDomainXml($domain)
    {
        // Common domain building logic
    }
    
    abstract protected function getModelName();
    abstract protected function getFields();
}
```

## Development Guidelines

### 1. Code Standards
- Follow Joomla coding standards
- Use PSR-4 autoloading
- Implement proper error handling
- Add debug logging for troubleshooting

### 2. Security Considerations
- Validate all user inputs
- Use CSRF tokens for forms
- Sanitize data before Odoo API calls
- Implement proper access control

### 3. Performance Optimization
- Cache Odoo API responses when appropriate
- Implement pagination for large datasets
- Use server-side filtering for search
- Optimize database queries

### 4. Error Handling
```php
try {
    $result = $this->executeOdooCall($xmlPayload);
    if (!$result) {
        throw new Exception('Odoo API call failed');
    }
    return $this->parseResponse($result);
} catch (Exception $e) {
    Factory::getApplication()->enqueueMessage('Error: ' . $e->getMessage(), 'error');
    return [];
}
```

## Debugging and Troubleshooting

### 1. Enable Debug Mode
- Set `enable_debug` parameter to `1` in component configuration
- Check Joomla system logs for detailed error messages
- Monitor Odoo API responses

### 2. Common Issues
- **No contacts showing**: Check Odoo API credentials and agent field mapping
- **Search not working**: Verify server-side filtering implementation
- **Pagination errors**: Check Joomla pagination method compatibility
- **CSS not loading**: Verify WebAssetManager implementation

### 3. Testing
- Test with different user agents
- Verify search functionality with various terms
- Test pagination with large datasets
- Validate CRUD operations

## Future Enhancements

### 1. Planned Features
- Contact import/export functionality
- Advanced search filters
- Contact activity history
- Integration with other Odoo modules

### 2. Performance Improvements
- Implement caching layer
- Optimize API calls
- Add lazy loading for large datasets

### 3. User Experience
- Add keyboard shortcuts
- Implement drag-and-drop functionality
- Add contact favorites
- Real-time notifications

## Integration Points

### 1. Joomla Integration
- User authentication and authorization
- Menu system integration
- Template override system
- Plugin system integration

### 2. Odoo Integration
- XML-RPC API communication
- Data synchronization
- Error handling and logging
- Authentication management

### 3. Third-Party Integrations
- Email system integration
- SMS notification system
- Calendar integration
- Document management system

This component serves as a foundation for building other Odoo-integrated components in Joomla, providing a robust and extensible architecture for enterprise applications.
