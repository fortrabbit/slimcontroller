<?php

/*
 * This file is part of SlimController.
 *
 * @author Ulrich Kautz <uk@fortrabbit.de>
 * @copyright 2012 Ulrich Kautz
 * @version 0.1.2
 * @package SlimController
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SlimController;

/**
 * Extended Slim base
 */
class Slim extends \Slim\Slim
{

    /**
     * @var array
     */
    protected static $ALLOWED_HTTP_METHODS = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD');

    /**
     * Add multiple controller based routes
     *
     * Simple Format
     * <code>
     * $app->addRoutes(array(
     *  '/some/path' => 'className:methodName'
     * ));
     * </code>
     *
     * With explicit HTTP method
     * <code>
     * $app->addRoutes(array(
     *  '/some/path' => array('className:methodName', 'get')
     * ));
     * </code>
     *
     * With local middleware
     * <code>
     * $app->addRoutes(array(
     *  '/some/path' => array('className:methodName', 'get', function() {})
     *  '/other/path' => array('className:methodName', function() {})
     * ));
     * </code>
     *
     * With global middleware
     * <code>
     * $app->addRoutes(array(
     *  '/some/path'  => 'className:methodName',
     * ), function() {});
     * </code>
     *
     * @param array $routes      The route definitions
     * @param       callable,... $middlewares Optional callable used for all routes as middleware
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addRoutes(array $routes, $middlewares = null)
    {
        $args = func_get_args();
        array_shift($args);
        $middlewares = $args;

        foreach ($routes as $path => $routeArgs) {
            $httpMethod = 'any';

            // simple
            if (!is_array($routeArgs)) {
                $routeArgs = array($routeArgs);
            }

            $classRoute = array_shift($routeArgs);

            // specific HTTP method
            if (count($routeArgs) > 0 && is_string($routeArgs[0])) {
                $httpMethod = strtoupper(array_shift($routeArgs));
                if (!in_array($httpMethod, static::$ALLOWED_HTTP_METHODS)) {
                    throw new \InvalidArgumentException("Http method '$httpMethod' is not supported.");
                }
            }

            $routeMiddlewares = array_merge($routeArgs, $middlewares);
            $route            = $this->addControllerRoute($path, $classRoute, $routeMiddlewares);

            if ('any' === $httpMethod) {
                call_user_func_array(array($route, 'via'), static::$ALLOWED_HTTP_METHODS);
            } else {
                $route->via($httpMethod);
            }
        }

        return $this;
    }

    /**
     * Add a new controller route
     *
     * <code>
     * $app->addControllerRoute("/the/path", "className:methodName", array(function () { doSome(); }))
     *  ->via('GET')->condition(..);
     *
     * $app->addControllerRoute("/the/path", "className:methodName")
     * ->via('GET')->condition(..);
     * </code>
     *
     * @param string     $path
     * @param string     $route
     * @param callable[] $middleware,...
     *
     * @return \Slim\Route
     */
    public function addControllerRoute($path, $route, array $middleware = array())
    {
        $callback = $this->buildCallbackFromControllerRoute($route);

        array_unshift($middleware, $path);
        array_push($middleware, $callback);

        $route = call_user_func_array(array($this, 'map'), $middleware);

        return $route;
    }

    /**
     * Builds closure callback from controller route
     *
     * @param $route
     *
     * @return \Closure
     */
    protected function buildCallbackFromControllerRoute($route)
    {
        list($className, $methodName) = $this->determineClassAndMethod($route);
        $app      = & $this;
        $callable = function () use ($app, $className, $methodName) {
            $args     = func_get_args();
            $instance = new $className($app);

            return call_user_func_array(array($instance, $methodName), $args);
        };

        return $callable;
    }

    /**
     * @param string $classMethod
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function determineClassAndMethod($classMethod)
    {

        // determine class prefix (eg "\Vendor\Bundle\Controller") and suffix (eg "Controller")
        $classNamePrefix = $this->config('controller.class_prefix');
        if ($classNamePrefix && substr($classNamePrefix, -strlen($classNamePrefix) !== '\\')) {
            $classNamePrefix .= '\\';
        }
        $classNameSuffix = $this->config('controller.class_suffix') ? : '';

        // determine method suffix or default to "Action"
        $methodNameSuffix = $this->config('controller.method_suffix');
        if (is_null($methodNameSuffix)) {
            $methodNameSuffix = 'Action';
        }
        $realClassMethod  = $classMethod;
        if (strpos($realClassMethod, '\\') !== 0) {
            $realClassMethod = $classNamePrefix . $classMethod;
        }

        // having <className>:<methodName>
        if (preg_match('/^([a-zA-Z0-9\\\\_]+):([a-zA-Z0-9_]+)$/', $realClassMethod, $match)) {
            $className  = $match[1] . $classNameSuffix;
            $methodName = $match[2] . $methodNameSuffix;
        } // malformed
        else {
            throw new \InvalidArgumentException("Malformed class action for '$classMethod'. Use 'className:methodName' format.");
        }

        return array($className, $methodName);
    }


}
