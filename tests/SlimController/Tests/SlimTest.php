<?php
/**
 * This class is part of SlimController
 */

namespace SlimController\Tests;


use SlimController\Tests\Fixtures\Controller\TestController;


class SlimTest extends TestCase
{

    public function testAddingRoutesToSameMethod()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => 'Controller:index'),
            '/alb' => array('get' => 'Controller:index')
        ));

        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
        // $this->assertTrue($this->app->router->hasNamedRoute('Controller:index'));
        $this->assertEquals('/bla', $this->app->urlFor('Controller:index'));
    }

    public function testAddingroutesWithOldSyntaxWithoutMiddlewares()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index'),
        ));

        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesWithOldSyntaxWithoutMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', function() {
                //
            })
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesWithOldSyntaxWithMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', array(function() {
                //
            }))
        ));
        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

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
            '/bla' => array('get' => 'Controller:index')
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

    public function testGlobalMiddlewareIsAddedToRouteAsArray()
    {
        $middlewares = array(
            function() { return false; },
            function() { return false; }
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => 'Controller:index'
        ), $middlewares);

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(2, count($middleware));
    }

    public function testLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', function() {
                return false;
            }))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(1, count($middleware));
    }

    public function testArrayOfLocalMiddlewareIsAddedToRoute()
    {
        $middlewares = array(
            function() { return false; },
            function() { return false; }
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', $middlewares))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(2, count($middleware));
    }

    public function testLocalMiddlewaresAreAddedToRoute()
    {
        $middlewares = array(
            function() { return false; },
            function() { return false; }
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', $middlewares[0], $middlewares[1]))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(2, count($middleware));
    }

    public function testGlobalAndLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', function() {
                return false;
            }))
        ), array(function() {
            return false;
        }, function() {
            return false;
        }));

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
            '/bla' => array('foo' => 'Controller:index')
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

    public function testEmptyButNotNullMethodSuffixAccepted()
    {
        $this->expectOutputString('Yes, I was called');
        $this->setUrl('/bla', '', array(
            'controller.method_suffix'   => ''
        ));
        $this->app->addRoutes(array(
            '/bla' => 'Test:notSuffixedMethod'
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $route->dispatch();
    }

    public function testAddControllerRoute()
    {
        $this->setUrl('/');
        $this->app->addControllerRoute(
            '/', 'Controller:index'
        )->via('GET');

        $this->assertEquals(1, count($this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddControllerRouteWithMiddleware()
    {
        $this->setUrl('/');
        $this->app->addControllerRoute(
            '/', 'Controller:index', array(
                function() {
                    return false;
                },
            )
        )->via('GET');

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        $this->assertInternalType('array', $middleware);
        $this->assertSame(1, count($middleware));
    }

    public function testNamedRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/'              => 'Controller:index',
            '/bla'           => 'Bla:Index',
            '/something/:id' => 'Something:show'
        ));

        $this->assertEquals('/', $this->app->urlFor('Controller:index'));
        $this->assertEquals('/bla', $this->app->urlFor('Bla:Index'));
        $this->assertEquals('/something/:id', $this->app->urlFor('Something:show'));
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Named route not found for name: this is not a named route
     */
    public function testNamedRoutesThrowsExceptionIfLookingForARouteThatDoesNotExist()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/'              => 'Controller:index',
            '/bla'           => 'Bla:Index',
            '/something/:id' => 'Something:show'
        ));

        $this->assertEquals('/', $this->app->urlFor('this is not a named route'));
    }

    public function testServiceControllersAreFetched()
    {
        $this->expectOutputString("What is up?");

        $config = array(
            'controller.class_prefix'    => '',
            'controller.class_suffix'    => '',
        );
        $this->setUrl('/', '', $config);
        $app = $this->app;
        $app->container->singleton('TestController', function () use ($app) {
            return new TestController($app);
        });

        $route = $this->app->addControllerRoute(
            '/', 'TestController:index'
        )->via('GET');

        // If the route could be dispatched, then the service was found
        $result = $route->dispatch();
        $this->assertTrue($result);
    }

    public function testServiceControllersAreFetchedWithParams()
    {
        $this->expectOutputString("What is up foo?");

        $config = array(
            'controller.class_prefix'    => '',
            'controller.class_suffix'    => '',
        );
        $this->setUrl('/', '', $config);
        $app = $this->app;
        $app->container->singleton('TestController', function () use ($app) {
            return new TestController($app);
        });

        $app->addRoutes(array(
            '/another/:name' => 'TestController:hello'
        ));
        $route = $app->router()->getNamedRoute('TestController:hello');
        $route->setParams(array('name' => 'foo'));
        $this->assertTrue($route->dispatch());
    }

    public function testServiceControllersAreFetchedEvenIfTheirNameIsAnInvalidPHPClassName()
    {
        $this->expectOutputString("What is up?");

        $config = array(
            'controller.class_prefix'    => '',
            'controller.class_suffix'    => '',
        );
        $this->setUrl('/', '', $config);
        $app = $this->app;
        $app->container->singleton('String\\Controller', function () use ($app) {
            return new TestController($app);
        });

        $route = $this->app->addControllerRoute(
            '/', 'String\\Controller:index'
        )->via('GET');

        // If the route could be dispatched, then the service was found
        $result = $route->dispatch();
        $this->assertTrue($result);
    }

}
