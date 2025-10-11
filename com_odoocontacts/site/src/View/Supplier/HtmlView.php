<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Site\View\Supplier;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Component\ComponentHelper;

/**
 * HTML Supplier View class for the Odoo Contacts component
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The supplier item
     *
     * @var    object
     */
    protected $item;

    /**
     * The component parameters
     *
     * @var    Registry
     */
    protected $params;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->params = ComponentHelper::getParams('com_odoocontacts');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
            return false;
        }

        $this->addToolbar();
        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        // Set the title
        $isNew = (empty($this->item) || empty($this->item->id));
        $title = $isNew ? Text::_('COM_ODOOCONTACTS_SUPPLIER_NEW') : Text::_('COM_ODOOCONTACTS_SUPPLIER_EDIT');
        $this->document->setTitle($title);
        
        // Add CSS and JS using WebAssetManager
        HTMLHelper::_('bootstrap.framework');
        HTMLHelper::_('behavior.formvalidator');
        
        $wa = $this->document->getWebAssetManager();
        $wa->registerAndUseStyle('com_odoocontacts.contacts', 'media/com_odoocontacts/css/contacts.css', [], ['version' => 'auto']);
        $wa->registerAndUseScript('com_odoocontacts.contacts', 'media/com_odoocontacts/js/contacts.js', [], ['version' => 'auto']);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();
        $isNew = (empty($this->item) || empty($this->item->id));
        
        $title = $isNew ? Text::_('COM_ODOOCONTACTS_SUPPLIER_NEW') : $this->item->name;

        if ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);
    }
}

