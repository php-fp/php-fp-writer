# The Writer Monad for PHP. [![Build Status](https://travis-ci.org/php-fp/php-fp-writer.svg?branch=master)](https://travis-ci.org/php-fp/php-fp-writer)

## Intro

The Writer monad is used for logging within a computation. Internally, the Writer stores two values: a computation value (like any other monad), and a monoid value that stores the log. A monoid must have an `empty` value and a `concat` method:

```php
<?php

class Monoid
{
    public static function empty()
    {
        return new Monoid([]);
    }

    public function __construct(array $xs)
    {
        $this->value = $xs;
    }

    public function concat($that) : Monoid
    {
        return new Monoid(array_merge($this->value, $that->value));
    }
}
```

This quick 'n' dirty monoid internally uses an array, where the empty list is the `empty` value, and list concatenation is `concat`. Chances are that you'll use a monoid that looks roughly like this, as the logger can then build up an array of strings that describe the process:

```php
<?php

use PhpFp\Writer\Writer;
// And your monoid from above...

$halve = function ($number) {
    $log = new Monoid(['Halving the number']);

    return Writer::tell('Halving the number')->map(
        function () use ($number)
        {
            return $number / 2;
        }
    );
};

list ($xs, $log) = $halve(16)->chain($halve)->run();

assert($xs == 4); // The inner value is halved twice.
assert($log->value == ['Halving the number', 'Halving the number']);
```

Although not the only way to log (you can return a Writer that you built yourself), I find that `tell` provides the most readable approach. More information about _how_ `tell` works can be found in its API documentation below.

Of course, `map` transforms the inner value without affecting the outer structure. In other words, use `map` when no logging should be done, and `chain` when you want to log something (probably using `tell`). This is a monad that, in truth, is probably most useful for explaining complicated computations, though you _could_ use it for more! What about a Writer in your app's control flow that builds up a server error log? Or an activity log for a user?

## API

In the following type signatures, constructors and static functions are written as one would see in pure languages such as Haskell. The others contain a pipe, where the type before the pipe represents the type of the current IO instance, and the type after the pipe represents the function.

### `of :: Monoid m => a -> m b -> Writer a (m b)`

This method is poorly named, but is usually called `of` by similar projects. If `of` is making you think that this be an applicative constructor, then don't believe a word - it isn't. An applicative constructor wraps a value, and thus should only take one value. However, a Writer can't be constructed without knowing what the monoid is, so that must be passed as well.

It is, however, a handy way to produce Writer instances without worrying about the internals, which is good news:

```php
<?php

use PhpFp\Writer\Writer;

assert(Writer::of(2, 'PhpFp\Maybe\Maybe')->run() [0] == 2);
```

Due to PHP's frankly bizarre type system, you can either pass in an instance (which might be overkill, given that Writer just calls `::empty`), or the monoid class name as a string (just remember that this won't pay any attention to your `use` statements).

### `tell :: Monoid m => m b -> Writer a m b`

This is the trickery that makes Writer so useful. This (static) function will return a Writer with a null value and a given monoid. This is particularly useful in `chain` calls, because it provides a neat way to add things to the log (without having to construct a Writer by hand):

```php
<?php

use PhpFp\Writer\Writer;

list($x, $log) = Writer::of(2, 'PhpFp\Maybe\Maybe')->chain(
    function ($x)
    {
        return Writer::tell(Maybe::of('BLAH'))->map(
            function ($_) use ($x)
            {
                // We can still access the old value!
                return $x + 2;
            }
        );
    }
)->run();


assert($x == 4);
assert($log->fork(null) == 'BLAH');
```

### `construct :: Monoid m => (-> (a, m b)) -> Writer a m b`

Sometimes -- probably in chained methods -- you'll want to construct your own Writers, usually because you want to add some data to the log. In which case, this function is for you. The type signature is unfortunately slightly clunky: you need to pass in a function (with no arguments) that returns a value/monoid pair.

```php
<?php

use PhpFp\Writer\Writer;

list ($x, $log) = new Writer(function () {
    return [2, new Monoid(['Hello!'])];
})->run();

assert($x == 2);
assert($log->value == ['Hello!']);
```

### `ap :: Monoid m => Writer (a -> b) m c | Writer a m c -> Writer b m c`

Standard application for Writer instances, derived from `chain`. This can be used like any application with an applicative functor (although the monoid types must match in order to be type-safe; behaviour is undefined if you don't!):

```php
<?php
$a = Writer::of(5, new Monoid([]));

list ($x, $log) = Writer::of(
    function ($x) {
        return $x + 2;
    },
    new Monoid([])
)->ap($a)->run();

assert ($x == 7);
assert ($log->value == []);
```

### `chain :: Monoid m => Writer a m c | (a -> Writer b m c) -> Writer b m c`

The process for chaining Writers is straightforward: a new action is created, in which the action so far is executed to make `$value1` and `$log1`. Then, the chaining function is called on `$value1` to make `$value2` and `$log2`. Finally, the returned Writer holds `[$value2, $log2->concat($log1)]`.

If this seems complicated, don't worry - you can do everything you'll need to do with tell for now, and chain Writer-returning methods in blissful ignorance!

```php
<?php

use PhpFp\Writer\Writer;

list ($x, $log) = Writer::of(2, 'PhpFp\Maybe\Maybe')
    ->chain(
        function ($x) {
            return Writer::of(
                2 * $x,
                'PhpFp\Maybe\Maybe'
            );
        }
    )
    ->run();

assert($x == 4);
assert($log == PhpFp\Maybe\Maybe::empty());
```

### `map :: Monoid m => Writer a m c | (a -> b) -> Writer b m c`

Like all monads, Writer is a functor, and the `map` method can be derived using `chain`. Mapping allows you to transform the inner value, but _not_ the log: that can only be added to. More complicated interactions can be made possible by the State monad.

```php
<?php

use PhpFp\Writer\Writer;
use PhpFp\Maybe\Maybe;

list ($x, $log) = Writer::of(2, 'PhpFp\Maybe\Maybe')->map(
    function ($x)
    {
        return $x * 2;
    }
)->run();

assert($x == 4);
assert($log == Maybe::empty());
```

### `run :: Monoid m => Writer a m b | (a, m b)`

At the end of your computation, you'll want to get the result out of the Writer instance. You'll get a pair from `run`: the value, and the monoid log, respectively. PHP's `list` constructor makes this look quite neat:

```php
<?php

use PhpFp\Writer\Writer;
use PhpFp\Maybe\Maybe;

list ($x, $log) = Writer::of(2, 'PhpFp\Maybe\Maybe')->run();

assert($x == 2);
assert(Maybe::empty()->equals($log));
```
