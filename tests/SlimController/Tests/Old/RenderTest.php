<?php

namespace SlimController\Tests;

class RenderTest extends TestCase
{

    /**
     * @doesNotPerformAssertions
     */
    public function testParamsMultiDefault()
    {
        $this->expectOutputString('This is orotound and grandios');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:render',
        ));
        [$route] = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        //$this->app->router()->dispatch($route);
        $route->dispatch();
    }
}
