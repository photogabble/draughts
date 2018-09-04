<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
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
            $this->assertNotNull($draughts->move($move));
            echo $draughts->ascii(true);
        }
    }

}