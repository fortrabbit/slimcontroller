<?php

namespace SlimControllerTest;

class RoutingTest extends SlimControllerUnitTestCase
{

    public function testAddSimpleRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Controller:index',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes()));

        $this->setUrl('/foo');
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        $this->assertEquals(0, count($this->app->router()->getMatchedRoutes()));

        $this->setUrl('/other');

        $this->app->addRoutes(array(
            '/other' => 'Controller:other',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes()));
    }

    public function testAddExtendedRoutes()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Controller:index',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes()));
    }

}
