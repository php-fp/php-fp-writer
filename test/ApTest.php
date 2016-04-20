<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid;

class ApTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionMethod('PhpFp\Writer\Writer::map'))
            ->getNumberOfParameters();

        $this->assertEquals($count, 1, 'Takes one parameter.');
    }

    public function testAp()
    {
        $a = Writer::of(5, new Monoid([]));

        $this->assertEquals(
            Writer::of(
                function ($x) {
                    return $x + 2;
                },
                new Monoid([])
            )
            ->ap($a)
            ->run() [0],
            7,
            'Applies a parameter.'
        );
    }
}
