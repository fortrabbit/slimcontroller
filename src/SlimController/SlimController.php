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
     * @var Slim
     */
    protected $app;

    /**
     * @var bool Whether cleanup params or not
     */
    protected $paramCleanup = false;

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
     * Suffix was never specified and defaults to empty string
     *
     * @var string
     */
    protected $renderTemplateSuffix = 'twig';

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
            $prefixLength      = strlen($this->paramPrefix);
            if ($prefixLength > 0 && substr($this->paramPrefix, -$prefixLength) !== '.') {
                $this->paramPrefix .= '.';
            }
        }
        if ($app->config('controller.cleanup_params')) {
            $this->paramCleanup = true;
        }
    }

    /**
     * Renders output with given template
     *
     * @param string $template Name of the template to be rendererd
     * @param array  $args     Args for view
     */
    protected function render($template, $args = array())
    {
        if (!is_null($this->renderTemplateSuffix)
            && !preg_match('/\.' . $this->renderTemplateSuffix . '$/', $template)
        ) {
            $template .= '.' . $this->renderTemplateSuffix;
        }
        $this->app->render($template, $args);
    }

    /**
     * Renders given data into JSON string, which is then set as response body
     *
     * @param mixed $data Data to be encoded into JSON string
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        $jsonBody = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->app->getLog()->error("Failed to encode data to JSON: " . json_last_error_msg());

            throw new \InvalidArgumentException(json_last_error_msg());
        }
        $this->app->response->setBody($jsonBody);
        $this->app->response->header('Content-Type', 'application/json');
        $this->app->response->setStatus($statusCode);
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
     * Slim's response object
     *
     * @return \Slim\Http\Response
     */
    protected function response()
    {
        return $this->app->response();
    }

    /**
     * Returns a single parameter of the "data[Object][Key]" format.
     *
     * <code>
     * $paramValue = $this->param('prefix.name'); // prefix[name] -> "string value"
     * $paramValue = $this->param('prefix.name', 'post'); // prefix[name] -> "string value"
     * $paramValue = $this->param('prefix.name', 'get'); // prefix[name] -> "string value"
     * </code>
     *
     * @param mixed $name    Name of the parameter
     * @param mixed $reqMode Optional mode. Either null (all params), true | "post"
     *                       (only POST params), false | "get" (only GET params)
     * @param mixed $cleanup Whether use simple cleanup
     *
     * @return mixed Either array or single string or null
     */
    protected function param($name, $reqMode = null, $cleanup = null)
    {
        $cleanup = is_null($cleanup) ? $this->paramCleanup : $cleanup;
        $name    = $this->paramPrefix . $name;
        $reqMeth = $this->paramAccessorMeth($reqMode);

        // determine stash name
        $reqStashName = 'params' . ucfirst($reqMeth);
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
                return $cleanup === true ? $this->cleanupParam($params) : $params;
            }
        }

        return null;
    }

    /**
     * Reads multiple params at once
     *
     * <code>
     * $params = $this->params(['prefix.name', 'other.name']); //  -> ["prefix.name" => "value", ..]
     * $params = $this->params(['prefix.name', 'other.name'], true); //  -> null if not all found
     * $params = $this->params(['prefix.name', 'other.name'], ['other.name' => "Default Value"]);
     * </code>
     *
     * @param mixed $names    Name or names of parameters (GET or POST)
     * @param mixed $reqMode  Optional mode. Either null (all params), true | "post"
     *                       (only POST params), false | "get" (only GET params)
     * @param mixed $defaults Either true (require ALL given or return null), array (defaults)
     *
     * @return mixed Either array or single string or null
     */
    protected function params($names = array(), $reqMode = null, $defaults = null)
    {
        // no names given -> get them all
        if (!$names) {
            $names = $this->getAllParamNames($reqMode);
        }
        $res = array();
        foreach ($names as $obj) {
            $name  = is_array($obj) ? $obj[0] : $obj;
            $param = $this->param($name, $reqMode);
            if (!is_null($param) && (!is_array($param) || !empty($param))) {
                $res[$name] = $param;
            } // if in "need all" mode
            elseif ($defaults === true) {
                return null;
            } // if in default mode
            elseif (is_array($defaults) && isset($defaults[$name])) {
                $res[$name] = $defaults[$name];
            }
        }

        return $res;
    }

    /**
     * Cleans up a single or a list of params by stripping HTML encodings
     *
     * @param string $value
     *
     * @return string
     */
    protected function cleanupParam($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $clean = $this->cleanupParam($v);
                if (!is_null($clean)) {
                    $value[$k] = $clean;
                }
            }

            return $value;
        } else {
            return preg_replace('/>/', '', preg_replace('/</', '', $value));
        }
    }

    /**
     * Flattens an array by transforming the form ["a" => ["b" => ["c" => 1]]] to ["a.b.c" => 1]
     *
     * @param array $data
     *
     * @return array
     */
    protected function flatten(array $data)
    {
        return $this->flattenInner($data);
    }

    private function flattenInner(array $data, $prefix = '', &$flat = array())
    {
        foreach ($data as $key => $value) {
            // is array -> flatten deep
            if (is_array($value)) {
                $this->flattenInner($value, $prefix . $key . '.', $flat);
            } // scalar -> use
            else {
                $flat[$prefix . $key] = $value;
            }
        }

        return $flat;
    }

    private function paramAccessorMeth($reqMode = null)
    {
        return $reqMode === true || $reqMode === 'post' // POST
            ? 'post'
            : ($reqMode === false || $reqMode === 'get' // GET
                ? 'get'
                : 'params' // ALL
            );
    }

    private function getAllParamNames($reqMode)
    {
        $reqMeth  = $this->paramAccessorMeth($reqMode);
        $params   = $this->request()->$reqMeth();
        $namesPre = $this->flatten($params);
        $names    = array_keys($namesPre);
        if ($prefix = $this->paramPrefix) {
            $prefixLen = strlen($prefix);
            $names     = array_map(function ($key) use ($prefixLen) {
                return substr($key, $prefixLen);
            }, array_filter($names, function ($in) use ($prefix) {
                return strpos($in, $prefix) === 0;
            }));
        }

        return $names;
    }
}
