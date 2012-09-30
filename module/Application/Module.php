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
use Zend\Session\SaveHandler\Cache as SaveHandlerCache;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;
use Zend\Cache\Exception\RuntimeException;

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
                'Application\Model\SessionTable' => function($serviceManager) {
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\SessionTable($dbAdapter);
                    return $table;
                },
                'Zend\Db\Adapter\Adapter' => function($serviceManager) {
                    $config = $serviceManager->get('config');
                    $dbConfig = $config['db'];
                    $dbAdapter = new DbAdapter($dbConfig);
                    return $dbAdapter;
                },
                'Zend\Authentication\Adapter\DbTable' => function($serviceManager) {
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $authAdapter = new AuthAdapter($dbAdapter);
                    return $authAdapter;
                },
                'Zend\Authentication\AuthenticationService' => function($serviceManager) {

                    $config = $serviceManager->get('config');

                    /*try {
                        $memcachedConfig = $config['memcached'];
                        $memcachedOptions = new MemcachedOptions($memcachedConfig);
                        $memcachedStorage = new Memcached($memcachedOptions);
                        $authService = new AuthenticationService();
                        $memcachedSaveHandler = new SaveHandlerCache($memcachedStorage);
                        $sessionManager = new SessionManager(null, null, $memcachedSaveHandler);
                        $sessionStorage = new SessionStorage('Zend_Auth', 'storage', $sessionManager);
                        $authService->setStorage($sessionStorage);

                    } catch (RuntimeException $e) {*/

                        // If memcached sessions fail, fall back to database sessions

                        $authService = new AuthenticationService();

                        $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');

                        $sessionTableGatewayOptions = new DbTableGatewayOptions();
                        $sessionTableGatewayOptions->setDataColumn('data')
                                ->setIdColumn('id')
                                ->setLifetimeColumn('lifetime')
                                ->setModifiedColumn('modified')
                                ->setNameColumn('name');

                        $sessionTableGateway = new TableGateway('sessions', $dbAdapter);
                        $dbSaveHandler = new DbTableGateway($sessionTableGateway, $sessionTableGatewayOptions);



                        $storage = new \Zend\Session\Storage\SessionStorage();


                        //$saveHandler = new \Zend\Session\SaveHandler\


                        $sessionManager = new SessionManager(null, $storage, $dbSaveHandler);

                        $sessionStorage = new SessionStorage('Zend_Auth', 'storage', $sessionManager);
/*
                        //$sessionStorage->write('abc');
                         */

                        $authService = new AuthenticationService();
                        //$authService->setStorage($sessionStorage);

                    //}

                    return $authService;
                },
            ),
        );
    }

}
