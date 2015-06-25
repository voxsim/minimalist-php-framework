<?php

require_once __DIR__.'/../vendor/autoload.php';

use Minima\Builder\TwigBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ApplicationIntegrationTest extends \PHPUnit_Framework_TestCase {
  private $application;
  private $logger;

  public function __construct()
  {
    $this->logger = new TestLogger();

    $this->application = $this->createApplication($this->logger);
  }

  public function testNotFoundHandling()
  {
    $response = $this->application->handle(new Request());

    $this->assertEquals('Something went wrong! (No route found for "GET /")', $response->getContent());
  }
  
  public function testRoute()
  {
    $request = Request::create('/hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testTwig()
  {
    $request = Request::create('/twig_hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon' . "\n", $response->getContent());
  }
  
  public function testCaching()
  {
    $request = Request::create('/rand_hello/Simon');

    $response1 = $this->application->handle($request);
    $response2 = $this->application->handle($request);

    $this->assertEquals($response1->getContent(), $response2->getContent());
  }
  
  public function testLogging()
  {
    $request = Request::create('/log_hello/Simon');

    $this->application->handle($request);
    $messages = $this->logger->getMessages();

    $this->assertEquals('Message from controller', $messages[0][1]);
  }

  private function createApplication(LoggerInterface $logger)
  {
    $configuration = array(
			  'twig.path' => __DIR__.'/views',
			  'cache.path' =>  __DIR__.'/cache',
			);

    $dispatcher = new EventDispatcher();
    $tokenStorage = new TokenStorage();
    $routeCollection = $this->createRouteCollection($configuration, $this->logger);
    return ApplicationFactory::build($dispatcher, $routeCollection, $configuration, $tokenStorage);
  }

  public function createRouteCollection(array $configuration, LoggerInterface $logger) {
    $routeCollection = new RouteCollection();

    $routeCollection->add('hello', new route('/hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name;
      }
    )));
    
    $routeCollection->add('twig_hello', new Route('/twig_hello/{name}', array(
      'name' => 'World',
      '_controller' => function ($name) use($configuration) {
	$twig = TwigBuilder::build($configuration);
	return $twig->render('hello.twig', array('name' => $name));
      }
    )));

    $routeCollection->add('rand_hello', new route('/rand_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name . ' ' . rand();
      }
    )));

    $routeCollection->add('log_hello', new route('/log_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) use($logger) {
        $logger->info('Message from controller'); 
      }
    )));

    return $routeCollection;
  }
}
