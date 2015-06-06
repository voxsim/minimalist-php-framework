<?php namespace Minima;

use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
 
class Application extends ApplicationDebug 
{
  public function __construct(array $configuration, EventDispatcher $dispatcher, ControllerResolver $resolver, \Minima\Logging\Logger $logger)
  {
    parent::__construct($configuration, $dispatcher, $resolver, $logger);

    $errorHandler = function (HttpKernel\Exception\FlattenException $exception) {
      $msg = 'Something went wrong! ('.$exception->getMessage().')';
   
      return new Response($msg, $exception->getStatusCode());
    };
    $dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));

    $this->httpKernel = new HttpCache($this->httpKernel, new Store($this->configuration['cache.path']));
    $dispatcher->addSubscriber(new \Minima\Cache\SetTtlListener($this->configuration['cache.page']));
  }
}
