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
use Joomla\CMS\MVC\Model\ItemModel;
use Grimpsa\Component\OdooContacts\Site\Helper\OdooHelper;

/**
 * Supplier model for the Odoo Contacts component.
 */
class SupplierModel extends ItemModel
{
    /**
     * Method to get supplier data.
     *
     * @param   integer  $pk  The id of the supplier.
     *
     * @return  object|boolean  Supplier object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('supplier.id');

        if ($pk > 0) {
            try {
                $helper = new OdooHelper();
                $supplier = $helper->getSupplierById($pk);
                
                if ($supplier) {
                    // Convert array to object for consistency with Joomla patterns
                    return (object) $supplier;
                }
            } catch (Exception $e) {
                Factory::getApplication()->enqueueMessage('Error getting supplier: ' . $e->getMessage(), 'error');
            }
        }

        return false;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load state from the request
        $pk = $app->input->getInt('id');
        $this->setState('supplier.id', $pk);
    }
}

