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

    public $captures = [];
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
}