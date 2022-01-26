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
        static::assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/foo');
        static::assertEquals(0, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/other');

        $this->app->addRoutes(array(
            '/other' => 'Controller:other',
        ));
        static::assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testRoutesWithVariables()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Controller:index',
        ));
        static::assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testRoutesWithExtendedFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', 'get')
        ));
        static::assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

}
