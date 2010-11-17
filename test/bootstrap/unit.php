<?php

if(!isset($_SERVER['SYMFONY'])) {
    $_SERVER['SYMFONY'] = dirname(__FILE__).'/../../../../lib/vendor/symfony/lib/';
    if(!file_exists($_SERVER['SYMFONY']))
        throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$configuration = new sfProjectConfiguration(dirname(__FILE__).'/../fixtures/project');
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

function sfRedisPlugin_autoload_again($class)
{
  $autoload = sfSimpleAutoload::getInstance();
  $autoload->reload();
  return $autoload->autoload($class);
}
spl_autoload_register('sfRedisPlugin_autoload_again');

if (file_exists($config = dirname(__FILE__).'/../../config/sfRedisPluginConfiguration.class.php'))
{
  require_once $config;
  $plugin_configuration = new sfRedisPluginConfiguration($configuration, dirname(__FILE__).'/../..', 'sfRedisPlugin');
}
else
{
  $plugin_configuration = new sfPluginConfigurationGeneric($configuration, dirname(__FILE__).'/../..', 'sfRedisPlugin');
}

require_once dirname(__FILE__).'/../fixtures/objects.php';
require_once dirname(__FILE__).'/../../lib/annotations.php';