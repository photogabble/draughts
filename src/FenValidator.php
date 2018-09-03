<?php

namespace Photogabble\Draughts;

/**
 * Class FenValidator
 * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L189
 */
class FenValidator
{
    public $error = 'no errors';

    public $fen = '';

    private $errors = [

        0 => 'no errors',
        1 => 'fen position not a string',
        2 => 'fen position has not colon at second position',
        3 => 'fen position has not 2 colons',
        4 => 'side to move of fen position not valid',
        5 => 'color(s) of sides of fen position not valid',
        6 => 'squares of fen position not integer',
        7 => 'squares of fen position not valid',
        8 => 'empty fen position'
    ];

    public function __construct(string $fen)
    {
        $this->fen = trim(str_replace(' ', '', $fen));

        if ($fen === 'B::' || $fen === 'W::' || $fen === '?::') {
            $this->fen .= ':B:W'; // exception allowed i.e. empty fen
            return;
        }

        $this->fen = preg_replace('/\..*$/', '', $this->fen);

        if ($this->fen === '') {
            $this->error = 'squares of fen position not valid';
            return;
        }

        if (substr($this->fen, 1, 1) !== ':') {
            $this->error = 'fen position has not colon at second position';
            return;
        }

        // fen should be 3 sections separated by colons
        $parts = explode(':', $this->fen);
        if (count($parts) !== 3) {
            $this->error = 'fen position has not 2 colons';
            return;
        }

        // @todo

    }

    public function isValid()
    {
        return $this->error === 'no errors';
    }
}