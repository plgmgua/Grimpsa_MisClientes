<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Administrator\View\ConnectionTest;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Grimpsa\Component\OdooContacts\Administrator\Helper\OdooConnectHelper;

/**
 * View class for the Odoo connection test.
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Test result
     *
     * @var    array
     */
    protected $testResult = null;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Perform the connection test
        $this->testResult = OdooConnectHelper::testConnection();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_ODOOCONTACTS_CONNECTION_TEST'), 'plug');
        ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_odoocontacts');
        ToolbarHelper::preferences('com_odoocontacts');
    }
}

