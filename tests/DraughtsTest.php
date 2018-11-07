<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use Photogabble\Draughts\Move;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

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
    }

    /**
     * @throws \Exception
     */
    public function testMove()
    {
        $expected = "\n+------------------------------+\n".
                    "|\t   b   b   b   b   b  \t|\n".
                    "|\t b   b   b   b   b    \t|\n".
                    "|\t   b   b   b   b   b  \t|\n".
                    "|\t 0   b   b   b   b    \t|\n".
                    "|\t   0   0   0   0   0  \t|\n".
                    "|\t w   0   0   0   0    \t|\n".
                    "|\t   0   b   w   w   w  \t|\n".
                    "|\t w   w   w   w   w    \t|\n".
                    "|\t   w   w   w   w   w  \t|\n".
                    "|\t w   w   w   w   w    \t|\n".
                    "+------------------------------+\n";
        $draughts = new Draughts();
        $this->assertFalse(is_null($draughts->move( new Move(['from' => 31, 'to' => 26])))); // W
        $this->assertFalse(is_null($draughts->move( new Move(['from' => 16, 'to' => 21])))); // B
        $this->assertFalse(is_null($draughts->move( new Move(['from' => 32, 'to' => 27])))); // W
        $this->assertFalse(is_null($draughts->move( new Move(['from' => 21, 'to' => 32])))); // B
        $this->assertEquals($expected, $draughts->ascii());
    }

    public function testPerft()
    {
        $this->markTestIncomplete('Not yet implemented');
        return;

        $perfts = [];

        foreach ($perfts as $perft) {
            $draughts = new Draughts($perft['fen']);
            $nodes = $draughts->perft($perft['depth']);
            $this->assertEquals($perft['nodes'], $nodes);
        }
    }

    public function testSingleSquareMoveGeneration()
    {
        $this->markTestIncomplete('Not yet implemented');
        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts($position['fen']);

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
        $this->markTestIncomplete('Not yet implemented');
        $this->markTestIncomplete('');
        return;

        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts($position['fen']);

            if ($position['draw']) {
                $this->assertTrue($draughts->insufficientMaterial() && $draughts->inDraw());
            } else {
                $this->assertTrue(!$draughts->insufficientMaterial() && !$draughts->inDraw());
            }
        }
    }

    public function testThreefoldRepetition()
    {
        $this->markTestIncomplete('');
        return;

        $positions = [];

        foreach ($positions as $position) {
            $draughts = new Draughts($position['fen']);
            $passed = true;
            for ($j = 0; $j < count($position['moves']); $j++) {
                if ($draughts->inThreefoldRepetition()) {
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
        $this->markTestIncomplete('Not yet implemented');
        $draughts = new Draughts();
        // @todo
    }

    /**
     * Written for issue #7
     * @see https://github.com/carbontwelve/draughts/issues/7
     * @throws \Exception
     */
    public function testLoadFEN()
    {
        $draughts = new Draughts();
        $draughts->move(new Move(['from' => 35, 'to' => 30]));
        $fen = $draughts->generateFen();
        $this->assertEquals('B:W30,31,32,33,34,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20', $fen);

        $draughts = new Draughts($fen);
        $this->assertEquals($fen, $draughts->generateFen());
    }

    /**
     * @throws \Exception
     */
    public function testMakeMove()
    {
        $positions = [
            [
                'fen' => 'W:W31-50:B1-20',
                'next' => '',
                'captured' => [],
                'move' => new Move(['from' => 17, 'to' => 22]),
                'legal' => false
            ],
            [
                'fen' => 'W:W31-50:B1-20',
                'next' => '',
                'captured' => [],
                'move' => new Move(['from' => 31, 'to' => 36]),
                'legal' => false
            ],
            [
                'fen' => 'W:W31-50:B1-20',
                'next' => '',
                'captured' => [],
                'move' => new Move(['from' => 20, 'to' => 15]),
                'legal' => false
            ],
            [
                'fen' => 'W:W31-50:B1-20',
                'next' => 'B:W30,31,32,33,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20',
                'captured' => [],
                'move' => new Move(['from' => 34, 'to' => 30]),
                'legal' => true
            ],
            [
                'fen' => 'W:W30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20,24',
                'next' => 'B:W19,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20',
                'captured' => [24],
                'move' => new Move(['from' => 30, 'to' => 19]),
                'legal' => true
            ],
        ];

        foreach ($positions as $position) {
            $draughts = new Draughts($position['fen']);
            $result = $draughts->move($position['move']);

            if ($position['legal'] === true){
                $this->assertNotNull($result);
                $this->assertEquals($position['next'], $draughts->generateFen());
                $this->assertEquals($position['captured'], $result->captures);

            } else {
                $this->assertNull($result);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function testSettingHeaders() {
        $draughts = new Draughts;
        $draughts->setHeader([
            'foo' => 'bar',
            'baz' => 'qez',
        ]);

        $draughtsReflection = new ReflectionObject($draughts);
        $headerProperty = $draughtsReflection->getProperty( 'header' );
        $headerProperty->setAccessible( true );

        $headers = $headerProperty->getValue($draughts);

        $this->assertArrayHasKey('foo', $headers);
        $this->assertArrayHasKey('baz', $headers);

        $this->assertEquals($headers['foo'], 'bar');
        $this->assertEquals($headers['baz'], 'qez');
    }
}