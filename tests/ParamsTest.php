<?php

namespace SlimControllerTest;

class ParamsTest extends SlimControllerUnitTestCase
{


    public function testParamsSingle()
    {
        $this->expectOutputString('Param is 123');
        $this->setUrl('/', 'data[Some][param]=123');
        $this->app->addRoutes(array(
            '/' => 'Test:paramSingle',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }

    public function testParamsMulti()
    {
        $this->expectOutputString('All is foo bar');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMulti',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }

    public function testParamsMultiMissing()
    {
        $this->expectOutputString('All is foo bar');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiMissing',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }

    public function testParamsMultiMissingReq()
    {
        $this->expectOutputString('OK');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiMissingReq',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }

    public function testParamsMultiDefault()
    {
        $this->expectOutputString('All is foo bar and great');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:paramMultiDefault',
        ));
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }
}
