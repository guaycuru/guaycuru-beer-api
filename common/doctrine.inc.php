<?php

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Util\Shared;

// Config
require_once(__DIR__.'/config.inc.php');

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Create a simple "default" Doctrine ORM configuration for Annotations
$cacheDir = __DIR__.'/../cache';
$modelsDir = __DIR__.'/../models';
$proxyDir = __DIR__.'/../proxies';
$config = ORMSetup::createAttributeMetadataConfiguration(array($modelsDir), CONFIG_DEV_MODE, $proxyDir);
if (CONFIG_DEV_MODE) {
	$config->setAutoGenerateProxyClasses(Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_EVAL);
} else {
	$queryCache = new PhpFilesAdapter('doctrine_queries');
	$config->setQueryCache($queryCache);

	$metadataCache = new PhpFilesAdapter('doctrine_metadata', 0, $cacheDir);
	$config->setMetadataCache($metadataCache);
}
unset($cacheDir, $modelsDir, $proxyDir, $queryCache, $metadataCache);

// DB connection options
$connectionConfig = array(
	'driver' => CONFIG_DB_TYPE,
	'user' => CONFIG_DB_USER,
	'password' => CONFIG_DB_PASS,
	'dbname' => CONFIG_DB_NAME,
	'charset' => CONFIG_DB_CHARSET,
	'driverOptions' => array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.CONFIG_DB_CHARSET
	)
);
if ((defined('CONFIG_DB_SOCKET')) && (!empty(CONFIG_DB_SOCKET)))
	$connectionConfig['unix_socket'] = CONFIG_DB_SOCKET;
elseif ((defined('CONFIG_DB_HOST')) && (!empty(CONFIG_DB_HOST))) {
	$connectionConfig['host'] = CONFIG_DB_HOST;
	if ((defined('CONFIG_DB_PORT')) && (!empty(CONFIG_DB_PORT)))
		$connectionConfig['port'] = CONFIG_DB_PORT;
}

// Create the connection
$connection = Doctrine\DBAL\DriverManager::getConnection($connectionConfig, $config);

// Create the Entity Manager
$_EM = new EntityManager($connection, $config);

// Set the Entity Manager in the Shared class
Shared::_EM($_EM);

// Unset non-global variables
unset($config, $connection);
