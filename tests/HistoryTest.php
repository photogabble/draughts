<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use Photogabble\Draughts\History;
use Photogabble\Draughts\Move;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{

    public function testHistoryClass()
    {
        $move = new Move(['from' => 32, 'to' => 28]);
        $history = new History($move, 'W', 1);

        $this->assertSame($move, $history->move);
        $this->assertEquals('W', $history->turn);
        $this->assertEquals(1, $history->moveNumber);
    }

    /**
     * @throws \Exception
     */
    public function testHistory()
    {
        $tests = [
            [
                'moves' => [
                    new Move(['from' => 32, 'to' => 28]),
                    new Move(['from' => 17, 'to' => 21]),
                    new Move(['from' => 37, 'to' => 32]),
                    new Move(['from' => 20, 'to' => 25]),
                ],
                'verbose' => false,
                'history' => [
                    '"32-28"',
                    '"17-21"',
                    '"37-32"',
                    '"20-25"',
                ],
                'fen' => 'W:W28,31,32,33,34,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,19,21,25',
            ],
            [
                'moves' => [
                    new Move(['from' => 32, 'to' => 28]),
                    new Move(['from' => 17, 'to' => 21]),
                    new Move(['from' => 37, 'to' => 32]),
                    new Move(['from' => 20, 'to' => 25]),
                ],
                'verbose' => true,
                'history' => [
                    '{"from":32,"to":28,"flags":"n","piece":"w","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}',
                    '{"from":17,"to":21,"flags":"n","piece":"b","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}',
                    '{"from":37,"to":32,"flags":"n","piece":"w","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}',
                    '{"from":20,"to":25,"flags":"n","piece":"b","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}'
                ],
                'fen' => 'W:W28,31,32,33,34,35,36,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,18,19,21,25',
            ],
            [
                'moves' => [
                    new Move(['from' => 32, 'to' => 27]),
                    new Move(['from' => 16, 'to' => 21]),
                    new Move(['from' => 27, 'to' => 16]),
                ],
                'verbose' => false,
                'history' => [
                    '"32-27"',
                    '"16-21"',
                    '"27x16"',
                ],
                'fen' => 'B:W16,31,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,17,18,19,20',
            ],
            [
                'moves' => [
                    new Move(['from' => 32, 'to' => 27]),
                    new Move(['from' => 16, 'to' => 21]),
                    new Move(['from' => 27, 'to' => 16]),
                ],
                'verbose' => true,
                'history' => [
                    '{"from":32,"to":27,"flags":"n","piece":"w","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}',
                    '{"from":16,"to":21,"flags":"n","piece":"b","captures":[],"piecesCaptured":[],"jumps":[],"takes":[],"piecesTaken":null}',
                    '{"from":27,"to":16,"flags":"c","piece":"w","captures":"21","piecesCaptured":["b"],"jumps":[27,16],"takes":[21],"piecesTaken":["b"]}',
                ],
                'fen' => 'B:W16,31,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50:B1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,17,18,19,20',
            ]
        ];

        foreach ($tests as $test) {
            $draughts = new Draughts();

            foreach($test['moves'] as $move) {
                $draughts->move($move);
            }

            $history = $draughts->getHistory($test['verbose']);
            $this->assertEquals($test['fen'], $draughts->generateFen());
            $this->assertCount(count($test['moves']), $history);
            foreach ($test['history'] as $i => $check) {
                $this->assertEquals($check, $history[$i]);
            }
        }
    }
}