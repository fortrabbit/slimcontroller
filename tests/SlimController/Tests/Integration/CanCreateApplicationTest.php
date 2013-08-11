<?php

namespace SlimController\Tests\Integration;

class CanCreateApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testCanCreateSimpleApplication()
    {
        $app = new \SlimController\Slim();
        $this->assertTrue(true); // if we got this far then creating the application worked
    }

}
