<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid;

class MapTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionMethod('PhpFp\Writer\Writer::map'))
            ->getNumberOfParameters();

        $this->assertEquals($count, 1, 'Takes one parameter.');
    }

    public function testMap()
    {
        $this->assertEquals(
            (
                new Writer(
                    function ()
                    {
                        return [0, 0];
                    }
                )
            )
            ->map(
                function ($x)
                {
                    return $x + 5;
                }
            )
            ->run(),
            [5, 0],
            'Maps over a Writer.'
        );
    }
}
