<?php

namespace PhpFp\Writer\Test\Double;

/**
 * A quick 'n' dirty monoid for testing. Essentially a list monad.
 */
class Monoid
{
    /**
     * The empty list monoid.
     * @return Monoid
     */
    public static function empty() : Monoid
    {
        return new Monoid([]);
    }

    /**
     * Construct the monoid from a list.
     * @param array $xs
     */
    public function __construct(array $xs)
    {
        $this->value = $xs;
    }

    /**
     * Concatenate two of this monoid.
     * @param Monoid $that
     * @return Monoid
     */
    public function concat($that) : Monoid
    {
        return new Monoid(
            array_merge(
                $this->value,
                $that->value
            )
        );
    }
}
