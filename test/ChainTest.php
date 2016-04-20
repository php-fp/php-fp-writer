<?php

namespace PhpFp\Writer\Test;

use PhpFp\Writer\Writer;
use PhpFp\Writer\Test\Double\Monoid as _;

class ChainTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterCount()
    {
        $count = (new \ReflectionMethod('PhpFp\Writer\Writer::chain'))
            ->getNumberOfParameters();

        $this->assertEquals($count, 1, 'Takes one parameter.');
    }

    public function testChain()
    {
        $halve = function ($number) {
            $log = new _(['Halving the number']);

            return Writer::tell($log)->map(
                function () use ($number)
                {
                    return $number / 2;
                }
            );
        };

        list ($xs, $log) = $halve(16)->chain($halve)->run();

        $this->assertEquals($xs, 4, 'Chains the value.');

        $this->assertEquals(
            $log->value,
            ['Halving the number', 'Halving the number'],
            'Chains the log.'
        );
    }
}
