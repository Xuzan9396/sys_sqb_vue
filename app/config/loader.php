<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'Common\Models'  => APP_PATH . '/common/models/',
    'Sys_sqb_vue'        => APP_PATH . '/common/library/',
]);

/**
 * Register module classes
 */
$loader->registerClasses([
    'Sqb\Modules\Api\Module' => APP_PATH . '/modules/api/Module.php',
    'Sqb\Modules\Sys\Module' => APP_PATH . '/modules/sys/Module.php',
    'Sys_sqb_vue\Modules\Cli\Module'      => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
