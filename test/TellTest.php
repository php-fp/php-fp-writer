<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid;

class TellTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionMethod('PhpFp\Writer\Writer::tell'))
            ->getNumberOfParameters();

        $this->assertEquals(
            $count,
            1,
            'Takes one parameter.'
        );
    }

    public function testTell()
    {
        $writer = Writer::of(2, Monoid::empty());

        list ($xs, $log) = $writer->chain(
            function ($x) use ($writer)
            {
                $log = new Monoid(['Hello, world!']);

                return $writer->tell($log)->map(
                    function ($_) use ($x)
                    {
                        return $x * 2;
                    }
                );
            }
        )->run();

        $this->assertEquals($xs, 4, 'Maps correctly.');

        $this->assertEquals(
            $log->value,
            ['Hello, world!'],
            'Logs correctly.'
        );
    }
}
