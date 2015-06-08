<?php

require_once __DIR__.'/../vendor/autoload.php';

use Minima\Logging\Logger;
use Minima\Routing\Router;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

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

    $this->assertEquals('> GET /log_hello/Simon', $messages[0][1]);
    $this->assertEquals('Matched route "log_hello" (parameters: "name": "Simon", "_controller": "{}", "_route": "log_hello")', $messages[1][1]);
    $this->assertEquals('Message from controller', $messages[2][1]);
  }

  private function createApplication(LoggerInterface $logger)
  {
    $configuration = array(
			  'twig.path' => __DIR__.'/views',
			  'cache.path' =>  __DIR__.'/cache',
			);

    $dispatcher = new EventDispatcher();
    $router = new Router($configuration, $logger);
    $resolver = new ControllerResolver($logger);
    return new \Minima\Application($configuration, $dispatcher, $resolver, $router, $logger);
  }
}
