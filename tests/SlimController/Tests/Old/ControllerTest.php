<?php

namespace SlimController\Tests;

class ControllerTest extends TestCase
{

    public function testControllerSimple()
    {
        $this->expectOutputString('What is up?');
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Test:index',
        ));

        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testControllerExtended()
    {
        $this->expectOutputString('What is up YOU?');
        $this->setUrl('/hello/YOU');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Test:hello',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testControllerAbsPath()
    {
        $this->expectOutputString('What is up YOU?');
        $this->setUrl('/hello/YOU');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Test:hello',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }
}
