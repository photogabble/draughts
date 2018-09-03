<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use PHPUnit\Framework\TestCase;

class DraughtsTest extends TestCase
{

    public function testAscii()
    {
        $expected = "\n+------------------------------+\n".
                    "|\t   b   b   b   b   b  \t|\n".
                    "|\t b   b   b   b   b    \t|\n".
                    "|\t   b   b   b   b   b  \t|\n".
                    "|\t b   b   b   b   b    \t|\n".
                    "|\t   0   0   0   0   0  \t|\n".
                    "|\t 0   0   0   0   0    \t|\n".
                    "|\t   w   w   w   w   w  \t|\n".
                    "|\t w   w   w   w   w    \t|\n".
                    "|\t   w   w   w   w   w  \t|\n".
                    "|\t w   w   w   w   w    \t|\n".
                    "+------------------------------+\n";
        $draughts = new Draughts();
        $this->assertEquals($expected, $draughts->ascii());
        $n =1;
    }

    public function testPerft()
    {
        $perfts = [];

        foreach ($perfts as $perft) {
            $draughts = new Draughts();
            $draughts->load($perft['fen']);
            $nodes = $draughts->perft($perft['depth']);
            $this->assertEquals($perft['nodes'], $nodes);
        }
    }

    public function testSingleSquareMoveGeneration()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);


            $moves = $draughts->moves(['square' => $position['square'], 'verbose' =>  $position['verbose']]);
            $passed = count($position['moves']) === count($moves);

            for ($j = 0; $j < count($moves); $j++) {
                if (!$position['verbose']) {
                    $passed = $passed && $moves[$j] == $position['moves'][$j];
                } else {
                    foreach ($moves[$j] as $k) {
                        $passed = $passed && $moves[$j][$k] == $position['moves'][$j][$k];
                    }
                }
            }
            $this->assertTrue($passed);
        }
    }

    public function testInsufficientMaterial()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);

            if ($position['draw']) {
                $this->assertTrue($draughts->insufficientMaterial() && $draughts->inDraw());
            } else {
                $this->assertTrue(!$draughts->insufficientMaterial() && !$draughts->inDraw());
            }
        }
    }

    public function testThreefoldRepetition()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);
            $passed = true;
            for ($j = 0; $j < count($position['moves']); $j++) {
                if ($draughts->InThreefoldRepetition()) {
                    $passed = false;
                    break;
                }
                $draughts->move($position['moves'][$j]);
            }

            $this->assertTrue($passed && $draughts->inThreefoldRepetition() && $draughts->inDraw());
        }
    }

    public function testGetPutRemove()
    {
        $draughts = new Draughts();
        // @todo
    }

    public function testFEN()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);
            $this->assertEquals($position['should_pass'], $draughts->fen() == $position['fen']);
        }
    }

    public function testLoadFEN()
    {
        $draughts = new Draughts();
        // @todo
    }

    public function testMakeMove()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);

            // @todo
        }
    }

    public function testHistory()
    {
        $draughts = new Draughts();
        // @todo
    }
}