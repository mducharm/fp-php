<?php namespace fp;
// An assortment of functional programming constructs.

function partial()
{
    // Borrowed from here:
    // https://eddmann.com/posts/using-partial-application-in-php/
    $args = func_get_args();
    $func = array_shift($args);

    return function () use ($func, $args) {
        return call_user_func_array($func, array_merge($args, func_get_args()));
    };
}

function compose(...$funcs)
{
    // Combines an arbitrary number of functions into a new function; the output of each function is passed into the next function's parameters
    return array_reduce(
        $funcs,
        fn ($carry, $item) => fn ($x) => $item($carry($x)),
        fn($x) => $x
    );
}

// Inspired by Rob Porter's article: https://dev.to/rgeraldporter/building-expressive-monads-in-javascript-introduction-23b
class Monad 
{
    // Methods that all monads should inherit.
    public function __construct($x)
    {
        $this->x = $x;
    }

    // Any class extending Monad must implement it's own 'of' method - this method does any type-checking needed and calls the constructor.
    public static function of($x)
    {
    }

    public function inspect()
    {
        return get_class($this) . "({$this->x})";
    }

    public function emit()
    {
        return $this->x;
    }

    public function chain($fn)
    {
        return $fn($this->x);
    }

    public function map($fn)
    {
        return $this::of($fn($this->x));
    }

    // Accepts an array of functions, passing the output of each function as a parameter for the next.
    public function pipe()
    {
        return array_reduce(
            func_get_args(),
            fn($carry, $item) => $carry->map($item),
            $this
        );

    }
}


class Identity extends Monad
{
    protected $x;

    public static function of($x)
    {
        return new Identity($x);
    }
}


class Collection extends Monad
{
    protected $x;

    public static function of($x)
    {
        if (is_array($x)) {
            return new Collection($x);
        }

        return new Collection(array($x));
    }

    public function inspect()
    {
        return get_class($this) . "(" . implode(", ", $this->x) . ")" ;
    }

    public function concat($x)
    {
        return Collection::of(array_merge($this->x, $x));
    }
}


class Maybe extends Monad
{
    protected $x;

    public static function of($x)
    {
        if (is_null($x) || !isset($x) || $x->isNothing) {
            return new Nothing();
        }

        return new Just($x);
    }
}

class Just extends Monad
{
    protected $x;
    public $isNothing = false;
    public $isJust = true;

    public function map($fn)
    {
        return Maybe::of($fn($this->x));
    }

    public function fork($f, $g)
    {
        return $g($this->x);
    }
}

class Nothing extends Monad
{
    public $isNothing = true;
    public $isJust = false;

    public function __construct()
    {
    }

    public function fork($f, $g)
    {
        return $f();
    }

    public function map($fn)
    {
        return new Nothing();
    }
}

?>