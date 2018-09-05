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
            $moves = $draughts->generateMoves();
            $move = $moves[array_rand($moves, 1)];
            $this->assertNotNull($draughts->move($move));
            echo $draughts->ascii(true);
        }
        $this->assertTrue($draughts->gameOver());
    }

    /**
     * This was written for issue #2.
     * @throws \Exception
     */
    public function testGetMoves()
    {
        $turns = [
            new Move(['from' => 32, 'to' => 28]), // W
            new Move(['from' => 17, 'to' => 21]), // B
            new Move(['from' => 37, 'to' => 32]), // W
            new Move(['from' => 20, 'to' => 25]), // B
            new Move(['from' => 35, 'to' => 30]), // W
            new Move(['from' => 21, 'to' => 26]), // B
            new Move(['from' => 28, 'to' => 23]), // W
        ];
        $draughts = new Draughts();
        foreach ($turns as $turn) {
            $this->assertNotNull($draughts->move($turn));
        }

        $validTurns = $draughts->generateMoves();
        // only two valid moves 19-28 and 18-29
        $this->assertCount(2, $validTurns);
    }

    /**
     * @throws \Exception
     */
    public function testFixedMatch()
    {
        $turns = [
            new Move(['from' => 32, 'to' => 28]), // W
            new Move(['from' => 20, 'to' => 25]), // B
            new Move(['from' => 35, 'to' => 30]), // W
            new Move(['from' => 17, 'to' => 22]), // B
            new Move(['from' => 28, 'to' => 17]), // W
        ];
        $draughts = new Draughts();
        foreach ($turns as $turn) {
            $this->assertNotNull($draughts->move($turn));
        }
    }

}