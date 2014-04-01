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
    protected static $ALLOWED_HTTP_METHODS = array('ANY', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD');

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
     * @param array $routes The route definitions
     * @param array $globalMiddlewares
     * @throws \InvalidArgumentException
     * @internal param $callable ,... $middlewares Optional callable used for all routes as middleware
     *
     * @return $this
     */
    public function addRoutes(array $routes, $globalMiddlewares = array())
    {
        if (!is_array($globalMiddlewares)) {
            $globalMiddlewares = array($globalMiddlewares);
        };

        foreach ($routes as $path => $routeArgs) {
            // create array for simple request
            $routeArgs = (is_array($routeArgs)) ? $routeArgs : array('any' => $routeArgs);

            foreach ($routeArgs as $httpMethod => $classArgs) {
                // assign vars if middleware callback exists
                if(is_array($classArgs)) {
                    $classRoute = $classArgs[0];
                    $localMiddlewares = (is_array($classArgs[1])) ? $classArgs[1] : array($classArgs[1]);
                } else {
                    $classRoute = $classArgs;
                    $localMiddlewares = array();
                }

                // specific HTTP method
                $httpMethod = strtoupper($httpMethod);
                if (!in_array($httpMethod, static::$ALLOWED_HTTP_METHODS)) {
                    throw new \InvalidArgumentException("Http method '$httpMethod' is not supported.");
                }

                $routeMiddlewares = array_merge($localMiddlewares, $globalMiddlewares);
                $route = $this->addControllerRoute($path, $classRoute, $routeMiddlewares)->name($classRoute);

                if ('any' === $httpMethod) {
                    call_user_func_array(array($route, 'via'), static::$ALLOWED_HTTP_METHODS);
                } else {
                    $route->via($httpMethod);
                }
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
