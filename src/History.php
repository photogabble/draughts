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

    private $signs = [
        'n' => '-',
        'c' => 'x'
    ];

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

    /**
     * This originates from the getHistory method on the original source
     * as a way of returning an array containing string
     * representations of the move.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1111
     * @param bool $pretty
     * @return string
     */
    public function history(bool $pretty = false): string
    {
        if ($pretty === true) {
            $arr = $this->move->toArray();
            if ($this->move->flags === 'c') {
                $arr['captures'] = implode(',', $this->move->captures);
            }
            return json_encode($arr);
        }

        return json_encode($this->move->from . $this->signs[$this->move->flags] . $this->move->to);
    }
}