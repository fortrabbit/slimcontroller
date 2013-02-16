<?php

namespace SlimControllerTest;

class ControllerTest extends SlimControllerUnitTestCase
{


    public function testControllerSimple()
    {
        $this->expectOutputString('What is up?');
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Test:index',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->app->router()->dispatch($route);
    }

    public function testControllerExtended()
    {
        $this->expectOutputString('What is up YOU?');
        $this->setUrl('/hello/YOU');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Test:hello',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->app->router()->dispatch($route);
    }

    public function testControllerAbsPath()
    {
        $this->expectOutputString('What is up YOU?');
        $this->setUrl('/hello/YOU');
        $this->app->addRoutes(array(
            '/hello/:name' => '\\Controller\\Test:hello',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->app->router()->dispatch($route);
    }
}
