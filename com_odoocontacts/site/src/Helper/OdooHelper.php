<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Helper class for Odoo API operations
 */
class OdooHelper
{
    /**
     * Odoo configuration
     */
    private $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = ComponentHelper::getParams('com_odoocontacts');
    }

    /**
     * Execute Odoo XML-RPC call
     *
     * @param   string  $xmlPayload  The XML payload
     *
     * @return  mixed  The response data or false on failure
     */
    private function executeOdooCall($xmlPayload)
    {
        // Log the request if debug is enabled
        if ($this->config->get('enable_debug', 0)) {
            Log::add('Odoo API Request: ' . substr($xmlPayload, 0, 1000) . '...', Log::DEBUG, 'com_odoocontacts');
        }
        
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
        $error = curl_error($curl);
        curl_close($curl);

        if ($this->config->get('enable_debug', 0)) {
            Log::add('Odoo API Call - HTTP Code: ' . $httpCode, Log::DEBUG, 'com_odoocontacts');
            Log::add('Odoo API Response: ' . substr($response, 0, 2000) . '...', Log::DEBUG, 'com_odoocontacts');
            if ($error) {
                Log::add('Odoo API Error: ' . $error, Log::ERROR, 'com_odoocontacts');
            }
        }

        if ($httpCode !== 200 || !$response) {
            Log::add('Odoo API Failed - HTTP: ' . $httpCode . ', Error: ' . $error, Log::ERROR, 'com_odoocontacts');
            return false;
        }

        // Load XML string exactly like your working PHP script
        $xml = simplexml_load_string($response);
        if (!$xml) {
            Log::add('Failed to parse Odoo XML response', Log::ERROR, 'com_odoocontacts');
            return false;
        }

        // Convert XML to JSON exactly like your working script
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * Get contacts by sales agent - using exact same structure as your working PHP script
     *
     * @param   string   $agentName  The sales agent name
     * @param   integer  $page       The page number
     * @param   integer  $limit      The number of contacts per page
     * @param   string   $search     The search term
     *
     * @return  array  Array of contacts
     */
    public function getContactsByAgent($agentName, $page = 1, $limit = 20, $search = '')
    {
        // Build search domain
        $domain = [];
        
        // Add agent filter
        $domain[] = ['x_studio_agente_de_ventas', '=', $agentName];
        
        // Add search filter if provided
        if (!empty($search)) {
            $domain[] = ['|', '|', '|',
                ['name', 'ilike', $search],
                ['email', 'ilike', $search],
                ['phone', 'ilike', $search],
                ['mobile', 'ilike', $search]
            ];
        }
        
        // Convert domain to XML format
        $domainXml = $this->buildDomainXml($domain);
        
        // Use the exact same XML structure as get_contacts_by_vendor.php
        $xmlPayload = '<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param>
         <value><string>grupoimpre</string></value>
      </param>
      <param>
         <value><int>2</int></value> <!-- User ID -->
      </param>
      <param>
         <value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value>
      </param>
      <param>
         <value><string>res.partner</string></value> <!-- Model -->
      </param>
      <param>
         <value><string>search_read</string></value> <!-- Method -->
      </param>
      <param>
         <value><array><data>' . $domainXml . '</data></array></value> <!-- Args -->
      </param>
      <param>
         <value>
            <struct>
               <member>
                  <name>fields</name>
                  <value>
                     <array>
                        <data>
                           <value><string>name</string></value>
                           <value><string>x_studio_agente_de_ventas</string></value>
                           <value><string>type</string></value>
                           <value><string>complete_name</string></value>
                           <value><string>vat</string></value>
                           <value><string>street</string></value>
                           <value><string>city</string></value>
                           <value><string>email</string></value>
                           <value><string>phone</string></value>
                           <value><string>mobile</string></value>
                           <value><string>display_name</string></value>
                           <value><string>child_ids</string></value>
                        </data>
                     </array>
                  </value>
               </member>
            </struct>
         </value> <!-- Keyword Args -->
      </param>
   </params>
</methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        
        if (!$result) {
            return [];
        }

        // Parse exactly like your working PHP script
        return $this->parseContactsFromAllResults($result, $agentName);
    }

    /**
     * Build domain XML for Odoo API calls
     *
     * @param   array  $domain  The search domain
     *
     * @return  string  XML string representation of the domain
     */
    private function buildDomainXml($domain)
    {
        if (empty($domain)) {
            return '';
        }
        
        $xml = '';
        foreach ($domain as $condition) {
            if (is_array($condition)) {
                if (count($condition) === 3) {
                    // Field condition: ['field', 'operator', 'value']
                    $xml .= '<value><array><data>';
                    $xml .= '<value><string>' . htmlspecialchars($condition[0]) . '</string></value>';
                    $xml .= '<value><string>' . htmlspecialchars($condition[1]) . '</string></value>';
                    $xml .= '<value><string>' . htmlspecialchars($condition[2]) . '</string></value>';
                    $xml .= '</data></array></value>';
                } elseif (count($condition) === 1) {
                    // OR operator: ['|']
                    $xml .= '<value><string>' . htmlspecialchars($condition[0]) . '</string></value>';
                }
            }
        }
        
        return $xml;
    }

    /**
     * Get total count of contacts for an agent
     *
     * @param   string  $agentName  The sales agent name
     * @param   string  $search     The search term
     *
     * @return  integer  Total number of contacts
     */
    public function getContactsCountByAgent($agentName, $search = '')
    {
        // Build search domain
        $domain = [];
        
        // Add agent filter
        $domain[] = ['x_studio_agente_de_ventas', '=', $agentName];
        
        // Add search filter if provided
        if (!empty($search)) {
            $domain[] = ['|', '|', '|',
                ['name', 'ilike', $search],
                ['email', 'ilike', $search],
                ['phone', 'ilike', $search],
                ['mobile', 'ilike', $search]
            ];
        }
        
        // Convert domain to XML format
        $domainXml = $this->buildDomainXml($domain);
        
        // Use search_count method to get total count
        $xmlPayload = '<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param>
         <value><string>grupoimpre</string></value>
      </param>
      <param>
         <value><int>2</int></value>
      </param>
      <param>
         <value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value>
      </param>
      <param>
         <value><string>res.partner</string></value>
      </param>
      <param>
         <value><string>search_count</string></value>
      </param>
      <param>
         <value><array><data>' . $domainXml . '</data></array></value>
      </param>
   </params>
</methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        
        if (!$result) {
            return 0;
        }

        // Parse the count from the response
        if (isset($result['params']['param']['value']['int'])) {
            $totalCount = (int)$result['params']['param']['value']['int'];
            // Filter by agent name (this is a simplified approach)
            // In a real implementation, you'd need to filter by agent in the search domain
            return $totalCount;
        }

        return 0;
    }

    /**
     * Parse contacts from all results and filter by agent - like your working PHP script
     *
     * @param   array   $result      The API response
     * @param   string  $agentName   The agent name to filter by
     *
     * @return  array  Array of contacts
     */
    private function parseContactsFromAllResults($result, $agentName)
    {
        if (!isset($result['params']['param']['value']['array']['data']['value'])) {
            return [];
        }

        $contacts = [];
        $values = $result['params']['param']['value']['array']['data']['value'];

        // Handle single contact response
        if (isset($values['struct'])) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if (!isset($value['struct']['member'])) {
                continue;
            }

            $contact = [];
            foreach ($value['struct']['member'] as $member) {
                $fieldName = $member['name'];
                $fieldValue = '';
                
                if (isset($member['value']['string'])) {
                    $fieldValue = $member['value']['string'];
                } elseif (isset($member['value']['int'])) {
                    $fieldValue = (string)$member['value']['int'];
                }
                
                $contact[$fieldName] = $fieldValue;
            }
            
            // Filter by agent name AND only show main contacts (type = 'contact' or empty)
            $contactType = isset($contact['type']) ? $contact['type'] : 'contact';
            $isMainContact = ($contactType === 'contact' || $contactType === '' || $contactType === false);
            
            if (isset($contact['x_studio_agente_de_ventas']) && 
                $contact['x_studio_agente_de_ventas'] === $agentName && 
                $isMainContact) {
                // Map fields to match expected structure
                $normalizedContact = [
                    'id' => isset($contact['id']) ? $contact['id'] : '0',
                    'name' => isset($contact['name']) ? $contact['name'] : '',
                    'email' => isset($contact['email']) ? $contact['email'] : '',
                    'phone' => isset($contact['phone']) ? $contact['phone'] : '',
                    'mobile' => isset($contact['mobile']) ? $contact['mobile'] : '',
                    'street' => isset($contact['street']) ? $contact['street'] : '',
                    'city' => isset($contact['city']) ? $contact['city'] : '',
                    'vat' => isset($contact['vat']) ? $contact['vat'] : '',
                    'type' => $contactType
                ];
                
                $contacts[] = $normalizedContact;
            }
        }

        return $contacts;
    }

    /**
     * Get single contact by ID - using exact same structure as contact_edit.php
     *
     * @param   integer  $contactId  The contact ID
     *
     * @return  array|null  Contact data or null if not found
     */
    public function getContact($contactId)
    {
        // Use the exact same XML structure as contact_edit.php
        $xmlPayload = '<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param>
         <value><string>grupoimpre</string></value>
      </param>
      <param>
         <value><int>2</int></value> <!-- User ID -->
      </param>
      <param>
         <value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value>
      </param>
      <param>
         <value><string>res.partner</string></value> <!-- Model -->
      </param>
      <param>
         <value><string>search_read</string></value> <!-- Method -->
      </param>
     <param>
      <value>
        <array>
          <data>
            <value>
              <array>
                <data>
                  <value>
                    <array>
                      <data>
                        <value><string>id</string></value>
                        <value><string>=</string></value>
                        <value><int>'.$contactId.'</int></value>
                      </data>
                    </array>
                  </value>
                </data>
              </array>
            </value>
          </data>
        </array>
      </value>
    </param>
    <param>
         <value>
            <struct> <!-- Specify fields to retrieve -->
               <member>
                  <name>fields</name>
                  <value>
                     <array>
                        <data>
                           <value><string>type</string></value>
                           <value><string>name</string></value>
                           <value><string>complete_name</string></value>
                           <value><string>vat</string></value>
                           <value><string>street</string></value>
                           <value><string>city</string></value>
                           <value><string>email</string></value>
                           <value><string>phone</string></value>
                           <value><string>mobile</string></value>
                           <value><string>x_studio_agente_de_ventas</string></value>
                           <value><string>display_name</string></value>
                           <value><string>child_ids</string></value>
                        </data>
                     </array>
                  </value>
               </member>
            </struct>
         </value>
      </param>
    </params>
</methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        
        if (!$result) {
            return null;
        }

        $contacts = $this->parseContactsResponse($result);
        return !empty($contacts) ? $contacts[0] : null;
    }

    /**
     * Get child contacts by parent ID
     *
     * @param   integer  $parentId  The parent contact ID
     *
     * @return  array  Array of child contacts
     */
    public function getChildContacts($parentId)
    {
        // Use the exact same XML structure as get_contacts_by_parent_id.php
        $xmlPayload = '<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param>
         <value><string>grupoimpre</string></value>
      </param>
      <param>
         <value><int>2</int></value> <!-- User ID -->
      </param>
      <param>
         <value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value>
      </param>
      <param>
         <value><string>res.partner</string></value> <!-- Model -->
      </param>
      <param>
         <value><string>search_read</string></value> <!-- Method -->
      </param>
     <param>
      <value>
        <array>
          <data>
            <value>
              <array>
                <data>
                  <value>
                    <array>
                      <data>
                        <value><string>parent_id</string></value>
                        <value><string>=</string></value>
                        <value><int>'.$parentId.'</int></value>
                      </data>
                    </array>
                  </value>
                </data>
              </array>
            </value>
          </data>
        </array>
      </value>
    </param>
    <param>
         <value>
            <struct> <!-- Specify fields to retrieve -->
               <member>
                  <name>fields</name>
                  <value>
                     <array>
                        <data>
                           <value><string>type</string></value>
                           <value><string>name</string></value>
                           <value><string>complete_name</string></value>
                           <value><string>vat</string></value>
                           <value><string>street</string></value>
                           <value><string>city</string></value>
                           <value><string>email</string></value>
                           <value><string>phone</string></value>
                           <value><string>mobile</string></value>
                           <value><string>x_studio_agente_de_ventas</string></value>
                           <value><string>display_name</string></value>
                           <value><string>child_ids</string></value>
                        </data>
                     </array>
                  </value>
               </member>
            </struct>
         </value>
      </param>
    </params>
</methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        
        if (!$result) {
            return [];
        }

        return $this->parseContactsResponse($result);
    }

    /**
     * Create new contact - using exact same structure as registrar_contacto.php
     *
     * @param   array  $contactData  The contact data
     *
     * @return  mixed  The contact ID on success, false on failure
     */
    public function createContact($contactData)
    {
        // Handle parent_id for child contacts
        $parentIdXml = '';
        if (isset($contactData['parent_id']) && (int)$contactData['parent_id'] > 0) {
            $parentIdXml = '<member>
                <name>parent_id</name>
                <value><int>' . (int)$contactData['parent_id'] . '</int></value>
            </member>';
        }
        
        // Use the exact same XML structure as registrar_contacto.php
        $xmlPayload = '<?xml version="1.0"?>
    <methodCall>
        <methodName>execute_kw</methodName>
        <params>
            <param>
                <value>
                    <string>grupoimpre</string>
                </value>
            </param>
            <param>
                <value>
                    <int>2</int>
                </value>
            </param>
            <param>
                <value>
                    <string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string>
                </value>
            </param>
            <param>
                <value>
                    <string>res.partner</string>
                </value>
            </param>
            <param>
                <value>
                    <string>create</string>
                </value>
            </param>
            <param>
                <value>
                    <array>
                        <data>
                            <value>
                                <struct>
                                    <member>
                                        <name>name</name>
                                        <value><string>' . htmlspecialchars($contactData['name'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>type</name>
                                        <value><string>' . htmlspecialchars($contactData['type'] ?? 'contact', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>email</name>
                                        <value><string>' . htmlspecialchars($contactData['email'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>street</name>
                                        <value><string>' . htmlspecialchars($contactData['street'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>vat</name>
                                        <value><string>' . htmlspecialchars($contactData['vat'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>phone</name>
                                        <value><string>' . htmlspecialchars($contactData['phone'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>mobile</name>
                                        <value><string>' . htmlspecialchars($contactData['mobile'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>x_studio_agente_de_ventas</name>
                                        <value><string>' . htmlspecialchars($contactData['x_studio_agente_de_ventas'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    <member>
                                        <name>city</name>
                                        <value><string>' . htmlspecialchars($contactData['city'] ?? '', ENT_XML1, 'UTF-8') . '</string></value>
                                    </member>
                                    ' . $parentIdXml . '
                                </struct>
                            </value>
                        </data>
                    </array>
                </value>
            </param>
        </params>
    </methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        return $this->parseCreateResponse($result);
    }

    /**
     * Update existing contact
     *
     * @param   integer  $contactId    The contact ID
     * @param   array    $contactData  The contact data
     *
     * @return  boolean  True on success, false on failure
     */
    public function updateContact($contactId, $contactData)
    {
        $xmlPayload = '<?xml version="1.0"?>
        <methodCall>
            <methodName>execute_kw</methodName>
            <params>
                <param><value><string>grupoimpre</string></value></param>
                <param><value><int>2</int></value></param>
                <param><value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value></param>
                <param><value><string>res.partner</string></value></param>
                <param><value><string>write</string></value></param>
                <param>
                    <value>
                        <array>
                            <data>
                                <value>
                                    <array>
                                        <data>
                                            <value><int>' . $contactId . '</int></value>
                                        </data>
                                    </array>
                                </value>
                                <value>
                                    <struct>
                                        ' . $this->buildContactXmlFields($contactData) . '
                                    </struct>
                                </value>
                            </data>
                        </array>
                    </value>
                </param>
            </params>
        </methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        return $result !== false;
    }

    /**
     * Delete contact
     *
     * @param   integer  $contactId  The contact ID
     *
     * @return  boolean  True on success, false on failure
     */
    public function deleteContact($contactId)
    {
        $xmlPayload = '<?xml version="1.0"?>
        <methodCall>
            <methodName>execute_kw</methodName>
            <params>
                <param><value><string>grupoimpre</string></value></param>
                <param><value><int>2</int></value></param>
                <param><value><string>2386bb5ae66c7fd9022feaf82148680c4cf4ce3b</string></value></param>
                <param><value><string>res.partner</string></value></param>
                <param><value><string>unlink</string></value></param>
                <param>
                    <value>
                        <array>
                            <data>
                                <value>
                                    <array>
                                        <data>
                                            <value><int>' . $contactId . '</int></value>
                                        </data>
                                    </array>
                                </value>
                            </data>
                        </array>
                    </value>
                </param>
            </params>
        </methodCall>';

        $result = $this->executeOdooCall($xmlPayload);
        return $result !== false;
    }

    /**
     * Build XML fields for contact data
     *
     * @param   array  $contactData  The contact data
     *
     * @return  string  The XML fields
     */
    private function buildContactXmlFields($contactData)
    {
        $fields = '';
        $fieldMap = [
            'name' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'street' => 'street',
            'city' => 'city',
            'vat' => 'vat',
            'type' => 'type',
            'x_studio_agente_de_ventas' => 'x_studio_agente_de_ventas'
        ];

        foreach ($fieldMap as $xmlField => $dataField) {
            if (isset($contactData[$dataField]) && $contactData[$dataField] !== '') {
                $value = htmlspecialchars($contactData[$dataField], ENT_XML1, 'UTF-8');
                $fields .= '<member>
                    <name>' . $xmlField . '</name>
                    <value><string>' . $value . '</string></value>
                </member>';
            }
        }

        return $fields;
    }

    /**
     * Parse contacts response from Odoo
     *
     * @param   mixed  $result  The API response
     *
     * @return  array  Array of contacts
     */
    private function parseContactsResponse($result)
    {
        if (!$result || !isset($result['params']['param']['value']['array']['data']['value'])) {
            return [];
        }

        $contacts = [];
        $values = $result['params']['param']['value']['array']['data']['value'];

        // Handle single contact response
        if (isset($values['struct'])) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if (!isset($value['struct']['member'])) {
                continue;
            }

            $contact = [];
            foreach ($value['struct']['member'] as $member) {
                $fieldName = $member['name'];
                $fieldValue = '';
                
                if (isset($member['value']['string'])) {
                    $fieldValue = $member['value']['string'];
                } elseif (isset($member['value']['int'])) {
                    $fieldValue = (string)$member['value']['int'];
                }
                
                $contact[$fieldName] = $fieldValue;
            }
            
            $contacts[] = $contact;
        }

        return $contacts;
    }

    /**
     * Parse create response from Odoo
     *
     * @param   mixed  $result  The API response
     *
     * @return  mixed  The contact ID on success, false on failure
     */
    private function parseCreateResponse($result)
    {
        if (!$result || !isset($result['params']['param']['value']['int'])) {
            return false;
        }

        return $result['params']['param']['value']['int'];
    }
}