<?php

namespace SlimController\Tests\Fixtures\Controller;

use SlimController\SlimController;

class TestController extends SlimController
{

    public function indexAction()
    {
        echo "What is up?";
    }

    public function helloAction($name)
    {
        echo "What is up $name?";
    }

    public function paramSingleAction()
    {
        echo "Param is " . $this->param('Some.param');
    }

    public function paramSingleArrayAction()
    {
        $obj = $this->param('Some');
        echo "Param is " . $obj['attrib1'] . $obj['attrib2'] . $obj['attrib3'];
    }

    public function paramMultiAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.missing'));
        echo json_encode($params);
    }

    public function paramMultiMissingReqAction()
    {
        $params = $this->params(array('Some.param', 'Other.param'), 'get', true);
        echo json_encode($params);
    }

    public function paramMultiDefaultAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.bla'), 'get', array('Other.bla' => 'great'));
        echo json_encode($params);
    }

    public function paramGetAllAction()
    {
        $params = $this->params();
        echo json_encode($params);
    }

    public function paramCleanupAction()
    {
        $messedUp = array('foo<bar>', '<other>Notgood');
        echo json_encode($this->cleanupParam($messedUp));
    }

    public function renderAction()
    {
        $this->render('rendertest', array('foo' => 'orotound', 'bar' => 'grandios'));
    }

    public function redirectAction()
    {
        $this->redirect('/here');
    }

    public function notSuffixedMethod()
    {
        echo "Yes, I was called";
    }

}