<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Grimpsa\Component\OdooContacts\Site\Helper\OdooHelper;

/**
 * Suppliers model for the Odoo Contacts component.
 */
class SuppliersModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'name', 'email', 'phone', 'mobile', 'ref', 'payment_terms'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a list of suppliers.
     *
     * @return  array  An array of suppliers.
     */
    public function getItems()
    {
        $user = Factory::getUser();
        
        if ($user->guest) {
            return [];
        }

        try {
            $helper = new OdooHelper();
            
            // Get pagination and search parameters
            $limitstart = $this->getStart();
            $limit = $this->getState('list.limit', 15);
            $search = $this->getState('filter.search', '');
            
            // Get all suppliers (no agent filter for suppliers)
            $suppliers = $helper->getAllSuppliers();
            
            // Ensure we return a proper array
            if (!is_array($suppliers)) {
                return [];
            }
            
            // Validate and normalize each supplier
            $validSuppliers = [];
            foreach ($suppliers as $supplier) {
                if (is_array($supplier)) {
                    // Ensure all expected fields exist as strings
                    $normalizedSupplier = [
                        'id' => isset($supplier['id']) ? (string)$supplier['id'] : '0',
                        'name' => isset($supplier['name']) && is_string($supplier['name']) ? $supplier['name'] : '',
                        'ref' => isset($supplier['ref']) && is_string($supplier['ref']) ? $supplier['ref'] : '',
                        'email' => isset($supplier['email']) && is_string($supplier['email']) ? $supplier['email'] : '',
                        'phone' => isset($supplier['phone']) && is_string($supplier['phone']) ? $supplier['phone'] : '',
                        'mobile' => isset($supplier['mobile']) && is_string($supplier['mobile']) ? $supplier['mobile'] : '',
                        'vat' => isset($supplier['vat']) && is_string($supplier['vat']) ? $supplier['vat'] : '',
                        'payment_terms' => isset($supplier['payment_terms']) && is_string($supplier['payment_terms']) ? $supplier['payment_terms'] : ''
                    ];
                    
                    // Apply search filter on server side
                    if (!empty($search)) {
                        $searchLower = strtolower($search);
                        // Ensure all fields are strings before strtolower()
                        $name = is_string($normalizedSupplier['name']) ? $normalizedSupplier['name'] : '';
                        $ref = is_string($normalizedSupplier['ref']) ? $normalizedSupplier['ref'] : '';
                        $email = is_string($normalizedSupplier['email']) ? $normalizedSupplier['email'] : '';
                        $phone = is_string($normalizedSupplier['phone']) ? $normalizedSupplier['phone'] : '';
                        $mobile = is_string($normalizedSupplier['mobile']) ? $normalizedSupplier['mobile'] : '';
                        
                        $nameMatch = strpos(strtolower($name), $searchLower) !== false;
                        $refMatch = strpos(strtolower($ref), $searchLower) !== false;
                        $emailMatch = strpos(strtolower($email), $searchLower) !== false;
                        $phoneMatch = strpos(strtolower($phone), $searchLower) !== false;
                        $mobileMatch = strpos(strtolower($mobile), $searchLower) !== false;
                        
                        if ($nameMatch || $refMatch || $emailMatch || $phoneMatch || $mobileMatch) {
                            $validSuppliers[] = $normalizedSupplier;
                        }
                    } else {
                        $validSuppliers[] = $normalizedSupplier;
                    }
                }
            }
            
            // Apply pagination
            $total = count($validSuppliers);
            $paginatedSuppliers = array_slice($validSuppliers, $limitstart, $limit);
            
            return $paginatedSuppliers;
            
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage('Error connecting to Odoo: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Method to get the total number of suppliers.
     *
     * @return  integer  The total number of suppliers.
     */
    public function getTotal()
    {
        $user = Factory::getUser();
        
        if ($user->guest) {
            return 0;
        }

        try {
            $helper = new OdooHelper();
            $search = $this->getState('filter.search', '');
            
            // Get all suppliers
            $allSuppliers = $helper->getAllSuppliers();
            
            if (!is_array($allSuppliers)) {
                return 0;
            }
            
            // Apply search filter to get accurate count
            if (!empty($search)) {
                $filteredCount = 0;
                foreach ($allSuppliers as $supplier) {
                    if (is_array($supplier)) {
                        $searchLower = strtolower($search);
                        // Ensure all fields are strings before strtolower()
                        $name = (isset($supplier['name']) && is_string($supplier['name'])) ? strtolower($supplier['name']) : '';
                        $ref = (isset($supplier['ref']) && is_string($supplier['ref'])) ? strtolower($supplier['ref']) : '';
                        $email = (isset($supplier['email']) && is_string($supplier['email'])) ? strtolower($supplier['email']) : '';
                        $phone = (isset($supplier['phone']) && is_string($supplier['phone'])) ? strtolower($supplier['phone']) : '';
                        $mobile = (isset($supplier['mobile']) && is_string($supplier['mobile'])) ? strtolower($supplier['mobile']) : '';
                        
                        if (strpos($name, $searchLower) !== false || 
                            strpos($ref, $searchLower) !== false ||
                            strpos($email, $searchLower) !== false || 
                            strpos($phone, $searchLower) !== false || 
                            strpos($mobile, $searchLower) !== false) {
                            $filteredCount++;
                        }
                    }
                }
                return $filteredCount;
            }
            
            return count($allSuppliers);
            
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = 'name', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit', 15), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Search state
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        // Ordering
        $this->setState('list.ordering', $ordering);
        $this->setState('list.direction', $direction);
    }

    /**
     * Method to get a Pagination object for the data set.
     *
     * @return  Pagination  A Pagination object for the data set.
     */
    public function getPagination()
    {
        if (empty($this->cache['pagination'])) {
            $this->cache['pagination'] = new Pagination(
                $this->getTotal(),
                $this->getStart(),
                $this->getState('list.limit', 15)
            );
        }

        return $this->cache['pagination'];
    }
}

