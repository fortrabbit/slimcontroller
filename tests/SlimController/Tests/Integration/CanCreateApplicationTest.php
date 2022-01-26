<?php

namespace SlimController\Tests\Integration;

class CanCreateApplicationTest extends \PHPUnit\Framework\TestCase
{

    public function testCanCreateSimpleApplication()
    {
        $app = new \SlimController\Slim();
        static::assertTrue(true); // if we got this far then creating the application worked
    }

}
