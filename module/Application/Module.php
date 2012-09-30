<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\Storage\StorageInterface;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\Adapter\MemcachedOptions;
use Zend\Session\SessionManager;
use Zend\Session\SaveHandler\Cache;

class Module
{
    public function onBootstrap($event)
    {
        $event->getApplication()->getServiceManager()->get('translator');
        $eventManager = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Model\AccountTable' => function($serviceManager) {
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $table = new AccountTable($dbAdapter);
                    return $table;
                },
                'Zend\Db\Adapter\Adapter' => function($serviceManager) {
                    $config = $serviceManager->get('config');
                    $config = $config['db'];
                    $dbAdapter = new DbAdapter($config);
                    return $dbAdapter;
                },
                'Zend\Authentication\Adapter\DbTable' => function($serviceManager) {
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $authAdapter = new AuthAdapter($dbAdapter);
                    return $authAdapter;
                },
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {
                    $config = $serviceManager->get('config');
                    $config = $config['memcached'];
                    $memcachedOptions = new MemcachedOptions($config);
                    $memcachedStorage = new Memcached($memcachedOptions);
                    $authService = new AuthenticationService();
                    $memcachedSaveHandler = new \Zend\Session\SaveHandler\Cache($memcachedStorage);
                    $sessionManager = new SessionManager(null, null, $memcachedSaveHandler);
                    $sessionStorage = new SessionStorage(null, null, $sessionManager);
                    $authService->setStorage($sessionStorage);
                    return $authService;
                },
            ),
        );
    }

}
