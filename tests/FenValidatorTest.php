<?php

namespace Photogabble\Draughts\Tests;

use Photogabble\Draughts\Draughts;
use Photogabble\Draughts\FenValidator;
use PHPUnit\Framework\TestCase;

class FenValidatorTest extends TestCase
{

    public function testValidateValidEmptyFEN()
    {
        $positions = [
            'B::',
            'W::',
            '?::'
        ];

        foreach ($positions as $position) {
            $validator = new FenValidator($position);
            $this->assertTrue($validator->isValid());
            $this->assertEquals($position . ':B:W', $validator->fen);
        }
    }

    public function testValidateFEN()
    {
        $positions = [
            'B::' => [
                'expected' => 'B:::B:W',
                'isValid' => true,
            ],
            'W::' => [
                'expected' => 'W:::B:W',
                'isValid' => true,
            ],
            '?::' => [
                'expected' => '?:::B:W',
                'isValid' => true,
            ],
            'W:WK4,30:B27,22.' => [
                'expected' => 'W:WK4,30:B27,22',
                'isValid' => true,
            ],
            'W:W27,19,18,11,7,6,5:B28,26,25,20,17,10,9,4,3,2.' => [
                'expected' => 'W:W27,19,18,11,7,6,5:B28,26,25,20,17,10,9,4,3,2',
                'isValid' => true,
            ],
            'B:W18,24,27,28,K10,K15:B12,16,20,K22,K25,K29' => [
                'expected' => 'B:W18,24,27,28,K10,K15:B12,16,20,K22,K25,K29',
                'isValid' => true,
            ],
            'B:W18,19,21,23,24,26,29,30,31,32:B1,2,3,4,6,7,9,10,11,12' => [
                'expected' => 'B:W18,19,21,23,24,26,29,30,31,32:B1,2,3,4,6,7,9,10,11,12',
                'isValid' => true,
            ],
            'B:W18, 19, 21, 23 ,24 ,26,29,30,31,32  : B1,2 ,3, 4,6, 7, 9,10 ,11,12   ' => [
                'expected' => 'B:W18,19,21,23,24,26,29,30,31,32:B1,2,3,4,6,7,9,10,11,12',
                'isValid' => true,
            ],
            'X:W18:' => [
                'isValid' => false,
                'error' => 'color(s) of sides of fen position not valid',
            ],
            'B:W18' => [
                'isValid' => false,
                'error' => 'fen position has not colon at second position',
            ]
        ];

        foreach ($positions as $position => $test) {
            $validator = new FenValidator($position);

            if ($test['isValid'] === true) {
                $this->assertEquals($test['expected'], $validator->fen);
            } else {
                $this->assertEquals($test['error'], $validator->error);
            }

            $this->assertEquals($test['isValid'], $validator->isValid());
        }
    }

}