<?php

namespace Photogabble\Draughts;

class Draughts
{
    const BLACK = 'B';
    const WHITE = 'W';
    const MAN = 'b';
    const KING = 'w';
    const FLAG_NORMAL = 'n';
    const FLAG_CAPTURE = 'c';
    const FLAG_PROMOTION = 'p';
    const SQUARES = 'A8';

    private $symbols = 'bwBW';

    private $defaultFEN = 'W:W31-50:B1-20';

    private $position;

    private $defaultPositionInternal = '-bbbbbbbbbb-bbbbbbbbbb-0000000000-wwwwwwwwww-wwwwwwwwww-';

    private $defaultPositionExternal = 'Wbbbbbbbbbbbbbbbbbbbb0000000000wwwwwwwwwwwwwwwwwwww';

    private $steps = [
        'NE' => -5,
        'SE' => 6,
        'SW' => 5,
        'NW' => -6
    ];

    private $possibleResults = ['2-0', '0-2', '1-1', '0-0', '*', '1-0', '0-1'];

    private $unicodes = [
        'w' => '\u26C0',
        'b' => '\u26C2',
        'B' => '\u26C3',
        'W' => '\u26C1',
        '0' => '\u0020\u0020'
    ];

    private $signs = [
        'n' => '-',
        'c' => 'x'
    ];

    private $bits = [
        'NORMAL' => 1,
        'CAPTURE' => 2,
        'PROMOTION' => 4
    ];

    private $turn = self::WHITE;

    private $moveNumber = 1;

    private $history = [];

    private $header = [];

    public function __construct($fen = null)
    {
        $this->position = $this->defaultPositionInternal;
        $this->load(is_null($fen) ? $this->defaultFEN : $fen);
    }

    public function load($fen)
    {
        // @todo
    }

    public function reset()
    {
        // @todo
    }

    public function moves()
    {
        // @todo
    }

    public function gameOver()
    {
        // @todo
    }

    public function inDraw()
    {
        // @todo
    }

    public function validateFen($fen)
    {
        // @todo
    }

    public function generateFen()
    {
        // @todo
    }

    public function generatePDN()
    {
        // @todo
    }

    public function loadPDN($pdn)
    {
        // @todo
    }

    public function parsePDN()
    {
        // @todo
    }

    public function header($args)
    {
        // @todo
    }

    public function ascii()
    {
        // @todo
    }

    public function turn()
    {
        // @todo
    }

    public function move($move)
    {
        // @todo
    }

    public function getMoves()
    {
        // @todo
    }

    public function getLegalMoves()
    {
        // @todo
    }

    public function undo()
    {
        // @todo
    }

    public function clear()
    {
        // @todo
    }

    public function put($piece, $square)
    {
        // @todo
    }

    public function get($square)
    {
        // @todo
    }

    public function remove($square)
    {
        // @todo
    }

    public function perft($depth)
    {
        // @todo
    }

    public function history()
    {
        // @todo
    }

    public function convertMoves()
    {
        // @todo
    }

    public function convertNumber()
    {
        // @todo
    }

    public function convertPosition()
    {
        // @todo
    }

    public function outsideBoard()
    {
        // @todo
    }

    public function directionStrings()
    {
        // @todo
    }

    public function oppositeDir()
    {
        // @todo
    }

    public function validDir()
    {
        // @todo
    }

    public function position()
    {
        // @todo
    }

    public function makeClone()
    {
        // @todo
    }

    public function makePretty()
    {
        // @todo
    }

    public function captures()
    {
        // @todo
    }
}