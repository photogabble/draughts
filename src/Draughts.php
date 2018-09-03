<?php

namespace Photogabble\Draughts;

class Draughts
{
    const BLACK = 'B';
    const WHITE = 'W';
    const MAN = 'b';
    const KING = 'w';
    const FLAG_NORMAL = 'n';
    const FLAG_CAPTURE = 'c';
    const FLAG_PROMOTION = 'p';
    const SQUARES = 'A8';

    private $symbols = 'bwBW';

    private $defaultFEN = 'W:W31-50:B1-20';

    private $position;

    private $defaultPositionInternal = '-bbbbbbbbbb-bbbbbbbbbb-0000000000-wwwwwwwwww-wwwwwwwwww-';

    private $defaultPositionExternal = 'Wbbbbbbbbbbbbbbbbbbbb0000000000wwwwwwwwwwwwwwwwwwww';

    private $steps = [
        'NE' => -5,
        'SE' => 6,
        'SW' => 5,
        'NW' => -6
    ];

    private $possibleResults = ['2-0', '0-2', '1-1', '0-0', '*', '1-0', '0-1'];

    private $unicodes = [
        'w' => '\u26C0',
        'b' => '\u26C2',
        'B' => '\u26C3',
        'W' => '\u26C1',
        '0' => '\u0020\u0020'
    ];

    private $signs = [
        'n' => '-',
        'c' => 'x'
    ];

    private $bits = [
        'NORMAL' => 1,
        'CAPTURE' => 2,
        'PROMOTION' => 4
    ];

    private $turn = self::WHITE;

    private $moveNumber = 1;

    /**
     * @var array|History[]
     */
    private $history = [];

    private $header = [];

