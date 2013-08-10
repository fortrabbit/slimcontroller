<?php

namespace SlimController\Tests;

class ParamsTest extends TestCase
{


    public function testParamsSingle()
    {
        $this->expectOutputString('Param is 123');
        $this->setUrl('/', 'data[Some][param]=123');
        $this->app->addRoutes(array(
            '/' => 'Test:paramSingle',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }


    public function testParamsSingleObject()
    {
        $this->expectOutputString('Param is 123123123');
        $this->setUrl('/', 'data[Some][attrib1]=123&data[Some][attrib2]=123&data[Some][attrib3]=123');
        $this->app->addRoutes(array(
            '/' => 'Test:paramSingleObject',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsMulti()
    {
        $this->expectOutputString('All is foo bar');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMulti',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsMultiMissing()
    {
        $this->expectOutputString('All is foo bar');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiMissing',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsMultiMissingReq()
    {
        $this->expectOutputString('OK');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiMissingReq',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsMultiDefault()
    {
        $this->expectOutputString('All is foo bar and great');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiDefault',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsDifferentPrefix()
    {
        $this->expectOutputString('GOT OK');
        $this->setUrl('/', 'data[Foo]=bar&other[Foo]=bar', array(
            'controller.param_prefix' => 'other.'
        ));
        $this->app->addRoutes(array(
            '/' => 'Test:paramDifferentPrefix',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }

    public function testParamsNoPrefix()
    {
        $this->expectOutputString('All params: data.Foo=bar - other.Foo=bar');
        $this->setUrl('/', 'data[Foo]=bar&other[Foo]=bar', array(
            'controller.param_prefix' => ''
        ));
        $this->app->addRoutes(array(
            '/' => 'Test:paramNoPrefix',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }
}
