<?php

namespace Controller;

class Test extends \SlimController\SlimController {

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
        echo "Param is ". $this->param('Some.param');
    }

    public function paramMultiAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.missing'));
        if ($params && isset($params['Some.param']) && isset($params['Other.param'])) {
            echo "All is ". $params['Some.param']. ' '. $params['Other.param'];
        } else {
            echo "FAIL";
        }
    }

    public function paramMultiMissingAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.bla'));
        if ($params && isset($params['Some.param']) && isset($params['Other.param']) && !isset($params['Other.bla'])) {
            echo "All is ". $params['Some.param']. ' '. $params['Other.param'];
        } else {
            echo "FAIL";
        }
    }

    public function paramMultiMissingReqAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.bla'), 'get', true);
        echo !$params ? "OK" : "FAIL";
    }

    public function paramMultiDefaultAction()
    {
        $params = $this->params(array('Some.param', 'Other.param', 'Other.bla'), 'get', array('Other.bla' => 'great'));
        if ($params) {
            echo "All is ". $params['Some.param']. ' '. $params['Other.param']. ' and '. $params['Other.bla'];
        } else {
            echo "FAIL";
        }
    }

    public function renderAction()
    {
        $this->render('rendertest', array('foo' => 'orotound', 'bar' => 'grandios'));
    }
}