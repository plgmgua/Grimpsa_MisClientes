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
 * Contacts model for the Odoo Contacts component.
 */
class ContactsModel extends ListModel
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
                'id', 'name', 'email', 'phone', 'mobile', 'city'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a list of contacts.
     *
     * @return  array  An array of contacts.
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
            
            $page = floor($limitstart / $limit) + 1;
            
            $contacts = $helper->getContactsByAgent($user->name, $page, $limit, $search);
            
            // Ensure we return a proper array
            if (!is_array($contacts)) {
                return [];
            }
            
            // Validate and normalize each contact
            $validContacts = [];
            foreach ($contacts as $contact) {
                if (is_array($contact)) {
                    // Ensure all expected fields exist as strings
                    $normalizedContact = [
                        'id' => isset($contact['id']) ? (string)$contact['id'] : '0',
                        'name' => isset($contact['name']) && is_string($contact['name']) ? $contact['name'] : '',
                        'email' => isset($contact['email']) && is_string($contact['email']) ? $contact['email'] : '',
                        'phone' => isset($contact['phone']) && is_string($contact['phone']) ? $contact['phone'] : '',
                        'mobile' => isset($contact['mobile']) && is_string($contact['mobile']) ? $contact['mobile'] : '',
                        'street' => isset($contact['street']) && is_string($contact['street']) ? $contact['street'] : '',
                        'city' => isset($contact['city']) && is_string($contact['city']) ? $contact['city'] : '',
                        'vat' => isset($contact['vat']) && is_string($contact['vat']) ? $contact['vat'] : '',
                        'type' => isset($contact['type']) && is_string($contact['type']) ? $contact['type'] : 'contact'
                    ];
                    
                    $validContacts[] = $normalizedContact;
                }
            }
            
            return $validContacts;
            
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage('Error connecting to Odoo: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Method to get the total number of contacts.
     *
     * @return  integer  The total number of contacts.
     */
    public function getTotal()
    {
        // For now, we'll return a reasonable number since Odoo doesn't easily provide total counts
        return 100;
    }

    /**
     * Method to get a pagination object for the contacts.
     *
     * @return  Pagination  A Pagination object for the contacts.
     */
    public function getPagination()
    {
        // Get the pagination request variables
        $limit = $this->getState('list.limit', 15);
        $limitstart = $this->getState('list.start', 0);

        // Get the total number of contacts
        $total = $this->getTotal();

        // Create the pagination object
        return new Pagination($total, $limitstart, $limit);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = 'name', $direction = 'asc')
    {
        $app = Factory::getApplication();
        
        // Get the pagination request variables
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
}