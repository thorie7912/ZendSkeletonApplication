<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
define('ZF_CLASS_CACHE', 'data/cache/classes.php.cache'); if (file_exists(ZF_CLASS_CACHE)) require_once ZF_CLASS_CACHE;
// Setup autoloading
include 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(include 'config/application.config.php')->run();

echo "This print is line 13 in index.php.... after Application::init()->run()You will see shutdown errors now...\n<BR><BR><HR> ";