    /**
     * Draughts constructor.
     *
     * @param string|null $fen
     * @throws \Exception
     */
    public function __construct(string $fen = null)
    {
        $this->position = $this->defaultPositionInternal;
        $this->load(is_null($fen) ? $this->defaultFEN : $fen);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L113
     */
    public function clear()
    {
        $this->position = $this->defaultPositionInternal;
        $this->turn = self::WHITE;
        $this->moveNumber = 1;
        $this->history = [];
        $this->header = [];
        // @todo update_setup(generate_fen())
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L126
     * @param null $fen
     * @return bool
     * @throws \Exception
     */
    public function load($fen = null): bool
    {
        if (is_null($fen) || $fen === $this->defaultFEN) {
            $this->position = $this->defaultPositionInternal;
            // @todo update_setup(generate_fen(position))
            return true;
        }

        // fen_constants(dimension) //TODO for empty fens

        $checkedFen = $this->validateFen($fen);
        if (!$checkedFen->isValid()) {
            throw new \Exception($checkedFen->error);
        }

        $this->clear();

        // The validator has already removed spaces and suffixes.
        // So no need to repeat that here.
        $fen = $checkedFen->fen;

        $tokens = explode(':', $fen);

        // Which side to move
        $this->turn = substr($tokens[0], 0, 1);

        // Positions
        $externalPosition = $this->defaultPositionExternal;

        for ($i = 1; $i <= strlen($externalPosition); $i++) {
            $externalPosition = $this->setCharAt($externalPosition, 1, 0);
        }

        $externalPosition = $this->setCharAt($externalPosition, 0, $this->turn);

        // @todo refactor
        for ($k = 1; $k <= 2; $k++) {
            // @todo called twice
            $colour = substr($tokens[$k], 0, 1);
            $sideString = substr($tokens[$k], 1);
            if (strlen($sideString) === 0) {
                continue;
            }
            $numbers = explode(',', $sideString);
            for ($i = 0; $i < count($numbers); $i++) {
                $numSquare = $numbers[$i];
                $isKing = substr($numSquare, 0, 1) === 'K';
                $numSquare = ($isKing === true ? substr($numSquare, 1) : $numSquare); // Strips K
                $range = explode('-', $numSquare);
                if (count($range) === 2) {
                    $from = (int)$range[0];
                    $to = (int)$range[1];
                    for ($j = $from; $j <= $to; $j++) {
                        $externalPosition = $this->setCharAt($externalPosition, $j, ($isKing === true ? strtoupper($colour) : strtolower($colour)));
                    }
                } else {
                    $numSquare = (int)$numSquare;
                    $externalPosition = $this->setCharAt($externalPosition, $numSquare, ($isKing === true ? strtoupper($colour) : strtolower($colour)));
                }
            }
        }

        $this->position = $this->convertPosition($externalPosition, 'internal');
        $this->updateSetup($this->generateFen());

        return true;
    }

    /**
     * Called when the initial board setup is changed with put() or remove().
     * modifies the SetUp and FEN properties of the header object.  if the FEN is
     * equal to the default position, the SetUp and FEN are deleted
     * the setup is only updated if history.length is zero, ie moves haven't been
     * made.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L419
     * @param string $fen
     * @return bool
     */
    private function updateSetup(string $fen): bool
    {
        if (count($this->history) > 0) {
            return false;
        }
        if ($fen === $this->defaultFEN) {
            $this->header['SetUp'] = '1';
            $this->header['FEN'] = $fen;
        } else {
            unset($this->header['SetUp']);
            unset($this->header['FEN']);
        }
        return true;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L699
     * @param string $position
     * @param int $idx
     * @param string $chr
     * @return string
     */
    private function setCharAt(string $position, int $idx, string $chr): string
    {
        if ($idx > strlen($position) - 1) {
            return $position;
        }
        return substr($position, 0, $idx) . $chr . substr($position, $idx + 1);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L122
     * @throws \Exception
     */
    public function reset()
    {
        $this->load($this->defaultFEN);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L860
     * @param Move $move
     */
    public function push(Move $move)
    {
        array_push($this->history, new History($move, $this->turn, $this->moveNumber));
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L643
     * @param null $square
     * @return Move[]
     */
    public function moves($square=null)
    {
        $moves = [];

        if (!is_null($square)){
            $moves = $this->getLegalMoves($square->square);
        } else {
            /** @var Move[] $tmpCaptures */
            $tmpCaptures = $this->getCaptures(); // @todo make sure these are cloned objects....
            if (count($tmpCaptures) > 0) {
                // TODO change to be applicable to array
                foreach ($tmpCaptures as &$capture) {
                    $capture->flags = self::FLAG_CAPTURE;
                    $capture->captures = $capture->jumps;
                    $capture->piecesCaptured = $capture->piecesTaken;
                } unset ($capture);
                return $tmpCaptures;
            }
            $moves = $this->getMoves();
        }

        // TODO returns [] for on hovering for square no
        // moves = [].concat.apply([], moves)
        return $moves;
    }



    public function gameOver()
    {
        // @todo
    }

    public function inDraw()
    {
        // @todo
    }

    /**
     * @param string $fen
     * @return FenValidator
     */
    public function validateFen(string $fen): FenValidator
    {
        return new FenValidator($fen);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L306
     * @return string
     */
    public function generateFen(): string
    {
        $black = [];
        $white = [];
        $externalPosition = $this->convertPosition($this->position, 'external');
        for ($i = 0; $i < strlen($externalPosition); $i++) {
            if ($externalPosition[$i] === 'w') {
                $white[] = $i;
            }
            if ($externalPosition[$i] === 'W') {
                $white[] = 'K' . $i;
            }
            if ($externalPosition[$i] === 'b') {
                $black[] = $i;
            }
            if ($externalPosition[$i] === 'B') {
                $black[] = 'K' . $i;
            }
        }

        return strtoupper($this->turn) . ':W' . implode(',', $white) . ':B' . implode(',', $black);
    }

    /**
     * @todo replace $options with two arguments
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L331
     * @param array|null $options
     * @return string
     */
    public function generatePDN(array $options = null)
    {
        $newLine = (is_array($options) && isset($options['newline_char'])) ? $options['newline_char'] : "\n";
        $maxWidth = (is_array($options) && isset($options['maxWidth'])) ? $options['maxWidth'] : 0;
        $result = [];
        $headerExists = false;

        foreach($this->header as $i => $header) {
            array_push($result, sprintf('[%d "%s"]', $i, $header).$newLine);
            $headerExists = true;
        }

        if ($headerExists === true && count($this->history) > 0) {
            array_push($result, $newLine);
        }

        $tmpHistory = $this->history;
        $moves = [];
        $moveString = '';
        $moveNumber = 1;

        while (count($tmpHistory) > 0) {
            /** @var History $move */
            $move = array_shift($tmpHistory);
            if ($move->turn === 'W') {
                $moveString .= $moveNumber . '. ';
            }
            $moveString .= $move->move->from;

            if ($move->move->flags === 'c') {
                $moveString .= 'x';
            } else {
                $moveString .= '-';
            }

            $moveString .= $move->move->to;
            $moveString .= ' ';
            $moveNumber++;
        }

        if (strlen($moveString) > 0){
            array_push($moves, $moveString);
        }

        // @todo result from pdn or header??
        if (isset($this->header['Result'])) {
            array_push($moves, $this->header['Result']);
        }

        if ($maxWidth === 0) {
            return implode('', $result) . implode('', $moves);
        }

        $currentWidth = 0;
        for ($i = 0; $i< count($moves); $i++) {
            if ($currentWidth + strlen($moves[$i]) > $maxWidth && $i !== 0) {
                if ($result[count($result) - 1] === ' ') {
                    array_pop($result);
                }

                array_push($result, $newLine);
                $currentWidth = 0;
            } else if ($i !== 0) {
                array_push($result, ' ');
                $currentWidth++;
            }
            array_push($result, ' ');
            $currentWidth += strlen($moves[$i]);
        }

        return implode('', $result);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L404
     * @param array $values
     * @return array
     */
    private function setHeader(array $values = []) : array
    {
        foreach($values as $key => $value) {
            $this->header[$key] = $value;
        }

        return $this->header;
    }

    /**
     * This looks not to be implemented in the js source.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1182
     * @param string $pdn
     * @param array $options
     */
    public function loadPDN(string $pdn, array $options = [])
    {
        // ...
    }

    public function parsePDN()
    {
        // @todo
    }

    public function header($args)
    {
        // @todo
    }

    public function ascii()
    {
        // @todo
    }

    public function turn()
    {
        // @todo
    }

    public function move($move)
    {
        // @todo
    }

    public function getMoves()
    {
        // @todo
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L666
     * @param int $index
     */
    public function getLegalMoves(int $index)
    {
        $index = $this->convertNumber($index, 'internal');
        $captures = $this->capturesAtSquare($index, ['position' => $this->position, 'dirFrom' => ''], ['jumps' => [$index], 'takes' => [], 'piecesTaken' => []]);
        $captures= $this->longestCapture($captures);
        $legalMoves = $captures;
        if (count($legalMoves) === 0) {
            $legalMoves = $this->movesAtSquare($index);
        }

        return $this->convertMoves($legalMoves, 'external');
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L769
     * @param $posFrom
     * @param $state
     * @param $capture
     */
    private function capturesAtSquare($posFrom, $state, $capture)
    {
        // @todo
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L868
     */
    public function undo()
    {
        if (!$old = array_pop($this->history)){
            return null;
        }

        $move = $old->move;
        $this->turn = $old->turn;
        $this->moveNumber = $old->moveNumber;

        $this->position = $this->setCharAt($this->position, $this->convertNumber((int) $move->from, 'internal'), $move->piece);
        $this->position = $this->setCharAt($this->position, $this->convertNumber((int) $move->to, 'internal'), 0);

        if ($move->flags === 'c') {
            for ($i = 0; $i < count($move->captures); $i++){ // @todo PORT: is captures a string or array?
                $this->position = $this->setCharAt($this->position, $this->convertNumber((int) $move->captures[$i], 'internal'), $move->piecesCaptured[$i]);
            }
        }

        if ($move->flags === 'p') {
            if (! empty($move->captures)){
                for ($i = 0; $i < count($move->captures); $i++){
                    $this->position = $this->setCharAt($this->position, $this->convertNumber((int) $move->captures[$i], 'internal'), $move->piecesCaptured[$i]);
                }
            }
            $this->position = $this->setCharAt($this->position, $this->convertNumber((int) $move->from, 'internal'), strtolower($move->piece));
        }

        return $move;
    }

    public function put($piece, $square)
    {
        // @todo
    }

    public function get($square)
    {
        // @todo
    }

    public function remove($square)
    {
        // @todo
    }

    public function perft($depth)
    {
        // @todo
    }

    public function history()
    {
        // @todo
    }

    public function convertMoves()
    {
        // @todo
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L955
     * @param int $number
     * @param string $notation
     * @return int
     */
    public function convertNumber(int $number, string $notation): int
    {
        if ($notation === 'internal') {
            return $number + floor(($number - 1) / 10);
        }

        if ($notation === 'external') {
            return $number - floor(($number - 1)/ 11);
        }

        return $number;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L971
     * @param string $position
     * @param string $notation
     * @return string
     */
    public function convertPosition(string $position, string $notation): string
    {
        $newPosition = $position;

        if ($notation === 'internal') {
            $sub1 = substr($position, 1, 10);
            $sub2 = substr($position, 11, 10);
            $sub3 = substr($position, 21, 10);
            $sub4 = substr($position, 31, 10);
            $sub5 = substr($position, 41, 10);
            $newPosition = sprintf('-%s-%s-%s-%s-%s-', $sub1, $sub2, $sub3, $sub4, $sub5);
        }

        if ($notation === 'external') {
            $sub1 = substr($position, 1, 10);
            $sub2 = substr($position, 12, 10);
            $sub3 = substr($position, 23, 10);
            $sub4 = substr($position, 34, 10);
            $sub5 = substr($position, 45, 10);
            $newPosition = sprintf('?%s%s%s%s%s', $sub1, $sub2, $sub3, $sub4, $sub5);
        }
        return $newPosition;
    }

    public function outsideBoard()
    {
        // @todo
    }

    public function directionStrings()
    {
        // @todo
    }

    public function oppositeDir()
    {
        // @todo
    }

    public function validDir()
    {
        // @todo
    }

    public function position()
    {
        // @todo
    }

    public function makePretty()
    {
        // @todo
    }

    public function captures()
    {
        // @todo
    }
}