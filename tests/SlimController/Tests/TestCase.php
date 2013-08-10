<?php

namespace SlimController\Tests;

use Slim\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use SlimController\Slim;

class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Environment
     */
    protected $env;

    /**
     * @var Request
     */
    protected $req;

    /**
     * @var Response
     */
    protected $res;

    /**
     * @var Slim
     */
    protected $app;


    protected function setUrl($path, $params = '', $config = array())
    {
        Environment::mock(array(
            'REQUEST_METHOD'  => 'GET',
            'REMOTE_ADDR'     => '127.0.0.1',
            'SCRIPT_NAME'     => '', //<-- Physical
            'PATH_INFO'       => $path, //<-- Virtual
            'QUERY_STRING'    => $params,
            'SERVER_NAME'     => 'slim',
            'SERVER_PORT'     => 80,
            'slim.url_scheme' => 'http',
            'slim.input'      => '',
            'slim.errors'     => fopen('php://stderr', 'w'),
            'HTTP_HOST'       => 'slim'
        ));
        $this->env = Environment::getInstance();
        $this->req = new Request($this->env);
        $this->res = new Response();
        $this->app = new Slim(array_merge(array(
            'controller.class_prefix'    => '\\SlimController\\Tests\\Fixtures\\Controller',
            'controller.class_suffix'    => 'Controller',
            'controller.method_suffix'   => 'Action',
            'controller.template_suffix' => 'php',
            'templates.path'             => __DIR__ . '/Fixtures/templates'
        ), $config));
    }
}