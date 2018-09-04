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

        // Remove tailing dots
        $this->fen = preg_replace('/\..*$/', '', $this->fen);

        if ($this->fen === '') {
            $this->error = $this->errors[7];
            return;
        }

        if ($fen === 'B::' || $fen === 'W::' || $fen === '?::') {
            $this->fen .= ':B:W'; // exception allowed i.e. empty fen
            return;
        }

        if (substr($this->fen, 1, 1) !== ':') {
            $this->error = $this->errors[2];
            return;
        }

        // fen should be 3 sections separated by colons
        $parts = explode(':', $this->fen);
        if (count($parts) !== 3) {
            $this->error = $this->errors[2];
            return;
        }

        //  which side to move
        $turnColour = $parts[0];
        if (!in_array($turnColour, ['B', 'W', '?'])) {
            $this->error = $this->errors[5];
            return;
        }

        // check colors of both sides
        $colours = substr($parts[1], 0, 1) . substr($parts[2], 0, 1);
        if (!in_array($colours, ['BW', 'WB'])) {
            $this->error = $this->errors[4];
            return;
        }

        // check parts for both sides
        for ($k = 1; $k <= 2; $k += 1) {
            $sideString = substr($parts[$k], 1); // Stripping color
            if (strlen($sideString) === 0) {
                continue;
            }

            $numbers = explode(',', $sideString);
            for ($i = 0; $i < count($numbers); $i++) {
                $numSquare = $numbers[$i];
                $isKing = substr($numSquare, 0, 1) === 'K';
                $numSquare = ($isKing === true ? substr($numSquare, 1) : $numSquare);

                $range = explode('-', $numSquare);
                if (count($range) === 2) {
                    if (!is_numeric($range[0])) {
                        $this->error = $this->errors[5];
                        return;
                    }
                    if (!($range[0] >= 1 && $range[0] <= 100)) {
                        $this->error = $this->errors[6];
                        return;
                    }
                    if (!is_numeric($range[1])) {
                        $this->error = $this->errors[5];
                        return;
                    }
                    if (!($range[1] >= 1 && $range[1] <= 100)) {
                        $this->error = $this->errors[6];
                        return;
                    }
                } else {
                    if (!is_numeric($numSquare)) {
                        $this->error = $this->errors[5];
                    }
                    if (!($numSquare >= 1 && $numSquare <= 100)) {
                        $this->error = $this->errors[6];
                    }
                }
            }
        }
    }

    public function isValid()
    {
        return $this->error === 'no errors';
    }
}