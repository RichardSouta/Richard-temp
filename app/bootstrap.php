<?php

require __DIR__.'/../vendor/autoload.php';
$configurator = new Nette\Configurator();

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP

$configurator->setDebugMode(true);
$configurator->enableDebugger(__DIR__.'/../log', 'riky@souta.cz');
$configurator->setTempDirectory(__DIR__.'/../temp');
define('WWW_DIR', realpath(__DIR__.'/../www'));

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__.'/config/config.neon');
$configurator->addConfig(__DIR__.'/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
