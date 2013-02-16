<?php

namespace SlimControllerTest;

class RenderTest extends SlimControllerUnitTestCase
{

    public function testParamsMultiDefault()
    {
        $this->expectOutputString('This is orotound and grandios');
        $this->setUrl('/', 'data[Some][param]=foo&data[Other][param]=bar');
        $this->app->addRoutes(array(
            '/' => 'Test:render',
        ));
        list($route) = $this->app->router()->getMatchedRoutes($this->req->getMethod(), $this->req->getResourceUri());
        $this->app->router()->dispatch($route);
    }
}
