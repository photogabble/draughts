<?php

namespace Photogabble\Draughts;

/**
 * Class History
 * This is a basic data container object.
 */
class History
{
    /**
     * @var Move
     */
    public $move;

    /**
     * @var string
     */
    public $turn;

    /**
     * @var int
     */
    public $moveNumber;

    /**
     * History constructor.
     * @param Move $move
     * @param string $turn
     * @param int $moveNumber
     */
    public function __construct(Move $move, string $turn, int $moveNumber)
    {
        $this->move = $move;
        $this->turn = $turn;
        $this->moveNumber = $moveNumber;
    }
}