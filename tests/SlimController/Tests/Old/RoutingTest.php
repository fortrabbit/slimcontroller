<?php

namespace SlimController\Tests;

class RoutingTest extends TestCase
{

    public function testAddSimpleRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Controller:index',
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/foo');
        $this->assertEquals(0, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/other');

        $this->app->addRoutes(array(
            '/other' => 'Controller:other',
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testRoutesWithVariables()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Controller:index',
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testRoutesWithExtendedFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', 'get')
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

}
