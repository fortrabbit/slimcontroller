<?php

namespace SlimControllerTest;

class SlimControllerUnitTestCase extends \PHPUnit_Framework_TestCase {

    protected $env;
    protected $req;
    protected $res;
    protected $app;


    protected function setUrl($path, $params = '')
    {
        \Slim\Environment::mock(array(
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '', //<-- Physical
            'PATH_INFO' => $path, //<-- Virtual
            'QUERY_STRING' => $params,
            'SERVER_NAME' => 'slim',
            'SERVER_PORT' => 80,
            'slim.url_scheme' => 'http',
            'slim.input' => '',
            'slim.errors' => fopen('php://stderr', 'w'),
            'HTTP_HOST' => 'slim'
        ));
        $this->env = \Slim\Environment::getInstance();
        $this->req = new \Slim\Http\Request($this->env);
        $this->res = new \Slim\Http\Response();
        $this->app = new \SlimController\Slim(array(
            'controller.class_prefix'    => '\\Controller',
            'controller.method_suffix'   => 'Action',
            'controller.template_suffix' => 'php',
            'templates.path'             => __DIR__ . '/templates'
        ));
    }
}