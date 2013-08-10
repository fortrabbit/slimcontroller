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
 * Implements a basic controller functionallity.
 * It should not be instanciated directly but extended from.
 */

abstract class SlimController
{

    /**
     * @const string
     */
    const VERSION = '0.1.4';

    /**
     * @var \Slim\Slim
     */
    protected $app;

    /**
     * @var bool Whether cleanup params or not
     */
    private $paramCleanup = true;

    /**
     * @var string Prefix for params
     */
    private $paramPrefix = 'data.';

    /**
     * @var array Stash of GET & POST params
     */
    private $paramsParams = null;

    /**
     * @var array Stash of GET params
     */
    private $paramsGet = null;

    /**
     * @var array Stash of POST params
     */
    private $paramsPost = null;

    /**
     * @var string
     */
    protected $renderTemplateSuffix = null;

    /**
     * Constructor for TodoQueue\Controller\Login
     *
     * @param \Slim\Slim $app Ref to slim app
     */
    public function __construct(\Slim\Slim &$app)
    {
        $this->app = $app;
        if ($renderTemplateSuffix = $app->config('controller.template_suffix')) {
            $this->renderTemplateSuffix = $renderTemplateSuffix;
        }
        if (!is_null($paramPrefix = $app->config('controller.param_prefix'))) {
            $this->paramPrefix = $paramPrefix;
        }
        $this->renderTemplateSuffix = $app->config('controller.template_suffix');
    }

    /**
     * Renders output with given template
     *
     * @param string $template Name of the template to be rendererd
     * @param array  $args     Args for view
     */
    protected function render($template, $args = null)
    {
        if (!is_null($args)) {
            $this->app->view()->appendData($args);
        }
        if (!is_null($this->renderTemplateSuffix)
            && !preg_match('/\.'. $this->renderTemplateSuffix. '$/', $template)
        ) {
            $template .= '.'. $this->renderTemplateSuffix;
        }
        print $this->app->view()->render($template);
    }

    /**
     * Performs redirect
     *
     * @param string $path
     */
    protected function redirect($path)
    {
        $this->app->redirect($path);
    }

    /**
     * Slim's request object
     *
     * @return \Slim\Http\Request
     */
    protected function request()
    {
        return $this->app->request();
    }


    /**
     * Returns a single parameter of the "data[Object][Key]" format.
     *
     * <code>
     $paramValue = $this->param('prefix.name'); // prefix[name] -> "string value"
     $paramValue = $this->param('prefix.name', 'post'); // prefix[name] -> "string value"
     $paramValue = $this->param('prefix.name', 'get'); // prefix[name] -> "string value"
     * </code>
     *
     * @param mixed $name    Name of the parameter
     * @param mixed $reqMode Optional mode. Either null (all params), true | "post"
     *                       (only POST params), false | "get" (only GET params)
     *
     * @return mixed Either array or single string or null
     */
    protected function param($name, $reqMode = null)
    {
        $args = array();
        if (is_array($name)) {

            // ["name"]
            if (count($name) === 1) {
                $name = $name[0];
            }

            // ["name", ["constraint" => "..", ..]]
            elseif (is_array($name[1])) {
                list($name, $args) = $name;
            }

            // ["name", "constraint" => "..", ..]
            else {
                $n = array_shift($name);
                $args = $name;
                $name = $n;
            }
        }
        $args = array_merge([
            'constraint' => null,
            'default'    => null,
            'raw'        => false
        ], $args);

        // prefix name
        $name = $this->paramPrefix. $name;

        // determine method for request
        $reqMeth = $this->paramAccessorMeth($reqMode);

        // determine stash name
        $reqStashName = 'params'. ucfirst($reqMeth);
        if (is_null($this->$reqStashName)) {
            $this->$reqStashName = $this->request()->$reqMeth();
        }
        $params = $this->$reqStashName;

        // split of parts and go through
        $parts = preg_split('/\./', $name);
        while (isset($params[$parts[0]])) {
            $params = $params[$parts[0]];
            array_shift($parts);
            if (empty($parts)) {
                return $this->cleanupParam($params, $args);
            }
        }
        return null;
    }

    private function cleanupParam($value, $args)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $clean = $this->cleanupParam($v, $args);
                if (!is_null($clean)) {
                    $value[$k] = $clean;
                }
            }
            return $value;
        }
        else {

            // cleanup
            if ($this->paramCleanup && !$args['raw']) {
                $value = preg_replace('/>/', '',
                    preg_replace('/</', '', $value ));
            }

            // check costraint
            if ($constraint = $args['constraint']) {

                // constraint = function & not matching
                if (is_object($constraint) && $constraint instanceof \Closure && !$constraint($value)) {
                    return null;
                }

                // constraint = regex & not matching
                elseif (!preg_match($constraint, $value)) {
                    return null;
                }
            }
            return $value;
        }
    }



    /**
     * Reads multiple params at once
     *
     * <code>
     $params = $this->params(['prefix.name', 'other.name']); //  -> ["prefix.name" => "value", ..]
     $params = $this->params(['prefix.name', 'other.name'], true); //  -> null if not all found
     $params = $this->params(['prefix.name', 'other.name'], ['other.name' => "Default Value"]);
     * </code>
     *
     * @param mixed $names    Name or names of parameters (GET or POST)
     * @param mixed $reqMode Optional mode. Either null (all params), true | "post"
     *                       (only POST params), false | "get" (only GET params)
     * @param mixed $defaults Either true (require ALL given or return null), array (defaults)
     *
     * @return mixed Either array or single string or null
     */
    protected function params($names = array(), $reqMode = null, $defaults = null)
    {
        // no names given -> get them all
        if (!$names) {
            $reqMeth = $this->paramAccessorMeth($reqMode);
            $params = $this->request()->$reqMeth();
            $namesPre = self::flatten($params);
            $names = array_keys($namesPre);
            if ($prefix = $this->paramPrefix) {
                $prefixLen = strlen($prefix);
                $names = array_map(function ($key) use ($prefixLen) {
                    return substr($key, $prefixLen);
                }, array_filter($names, function ($in) use ($prefix) {
                    return strpos($in, $prefix) === 0;
                }));
            }
        }
        $res = array();
        foreach ($names as $n) {
            $param = $this->param($n, $reqMode);
            if (!is_null($param) && (!is_array($param) || !empty($param))) {
                $res[$n] = $param;
            }

            // if in "need all" mode
            elseif ($defaults === true) {
                return null;
            }

            // if in default mode
            elseif (is_array($defaults) && isset($defaults[$n])) {
                $res[$n] = $defaults[$n];
            }
        }
        return $res;
    }


    protected function paramAccessorMeth($reqMode = null)
    {
        return $reqMode === true || $reqMode === 'post' // POST
            ? 'post'
            : ($reqMode === false || $reqMode === 'get' // GET
                ? 'get'
                : 'params' // ALL
            );
    }



    protected static function flatten($data, $flat = array(), $prefix = '')
    {
        foreach ($data as $key => $value) {

            // is array -> flatten deeped
            if (is_array($value)) {
                $flat = self::flatten($value, $flat, $prefix. $key. '.');
            }
            // scalar -> use
            else {
                $flat[$prefix. $key] = $value;
            }
        }
        return $flat;
    }

}
