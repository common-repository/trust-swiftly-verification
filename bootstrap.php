<?php

use TrustswiftlyVerification\Core\Autoloader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

$constants = require_once __DIR__ . '/config/constants.php';

foreach ($constants as $key => $value) {
    if (! defined($key)) {
        define($key, $value);
    }
}

$helpersFile = __DIR__ . '/src/helpers/functions.php';
if (file_exists($helpersFile)) {
    require_once($helpersFile);
}

require_once __DIR__ . '/core/Autoloader.php';

$loader = new Autoloader();
$autoloads = ts_config('autoload.psr4', []);
$loader->setNamespacesConfig($autoloads);

$loader->register();