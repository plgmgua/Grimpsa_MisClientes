<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// Explicitly require the component class to ensure it's loaded
$componentClassFile = __DIR__ . '/../src/Extension/OdooContactsComponent.php';
if (file_exists($componentClassFile)) {
    require_once $componentClassFile;
}

// Explicitly require controller classes to ensure they're loaded
$controllerFile = __DIR__ . '/../src/Controller/DisplayController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
}

// Explicitly require view classes to ensure they're loaded
$dashboardViewFile = __DIR__ . '/../src/View/Dashboard/HtmlView.php';
if (file_exists($dashboardViewFile)) {
    require_once $dashboardViewFile;
}

$connectionTestViewFile = __DIR__ . '/../src/View/ConnectionTest/HtmlView.php';
if (file_exists($connectionTestViewFile)) {
    require_once $connectionTestViewFile;
}

use Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent;

/**
 * The Odoo Contacts service provider.
 */
return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Grimpsa\\Component\\OdooContacts'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Grimpsa\\Component\\OdooContacts'));
        $container->registerServiceProvider(new RouterFactory('\\Grimpsa\\Component\\OdooContacts'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new OdooContactsComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};