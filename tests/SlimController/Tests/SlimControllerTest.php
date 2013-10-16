<?php
/**
 * This class is part of SlimController
 */

namespace SlimController\Tests;

use Mockery as m;
use SlimController\SlimController;
use SlimController\Tests\Fixtures\Controller\TestController;

class SlimControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $slim;

    public function setUp()
    {
        $this->slim = m::mock('\Slim\Slim');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->addToAssertionCount($this->slim->mockery_getExpectationCount());
        m::close();
        parent::tearDown();
    }

    public function testControllerConfigParamsAreUsed()
    {
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.template_suffix')
            ->andReturnNull();
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.param_prefix')
            ->andReturnNull();
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.cleanup_params')
            ->andReturnNull();
        $controller = new TestController($this->slim);
        $this->assertTrue(true);
    }

    public function testRenderingWorksFine()
    {
        $this->assertDefaultConstruction();
        $this->slim->shouldReceive('render')
            ->once()
            ->with('rendertest.Suffix', array('foo' => 'orotound', 'bar' => 'grandios'));

        $controller = new TestController($this->slim);
        $controller->renderAction();
    }

    public function testRedirectWorksFine()
    {
        $this->assertDefaultConstruction();
        $this->slim->shouldReceive('redirect')
            ->once()
            ->with('/here');

        $controller = new TestController($this->slim);
        $controller->redirectAction();
    }

    public function testSingleParamLeafAccessWorks()
    {
        $this->expectOutputString("Param is foo");
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo')));

        $controller = new TestController($this->slim);
        $controller->paramSingleAction();
    }

    public function testSingleParamArrayAccessWorks()
    {
        $this->expectOutputString("Param is foobarbaz");
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('attrib1' => 'foo', 'attrib2' => 'bar', 'attrib3' => 'baz')));

        $controller = new TestController($this->slim);
        $controller->paramSingleArrayAction();
    }

    public function testMultiParamAccessWorks()
    {
        $this->expectOutputString('{"Some.param":"foo","Other.param":"bar"}');
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo'), 'Other' => array('param' => 'bar')));

        $controller = new TestController($this->slim);
        $controller->paramMultiAction();
    }

    public function testMultiParamAccessWithRequiredParams()
    {
        $this->expectOutputString('{"Some.param":"foo","Other.param":"bar"}');
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo'), 'Other' => array('param' => 'bar')));

        $controller = new TestController($this->slim);
        $controller->paramMultiMissingReqAction();
    }

    public function testMultiParamAccessWithRequiredParamsWhichAreMissing()
    {
        $this->expectOutputString('null');
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo')));

        $controller = new TestController($this->slim);
        $controller->paramMultiMissingReqAction();
    }

    public function testMultiParamAccessWithDefaultValues()
    {
        $this->expectOutputString('{"Some.param":"foo","Other.bla":"great"}');
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->once()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo')));

        $controller = new TestController($this->slim);
        $controller->paramMultiDefaultAction();
    }

    public function testGetAllAvailableParams()
    {
        $this->expectOutputString('{"Some.param":"foo","Other":"bar"}');
        $this->assertDefaultConstruction();
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->twice()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->twice()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo'), 'Other' => 'bar'));

        $controller = new TestController($this->slim);
        $controller->paramGetAllAction();
    }

    public function testGetAllAvailableParamsWithPrefix()
    {
        $this->expectOutputString('{"Some.param":"foo"}');
        $this->assertDefaultConstruction('Suffix', 'data');
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->twice()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->twice()
            ->withNoArgs()
            ->andReturn(array('data' => array('Some' => array('param' => 'foo')), 'Other' => 'bar'));

        $controller = new TestController($this->slim);
        $controller->paramGetAllAction();
    }

    public function testUseSimpleNoCleanup()
    {
        $this->expectOutputString('{"Some.param":"foo<bla>","Other":"bar"}');
        $this->assertDefaultConstruction('Suffix', '', false);
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->twice()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->twice()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo<bla>'), 'Other' => 'bar'));

        $controller = new TestController($this->slim);
        $controller->paramGetAllAction();
    }

    public function testUseSimpleCleanup()
    {
        $this->expectOutputString('{"Some.param":"foobla","Other":"bar"}');
        $this->assertDefaultConstruction('Suffix', '', true);
        $request = m::mock();
        $this->slim->shouldReceive('request')
            ->twice()
            ->withNoArgs()
            ->andReturn($request);
        $request->shouldReceive('params')
            ->twice()
            ->withNoArgs()
            ->andReturn(array('Some' => array('param' => 'foo<bla>'), 'Other' => 'bar'));

        $controller = new TestController($this->slim);
        $controller->paramGetAllAction();
    }

    public function testArrayCleanup()
    {
        $this->expectOutputString('["foobar","otherNotgood"]');
        $this->assertDefaultConstruction('Suffix', '', true);
        $controller = new TestController($this->slim);
        $controller->paramCleanupAction();
    }


    protected function assertDefaultConstruction($suffix = 'Suffix', $paramPrefix = '', $cleanupParams = false)
    {
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.template_suffix')
            ->andReturn($suffix);
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.param_prefix')
            ->andReturn($paramPrefix);
        $this->slim->shouldReceive('config')
            ->once()
            ->with('controller.cleanup_params')
            ->andReturn($cleanupParams);
    }

}
