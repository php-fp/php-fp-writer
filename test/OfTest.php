<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid;

class OfTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionMethod('PhpFp\Writer\Writer::of'))
            ->getNumberOfParameters();

        $this->assertEquals(
            $count,
            2,
            'Takes two parameters.'
        );
    }

    public function testApplicativeConstructor()
    {
        $this->assertEquals(
            Writer::of(2, Monoid::empty())->run() [0],
            2,
            'Constructs an applicative.'
        );
    }
}
