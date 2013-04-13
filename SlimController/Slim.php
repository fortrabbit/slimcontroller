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
     * Add multiple controller based routes
     *
     * @param array    $routings  The route definitions
     * @param callable $condition Optional callable used for all routes as middleware
     */
    public function addRoutes(array $routings, $condition = null)
    {
        foreach ($routings as $path => $args) {
            $httpMethod = 'any';

            // simple
            if (!is_array($args)) {
                $args = array($args);
            }

            // specific HTTP method
            if (count($args) > 1 && is_string($args[1])) {
                $classAction = array_shift($args);
                $httpMethod  = array_shift($args);
                array_unshift($args, $classAction);
            }

            // readd path & extract route
            array_unshift($args, $path);
            $this->extractControllerFromRoute($args, $condition);

            // call "map" method to add routing
            $route = call_user_func_array(array($this, 'map'), $args);
            if ('any' === $httpMethod) {
                $route->via('GET', 'POST');
            } else {
                $route->via(strtoupper($httpMethod));
            }
        }
        return $this;
    }


    protected function extractControllerFromRoute(array &$args, $condition = null)
    {
        // tmp remove path
        $path = array_shift($args);

        // determine prefix (eg "\Vendor\Bundle\Controller")
        $classNamePrefix = isset($this->settings['controller.class_prefix'])
            ? $this->settings['controller.class_prefix']. '\\'
            : '';

        // determine method suffix or default to "Action"
        $methodNameSuffix = isset($this->settings['controller.method_suffix'])
            ? $this->settings['controller.method_suffix']
            : 'Action';
        $methodName = '';
        $className = array_shift($args);
        if (strpos($className, '\\') !== 0) {
            $className = $classNamePrefix. $className;
        }

        // having <className>:<methodName>
        if (preg_match('/^([a-zA-Z0-9\\\\_]+):([a-zA-Z0-9_]+)$/', $className, $match)) {
            $className = $match[1];
            $methodName = $match[2]. $methodNameSuffix;
        }

        // malformed
        else {
            throw new \InvalidArgumentException("Malformed class action for '$className'. Use 'className:methodName' format.");
        }

        // build & append callable
        $app = &$this;
        $callable = function() use($app, $className, $methodName, $path) {
            $args = func_get_args();
            $instance = new $className($app);
            return call_user_func_array(array($instance, $methodName), $args);
        };
        if (!is_null($condition)) {
            array_push($args, $condition);
        }
        array_push($args, $callable);

        // re-add path
        array_unshift($args, $path);
        return;
    }


}
