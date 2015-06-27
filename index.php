<?php

require_once __DIR__.'/bootstrap.php';

use Minima\Builder\DatabaseBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

// Configuration
$configuration = array();

// Stateful Componenents
$dispatcher = new EventDispatcher();

$storage = new NativeSessionStorage();
$session = new Session($storage);

$database = DatabaseBuilder::getConnection();

// Loading routes
$routeCollection = new RouteCollection();

// Add your routes here

// Build Application
$application = ApplicationFactory::build($dispatcher, $routeCollection, $configuration);

// Handle the request
$request = Request::createFromGlobals();

$response = $application->handle($request);
$response->send();
