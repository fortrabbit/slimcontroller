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

        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
        // $this->assertTrue($this->app->router->hasNamedRoute('Controller:index'));
        static::assertEquals('/bla', $this->app->urlFor('Controller:index'));
    }

    public function testAddingroutesWithOldSyntaxWithoutMiddlewares()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index'),
        ));

        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesWithOldSyntaxWithoutMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', function() {
                //
            })
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesWithOldSyntaxWithMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', array(function() {
                //
            }))
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddSimpleRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Controller:index',
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/foo');
        static::assertEquals(0, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));

        $this->setUrl('/other');

        $this->app->addRoutes(array(
            '/other' => 'Controller:other',
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesWithVariables()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/:name' => 'Controller:index',
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddRoutesInExtendedFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => 'Controller:index')
        ));
        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testFailToAddInvalidClassMethodFormat()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed class action for \'Controller:index:foo\'. Use \'className:methodName\' format.');
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
        ), fn() => false);

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(1, count($middleware));
    }

    public function testGlobalMiddlewareIsAddedToRouteAsArray()
    {
        $middlewares = array(
            fn() => false,
            fn() => false
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => 'Controller:index'
        ), $middlewares);

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(2, count($middleware));
    }

    public function testLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', fn() => false))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(1, count($middleware));
    }

    public function testArrayOfLocalMiddlewareIsAddedToRoute()
    {
        $middlewares = array(
            fn() => false,
            fn() => false
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', $middlewares))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(2, count($middleware));
    }

    public function testLocalMiddlewaresAreAddedToRoute()
    {
        $middlewares = array(
            fn() => false,
            fn() => false
        );

        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', $middlewares[0], $middlewares[1]))
        ));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(2, count($middleware));
    }

    public function testGlobalAndLocalMiddlewareIsAddedToRoute()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => array('Controller:index', fn() => false))
        ), array(fn() => false, fn() => false));

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(3, count($middleware));
    }

    public function testFailToAddRouteForUnsupportedHttpMethod()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Http method \'FOO\' is not supported.');
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
        [$route] = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
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
        [$route] = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $route->dispatch();
    }

    public function testAddControllerRoute()
    {
        $this->setUrl('/');
        $this->app->addControllerRoute(
            'GET',
            '/', 'Controller:index'
        );

        static::assertEquals(1, count($this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri())));
    }

    public function testAddControllerRouteWithMiddleware()
    {
        $this->setUrl('/');
        $this->app->addControllerRoute(
            'GET',
            '/', 'Controller:index', array(
                fn() => false,
            )
            );

        /** @var \Slim\Route[] $routes */
        $routes = $this->app->container->get('router')->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        static::assertEquals(1, count($routes));

        $middleware = $routes[0]->getMiddleware();
        static::assertIsArray($middleware);
        static::assertSame(1, count($middleware));
    }

    public function testNamedRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/'              => 'Controller:index',
            '/bla'           => 'Bla:Index',
            '/something/:id' => 'Something:show'
        ));

        static::assertEquals('/', $this->app->urlFor('Controller:index'));
        static::assertEquals('/bla', $this->app->urlFor('Bla:Index'));
        static::assertEquals('/something/:id', $this->app->urlFor('Something:show'));
    }

    public function testNamedRoutesThrowsExceptionIfLookingForARouteThatDoesNotExist()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Named route not found for name: this is not a named route');
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/'              => 'Controller:index',
            '/bla'           => 'Bla:Index',
            '/something/:id' => 'Something:show'
        ));

        static::assertEquals('/', $this->app->urlFor('this is not a named route'));
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
        $app->getContainer()['TestController'] = fn() => new TestController($app);

        $route = $this->app->addControllerRoute(
            'GET', '/', 'TestController:index'
        );

        // If the route could be dispatched, then the service was found
        $result = $route->dispatch();
        static::assertTrue($result);
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
        $app->getContainer()['TestController'] = fn() => new TestController($app);

        $app->addRoutes(array(
            '/another/:name' => 'TestController:hello'
        ));
        $route = $app->container->get('router')->getNamedRoute('TestController:hello');
        $route->setParams(array('name' => 'foo'));
        static::assertTrue($route->dispatch());
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
        $app->getContainer()['String\\Controller'] = fn() => new TestController($app);

        $route = $this->app->addControllerRoute(
            'GET', '/', 'String\\Controller:index'
        );

        // If the route could be dispatched, then the service was found
        $result = $route->dispatch();
        static::assertTrue($result);
    }

}
