<?php

namespace Photogabble\Draughts;

/**
 * Class Move
 * This is a basic data container object.
 */
class Move
{
    /**
     * @var int
     */
    public $from;

    /**
     * @var int
     */
    public $to;

    /**
     * @var string
     */
    public $flags;

    /**
     * @var string
     */
    public $piece;

    /**
     * @var array
     */
    public $captures = [];

    /**
     * @var array
     */
    public $piecesCaptured = [];

    /**
     * @var array
     */
    public $jumps = [];

    /**
     * @var array
     */
    public $takes = [];

    /**
     * @var array|null
     */
    public $piecesTaken = null;

    public function __construct($properties = [])
    {
        $publicProperties = array_keys(call_user_func('get_object_vars', $this));
        foreach ($properties as $key => $value) {
            if (!in_array($key, $publicProperties)) {
                continue;
            }
            $this->{$key} = $value;
        }
    }

    /**
     * Ensure clone creates a new instance.
     *
     * @return Move
     */
    public function __clone()
    {
        return new Move($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return call_user_func('get_object_vars', $this);
    }
}