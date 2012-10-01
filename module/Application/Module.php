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

        $eventManager->attach('finish', array($this, 'writeCloseSession'));
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

    /**
     * Workaround for issue https://github.com/zendframework/zf2/issues/2628
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function writeCloseSession(\Zend\Mvc\MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();

        if (!$serviceManager->has('Zend\Session\SessionManager')) {
            return;
        }

        $sessionManager = $serviceManager->get('Zend\Session\SessionManager');
        $saveHandler = $sessionManager->getSaveHandler();

        // Only if the saveHandler is DbTable (not memcached) do we want to
        // apply the bug workaround writeClose() call here.
        if ('Zend\Session\SaveHandler\DbTableGateway' == get_class($saveHandler)) {
            $sessionManager->writeClose();
        }
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                /*'Application\Model\SessionTable' => function($serviceManager) {
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $table = new Model\SessionTable($dbAdapter);
                    return $table;
                },*/
                'Zend\Authentication\Storage\Session' => function($serviceManager) {

                    $config = $serviceManager->get('config');

                    $sessionManager = new SessionManager();

                    try {

                        $memcachedConfig = $config['memcached'];
                        $memcachedOptions = new MemcachedOptions($memcachedConfig);
                        $memcachedStorage = new Memcached($memcachedOptions);

                        // Calling getAvailableSpace() before starting the session will prevent the
                        // session from being stored before getting a chance to fall-back to DB sessions.
                        // This will prevent memcached as being set as the session handler when it
                        // is unavailable (causing various problems, such as __destruct() exceptions).
                        // The trick is, getAvailableSpace will throw an exception before the
                        // subsequent session code has a chance to lock in memcached as the handler.
                        $space = $memcachedStorage->getAvailableSpace();

                        if ($space < 0) {
                            throw new RuntimeException('Memcached is out of space');
                        }

                        $memcachedSaveHandler = new SaveHandlerCache($memcachedStorage);
                        $sessionManager->setSaveHandler($memcachedSaveHandler);
                        $sessionStorage = new SessionStorage('MemcachedSession', null, $sessionManager);

                    } catch (RuntimeException $e) {

                        // If memcached sessions fail, fall back to database sessions

                        $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                        $sessionTableGatewayOptions = new DbTableGatewayOptions();
                        $sessionTableGatewayOptions->setDataColumn('data')
                                ->setIdColumn('id')
                                ->setLifetimeColumn('lifetime')
                                ->setModifiedColumn('modified')
                                ->setNameColumn('name');

                        $sessionTableGateway = new TableGateway('sessions', $dbAdapter);
                        $dbSaveHandler = new DbTableGateway($sessionTableGateway, $sessionTableGatewayOptions);
                        $sessionManager->setSaveHandler($dbSaveHandler);
                        $sessionStorage = new SessionStorage('DbSession', 'storage', $sessionManager);


                    }

                    $serviceManager->setService('Zend\Session\SessionManager', $sessionManager);


                    return $sessionStorage;

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

                    $authService = new AuthenticationService();
                    $sessionStorage = $serviceManager->get('Zend\Authentication\Storage\Session');
                    $authService->setStorage($sessionStorage);

                    return $authService;
                },
            ),
        );
    }

}
