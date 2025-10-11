<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Routing class from com_odoocontacts
 */
class Router extends RouterView
{
    /**
     * The constructor for this router
     *
     * @param   SiteApplication  $app   The application object
     * @param   AbstractMenu     $menu  The menu object to work with
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $contacts = new RouterViewConfiguration('contacts');
        $this->registerView($contacts);

        $contact = new RouterViewConfiguration('contact');
        $contact->setKey('id');
        $this->registerView($contact);

        // Supplier routes
        $suppliers = new RouterViewConfiguration('suppliers');
        $this->registerView($suppliers);

        $supplier = new RouterViewConfiguration('supplier');
        $supplier->setKey('id');
        $this->registerView($supplier);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Method to get the segment(s) for a contact
     *
     * @param   string  $id     ID of the contact to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getContactSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for contacts
     *
     * @param   string  $id     ID of the contacts to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getContactsSegment($id, $query)
    {
        return [];
    }

    /**
     * Method to get the id for a contact
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getContactId($segment, $query)
    {
        return (int) $segment;
    }

    /**
     * Method to get the id for contacts
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getContactsId($segment, $query)
    {
        return 1;
    }

    /**
     * Method to get the segment(s) for a supplier
     *
     * @param   string  $id     ID of the supplier to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getSupplierSegment($id, $query)
    {
        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for suppliers
     *
     * @param   string  $id     ID of the suppliers to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getSuppliersSegment($id, $query)
    {
        return [];
    }

    /**
     * Method to get the id for a supplier
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getSupplierId($segment, $query)
    {
        return (int) $segment;
    }

    /**
     * Method to get the id for suppliers
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getSuppliersId($segment, $query)
    {
        return 1;
    }
}