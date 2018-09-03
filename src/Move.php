<?php

namespace Photogabble\Draughts;

/**
 * Class Move
 * This is a basic data container object.
 */
class Move
{
    public $from;
    public $to;
    public $flags;
    public $piece;
    public $captures = [];
    public $piecesCaptured = [];
    public $jumps = [];
    public $piecesTaken = [];
}