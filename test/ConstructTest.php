<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid;

class ConstructTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionClass('PhpFp\Writer\Writer'))
            ->getConstructor()->getNumberOfParameters();

        $this->assertEquals($count, 1, 'Takes one parameter.');
    }

    public function testConstructor()
    {
        $this->assertEquals(
            (
                new Writer(
                    function ()
                    {
                        return [0, 0];
                    }
                )
            )->run(),
            [0, 0],
            'Constructs a Writer.'
        );
    }
}
