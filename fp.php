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


class Identity
{
    private $x;

    public function __construct($x)
    {
        $this->x = $x;
    }

    public static function of($x)
    {
        return new Identity($x);
    }

    public function emit()
    {
        return $this->x;
    }

    public function inspect()
    {
        return "Identity({$this->x})";
    }

    public function chain($fn)
    {
        return $fn($this->x);
    }

    public function map($fn)
    {
        return new Identity($fn($this->x));
    }
}

class Collection extends Identity
{
    private $x;

    public function __construct($x)
    {
        $this->x = $x;
    }

    public static function of($x)
    {
        if (is_array($x)) {
            return new Collection($x);
        }

        return new Collection(array($x));
    }

    public function inspect()
    {
        return "Collection({$this->x})";
    }

    public function map($fn)
    {
        return Collection::of($fn($this->x));
    }

    public function concat($x)
    {
        return Collection::of(array_merge($this->x, $x));
    }
}

?>