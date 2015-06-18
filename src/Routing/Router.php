<?php namespace Minima\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Router implements RouterInterface {
  private $matcher;

  public function __construct($configuration, UrlMatcherInterface $matcher, LoggerInterface $logger = null) {
    $this->matcher = $matcher;
    $this->logger = $logger == null ? new NullLogger() : $logger;
  }

  public function lookup(Request $request) {
    try {
      $parameters = $this->matcher->match($request->getPathInfo());

      $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));

      $request->attributes->add($parameters);
      unset($parameters['_route'], $parameters['_controller']);
      $request->attributes->set('_route_params', $parameters);
    } catch (ResourceNotFoundException $e) {
      $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

      if($referer = $request->headers->get('referer')) {
	$message .= sprintf(' (from "%s")', $referer);
      }

      throw new NotFoundHttpException($message, $e);
    } catch (MethodNotAllowedException $e) {
      $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), implode(', ', $e->getAllowedMethods()));

      throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
    }
  }

  private function parametersToString(array $parameters)
  {
    $pieces = array();
    foreach ($parameters as $key => $val) {
      $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
    }

    return implode(', ', $pieces);
  }
}
