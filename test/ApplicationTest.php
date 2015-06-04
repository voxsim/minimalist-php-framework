<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase {
  private $application;

  public function __construct() {
      $configuration = array(
			    'charset' => 'UTF-8',
			    'debug' => false,
			    'twig.path' => __DIR__.'/views',
			    'cache.path' =>  __DIR__.'/cache',
			    'cache.page' => 10
			   );
      $this->application = new Application($configuration);
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
}
