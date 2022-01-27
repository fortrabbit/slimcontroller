<?php

namespace SlimController\Tests;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use SlimController\Slim;

class TestCase extends  \PHPUnit\Framework\TestCase
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


    protected function setUrl($path, $params = '', $settings = array())
    {
        $this->env = Environment::mock(array(
            'REQUEST_URI'     => $path,
            'QUERY_STRING'    => $params,
            'slim.errors'     => fopen('php://stderr', 'w'),
            'HTTP_HOST'       => 'slim'
        ));
        $this->req = Request::createFromEnvironment($this->env);
        $this->res = new Response();
        $this->app = new Slim(array(
                'settings' => array_merge(array(
                    'controller.class_prefix'    => '\\SlimController\\Tests\\Fixtures\\Controller',
                    'controller.class_suffix'    => 'Controller',
                    'controller.method_suffix'   => 'Action',
                    'controller.template_suffix' => 'php',
                    'templates.path'             => __DIR__ . '/Fixtures/templates'
                ), $settings)
            )
        );
    }
}