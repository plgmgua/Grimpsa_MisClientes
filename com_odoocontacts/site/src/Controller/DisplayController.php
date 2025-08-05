<?php
/**
 * @package     Grimpsa.Site
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Odoo Contacts Component Controller
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     */
    protected $default_view = 'contacts';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types
     *
     * @return  static  This object to support chaining.
     */
    public function display($cachable = false, $urlparams = [])
    {
        $user = Factory::getUser();
        
        // Check if user is logged in
        if ($user->guest) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_LOGIN_REQUIRED'), 'warning');
            $this->app->redirect(Route::_('index.php?option=com_users&view=login'));
            return $this;
        }

        // Get the document object
        $document = $this->app->getDocument();
        
        // Set the default view if not set
        $vName = $this->input->get('view', $this->default_view);
        $layout = $this->input->get('layout', 'default');
        $this->input->set('view', $vName);
        
        // Handle contact edit layout
        if ($vName === 'contact' && $layout === 'edit') {
            $this->input->set('layout', 'edit');
            
            // For new contacts, ensure ID is 0
            $contactId = $this->input->getInt('id');
            if ($contactId === null || $contactId < 0) {
                $contactId = 0;
            }
            $this->input->set('id', $contactId);
        }

        parent::display($cachable, $urlparams);

        return $this;
    }
}