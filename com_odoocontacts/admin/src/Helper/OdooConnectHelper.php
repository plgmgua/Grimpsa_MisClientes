<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Helper class for Odoo connection testing and administration
 */
class OdooConnectHelper
{
    /**
     * Test the Odoo connection
     *
     * @return  array  Array with 'success' (boolean) and 'message' (string)
     */
    public static function testConnection()
    {
        $config = ComponentHelper::getParams('com_odoocontacts');
        
        // Get configuration values - handle both field name variations
        $odooUrl = $config->get('odoo_url', 'https://grupoimpre.odoo.com/xmlrpc/2/object');
        // Try both odoo_db and odoo_database
        $odooDb = $config->get('odoo_db') ?: $config->get('odoo_database', 'grupoimpre');
        // Try both odoo_user_id and odoo_username (convert username to ID if needed)
        $odooUserId = $config->get('odoo_user_id') ?: $config->get('odoo_username', '2');
        // Get API key from config, fallback to default if empty (password fields may be empty even if set)
        $odooApiKey = $config->get('odoo_api_key', '');
        if (empty($odooApiKey)) {
            // Use default API key from manifest if config is empty
            $odooApiKey = '2386bb5ae66c7fd9022feaf82148680c4cf4ce3b';
        }
        
        // Validate required parameters
        if (empty($odooUrl) || empty($odooDb) || empty($odooApiKey)) {
            return [
                'success' => false,
                'message' => 'Missing required Odoo configuration parameters'
            ];
        }
        
        // For Odoo 19, test connection using API key authentication with execute_kw
        // Test with a simple search_count on res.partner
        $testXmlPayload = '<?xml version="1.0"?>
<methodCall>
   <methodName>execute_kw</methodName>
   <params>
      <param>
         <value><string>' . htmlspecialchars($odooDb, ENT_XML1, 'UTF-8') . '</string></value>
      </param>
      <param>
         <value><int>' . (int)$odooUserId . '</int></value>
      </param>
      <param>
         <value><string>' . htmlspecialchars($odooApiKey, ENT_XML1, 'UTF-8') . '</string></value>
      </param>
      <param>
         <value><string>res.partner</string></value>
      </param>
      <param>
         <value><string>search_count</string></value>
      </param>
      <param>
         <value><array><data></data></array></value>
      </param>
   </params>
</methodCall>';
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $odooUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $testXmlPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml',
            ],
        ]);
        
        $testResponse = curl_exec($curl);
        $testHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $testError = curl_error($curl);
        curl_close($curl);
        
        if ($testHttpCode !== 200 || !$testResponse) {
            return [
                'success' => false,
                'message' => 'Failed to execute test query on Odoo. HTTP Code: ' . $testHttpCode . ($testError ? ' - ' . $testError : '')
            ];
        }
        
        // Parse the response
        $xml = @simplexml_load_string($testResponse);
        if (!$xml) {
            return [
                'success' => false,
                'message' => 'Invalid XML response from Odoo server. Please verify your Odoo 19 API endpoint is correct.'
            ];
        }
        
        // Check for Odoo error in response (Odoo 19 may return fault responses)
        if (isset($xml->fault)) {
            $faultString = (string)$xml->fault->value->struct->member[1]->value->string;
            return [
                'success' => false,
                'message' => 'Odoo API error: ' . $faultString
            ];
        }
        
        // If we get here, the connection is working
        return [
            'success' => true,
            'message' => 'Successfully connected to Odoo ' . $odooDb . ' database'
        ];
    }
}

