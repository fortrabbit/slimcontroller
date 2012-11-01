<?php

/*
 * This file is part of SlimController.
 *
 * @author Ulrich Kautz <uk@fortrabbit.de>
 * @copyright 2012 Ulrich Kautz
 * @version 0.1.1
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
            elseif (isset($args['http'])) {
                $httpMethod = strtolower($args['http']);
                unset($args['http']);
            }

            if (isset($args['args'])) {
                $args = $args['args'];
            }

            array_unshift($args, $path);
            $this->extractControllerFromRoute($args, $condition);
            if ('any' === $httpMethod) {
                $route = call_user_func_array(array($this, 'map'), $args);
                $route->via('GET', 'POST');
            } else {
                call_user_func(array($this, $httpMethod), $args);
            }
        }
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
        $className = $classNamePrefix. array_shift($args);

        // having $app->cGet("\Vendor\Bla:method", ..)
        if (preg_match('/^(.+):+(.+)$/', $className, $match)) {
            $className = $match[1];
            $methodName = $match[2]. $methodNameSuffix;
        }

        // having $app->cGet("\Vendor\Bla", "method", ..)
        else {
            $methodName = array_shift($args). $methodNameSuffix;
        }

        // build & append callable
        $app = &$this;
        $callable = function() use($app, $className, $methodName, $path) {
            $args = func_get_args();
            $instance = new $className($app);
            //return call_user_method_array($methodName, $instance, $args);
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
