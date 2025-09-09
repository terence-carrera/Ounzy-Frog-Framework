<?php

namespace Ounzy\FrogFramework\Tests;

use Ounzy\FrogFramework\Core\Container;
use stdClass;

class ContainerTest extends TestCase
{
    public function run(): void
    {
        $c = new Container();
        $c->bind(stdClass::class, stdClass::class);
        $obj = $c->make(stdClass::class);
        $this->assertTrue($obj instanceof stdClass, 'Should resolve stdClass');
    }
}
