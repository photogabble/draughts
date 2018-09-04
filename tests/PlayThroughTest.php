<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use Photogabble\Draughts\Move;
use PHPUnit\Framework\TestCase;

class PlayThroughTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testRandomMatch()
    {
        $draughts = new Draughts();
        echo $draughts->ascii(true);
        while (!$draughts->gameOver())
        {
            $moves = $draughts->getMoves();
            $move = $moves[array_rand($moves, 1)];
            $r = $draughts->move($move);
            if (is_null($r)){
                $n = 1;
            }
            $this->assertNotNull($r);
            echo $draughts->ascii(true);
        }
    }

    public function testFixedMatch()
    {
        $turns = [
            new Move(['from' => 32, 'to' => 28]), // W
            new Move(['from' => 20, 'to' => 25]), // B
            new Move(['from' => 35, 'to' => 30]), // W
            new Move(['from' => 17, 'to' => 22]), // B
            new Move(['from' => 30, 'to' => 24]), // W
        ];
        $draughts = new Draughts();
        foreach ($turns as $turn) {
            $r = $draughts->move($turn);
            $fen = $draughts->generatePDN();
            if (is_null($r)){
                $n = 1;
            }
        }
    }

}