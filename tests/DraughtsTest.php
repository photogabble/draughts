<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use PHPUnit\Framework\TestCase;

class DraughtsTest extends TestCase
{

    public function perftTest()
    {
        $perfts = [];

        foreach ($perfts as $perft) {
            $draughts = new Draughts();
            $draughts->load($perft['fen']);
            $nodes = $draughts->perft($perft['depth']);
            $this->assertEquals($perft['nodes'], $nodes);
        }
    }

    public function singleSquareMoveGenerationTest()
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

    public function insufficientMaterialTest()
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

    public function threefoldRepetitionTest()
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

    public function getPutRemoveTest()
    {
        $draughts = new Draughts();
        // @todo
    }

    public function FENTest()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);
            $this->assertEquals($position['should_pass'], $draughts->fen == $position['fen']);
        }
    }

    public function loadFENTest()
    {
        $draughts = new Draughts();
        // @todo
    }

    public function makeMoveTest()
    {
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts();
            $draughts->load($position['fen']);

            // @todo
        }
    }

    public function historyTest()
    {
        $draughts = new Draughts();
        // @todo
    }
}