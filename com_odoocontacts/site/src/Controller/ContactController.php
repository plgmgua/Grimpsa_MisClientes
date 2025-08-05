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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Contact controller class.
 */
class ContactController extends FormController
{
    /**
     * Method to check out an item for editing and redirect to the edit form.
     *
     * @return  boolean  True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $urlVar = null)
    {
        $user = Factory::getUser();
        
        if ($user->guest) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login'));
            return false;
        }

        $contactId = $this->input->getInt('id', 0);
        
        // Redirect to edit layout
        $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
        return true;
    }

    /**
     * Method to add a new record.
     *
     * @return  boolean  True if the record can be added, false if not.
     */
    public function add()
    {
        $user = Factory::getUser();
        
        if ($user->guest) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login'));
            return false;
        }

        // Redirect to edit layout for new contact
        $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=0'));
        return true;
    }

    /**
     * Method to save a contact.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries
        if (!Session::checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
            return false;
        }

        $user = Factory::getUser();
        
        if ($user->guest) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login'));
            return false;
        }

        $model = $this->getModel('Contact');
        $data = $this->input->post->get('jform', [], 'array');
        
        // Handle parent_id for child contacts
        $parentId = $this->input->getInt('parent_id', 0);
        if ($parentId > 0) {
            $data['parent_id'] = $parentId;
        }
        
        // Add the sales agent field
        $data['x_studio_agente_de_ventas'] = $user->name;

        $contactId = $this->input->getInt('id', 0);
        $returnToParent = $this->input->getInt('return_to_parent', 0);
        
        try {
            if ($contactId > 0) {
                $result = $model->updateContact($contactId, $data);
                $message = 'Contacto actualizado exitosamente';
            } else {
                $result = $model->createContact($data);
                $message = 'Contacto creado exitosamente';
                $contactId = $result;
            }

            if ($result !== false) {
                $this->app->enqueueMessage($message, 'success');
                
                // If this is a child contact creation, return to parent
                if ($returnToParent && $parentId > 0) {
                    $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $parentId));
                } else {
                    $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
                }
            } else {
                $this->app->enqueueMessage('Error al guardar el contacto', 'error');
                $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
            }
        } catch (Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
        }

        return true;
    }

    /**
     * Method to apply changes to a contact and stay on the edit form.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function apply()
    {
        // Check for request forgeries
        if (!Session::checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
            return false;
        }

        $user = Factory::getUser();
        
        if ($user->guest) {
            $this->app->enqueueMessage('Debes iniciar sesión para gestionar contactos', 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login'));
            return false;
        }

        $model = $this->getModel('Contact');
        $data = $this->input->post->get('jform', [], 'array');
        
        // Handle parent_id for child contacts
        $parentId = $this->input->getInt('parent_id', 0);
        if ($parentId > 0) {
            $data['parent_id'] = $parentId;
        }
        
        // Add the sales agent field
        $data['x_studio_agente_de_ventas'] = $user->name;

        $contactId = $this->input->getInt('id', 0);
        
        try {
            if ($contactId > 0) {
                $result = $model->updateContact($contactId, $data);
                $message = 'Contacto actualizado exitosamente';
            } else {
                $result = $model->createContact($data);
                $message = 'Contacto creado exitosamente';
                $contactId = $result;
            }

            if ($result !== false) {
                $this->app->enqueueMessage($message, 'success');
                $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
            } else {
                $this->app->enqueueMessage('Error al guardar el contacto', 'error');
                $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
            }
        } catch (Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contact&layout=edit&id=' . $contactId));
        }

        return true;
    }

    /**
     * Method to delete a contact.
     *
     * @return  boolean  True if successful, false otherwise.
     */
    public function delete()
    {
        // Check for request forgeries
        if (!Session::checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
            return false;
        }

        $user = Factory::getUser();
        
        if ($user->guest) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_LOGIN_REQUIRED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login'));
            return false;
        }

        $contactId = $this->input->getInt('id', 0);
        
        if ($contactId <= 0) {
            $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_INVALID_CONTACT'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
            return false;
        }

        $model = $this->getModel('Contact');
        
        try {
            $result = $model->deleteContact($contactId);
            
            if ($result) {
                $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_CONTACT_DELETED_SUCCESS'), 'success');
            } else {
                $this->app->enqueueMessage(Text::_('COM_ODOOCONTACTS_ERROR_DELETE_FAILED'), 'error');
            }
        } catch (Exception $e) {
            $this->app->enqueueMessage('Error: ' . $e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
        return true;
    }

    /**
     * Method to cancel an operation
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     */
    public function cancel($key = null)
    {
        $this->setRedirect(Route::_('index.php?option=com_odoocontacts&view=contacts'));
        return true;
    }
}