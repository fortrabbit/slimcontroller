<?php
/**
 * This class is part of SlimController
 */

namespace SlimController\Tests;


class SlimTest extends TestCase
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

    public function testAddRoutesWithVariables()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Controller:index',
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesInExtendedFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', 'get')
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Malformed class action for 'Controller:index:foo'. Use 'className:methodName' format.
     */
    public function testFailToAddInvalidClassMethodFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => 'Controller:index:foo'
        ));
    }

    public function testGlobalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => 'Controller:index'
        ), function() {
            return false;
        });

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(1, count($middleware));
    }

    public function testLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', function() {
                return false;
            })
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(1, count($middleware));
    }

    public function testGlobalAndLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', function() {
                return false;
            })
        ), function() {
            return false;
        }, function() {
            return false;
        });

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(3, count($middleware));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Http method 'FOO' is not supported.
     */
    public function testFailToAddRouteForUnsupportedHttpMethod()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index', 'foo')
        ));
    }

    public function testRouteCallbacksAreFiredOnDispatch()
    {
        $this->expectOutputString('What is up?');
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => 'Test:index'
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $route->dispatch();
    }

}