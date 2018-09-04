<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\CaptureState;
use Photogabble\Draughts\Move;
use PHPUnit\Framework\TestCase;

class DataContainerTest extends TestCase
{
    public function testMoveClass()
    {
        $move = new Move(['jumps' => ['abc', '123', 'xyz']]);
        $this->assertEquals(['abc', '123', 'xyz'], $move->jumps);

        $clone = clone($move);
        $this->assertEquals(['abc', '123', 'xyz'], $clone->jumps);

        $this->assertNotSame($move, $clone);

        $clone->to = 'hello';

        $this->assertEquals('hello', $clone->to);
        $this->assertNull($move->to);
    }

    public function testCaptureStateClass()
    {
        $state = new CaptureState('abc');
        $this->assertEquals('abc', $state->position);
        $this->assertEquals('', $state->dirFrom);

        $clone = clone($state);
        $this->assertEquals('abc', $clone->position);
        $this->assertEquals('', $clone->dirFrom);

        $this->assertNotSame($state, $clone);

        $clone->dirFrom = '123';

        $this->assertEquals('123', $clone->dirFrom);
        $this->assertEquals('', $state->dirFrom);
    }
}