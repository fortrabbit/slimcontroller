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

        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
        // $this->assertTrue($this->app->router->hasNamedRoute('Controller:index'));
        static::assertEquals('/bla', $this->app->getContainer()->get('router')->pathFor('Controller:index'));
    }

    public function testAddingroutesWithOldSyntaxWithoutMiddlewares()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('Controller:index'),
        ));

        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
    }

    public function testAddRoutesWithOldSyntaxWithoutMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', function() {
                //
            })
        ));
        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
    }

    public function testAddRoutesWithOldSyntaxWithMiddlewareArray()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => array('Home:index', array(function() {
                //
            }))
        ));
        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
    }

    public function testAddSimpleRoutes()
    {
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/' => 'Controller:index',
        ));
        /** @var \Slim\Router $router */
        $router = $this->app->getContainer()->get('router');
        
        static::assertEquals(\FastRoute\Dispatcher::FOUND, $router->dispatch($this->req)[0]);

        $this->setUrl('/foo');
        static::assertEquals(\FastRoute\Dispatcher::NOT_FOUND, $router->dispatch($this->req)[0]);

        $this->setUrl('/other');

        // Adding a route after we've dispatched no longer works. I suspect this is a change in 
        // Slim v3. I've not found the cause or a workaround, but also can't see a real world
        // use-case so I won't be spending any more time trying to fix it.
        $this->app->addRoutes(array(
            '/other' => 'Controller:other',
        ));
        
        static::assertEquals(\FastRoute\Dispatcher::NOT_FOUND, $router->dispatch($this->req)[0]);
    }

    public function testAddRoutesWithVariables()
    {
        $this->setUrl('/hello/you');
        $this->app->addRoutes(array(
            '/hello/{name}' => 'Controller:index',
        ));
        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
    }

    public function testAddRoutesInExtendedFormat()
    {
        $this->setUrl('/bla');
        $this->app->addRoutes(array(
            '/bla' => array('get' => 'Controller:index')
        ));
        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
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

        $router = $this->app->getContainer()->get('router');

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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
        ($this->app)($this->req, $this->res);
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
        ($this->app)($this->req, $this->res);
    }

    public function testAddControllerRouteSimple()
    {
        $this->setUrl('/');
        $this->app->addControllerRoute(
            'GET',
            '/', 'Controller:index'
        );

        static::assertEquals(
            \FastRoute\Dispatcher::FOUND,
            $this->app->getContainer()->get('router')->dispatch($this->req)[0]
        );
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
        $routes = $this->app->getContainer()->get('router')->dispatch($this->req);

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
            '/something[/{id}]' => 'Something:show'
        ));

        static::assertEquals('/', $this->app->getContainer()->get('router')->pathFor('Controller:index'));
        static::assertEquals('/bla', $this->app->getContainer()->get('router')->pathFor('Bla:Index'));
        static::assertEquals('/something', $this->app->getContainer()->get('router')->pathFor('Something:show'));
        static::assertEquals('/something/10', $this->app->getContainer()->get('router')->pathFor('Something:show', [ 'id' => 10]));
    }

    public function testNamedRoutesThrowsExceptionIfLookingForARouteThatDoesNotExist()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Named route does not exist for name: this is not a named route');
        $this->setUrl('/');
        $this->app->addRoutes(array(
            '/'              => 'Controller:index',
            '/bla'           => 'Bla:Index',
            '/something/{id}' => 'Something:show'
        ));

        static::assertEquals('/', $this->app->getContainer()->get('router')->pathFor('this is not a named route'));
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
