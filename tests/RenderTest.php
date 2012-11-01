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
        $this->app->router()->setResourceUri($this->req->getResourceUri());
        list($route) = $this->app->router()->getMatchedRoutes();
        $this->app->router()->dispatch($route);
    }
}
