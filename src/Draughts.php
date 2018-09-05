<?php

namespace Photogabble\Draughts;

/**
 * Class Draughts
 *
 * This class and its companions are part of a port to PHP from the JavaScript
 * project: https://github.com/shubhendusaurabh/draughts.js
 *
 * ==================================================================================
 * DESCRIPTION OF IMPLEMENTATION PRINCIPLES
 * A. Position for rules (internal representation): string with length 56.
 *    Special numbering for easy applying rules.
 *    Valid characters: b B w W 0 -
 *       b (black) B (black king) w (white) W (white king) 0 (empty) (- unused)
 *    Examples:
 *      '-bbbBBB000w-wwWWWwwwww-bbbbbbbbbb-000wwwwwww-00bbbwwWW0-'
 *      '-0000000000-0000000000-0000000000-0000000000-0000000000-'  (empty position)
 *      '-bbbbbbbbbb-bbbbbbbbbb-0000000000-wwwwwwwwww-wwwwwwwwww-'  (start position)
 * B. Position (external respresentation): string with length 51.
 *    Square numbers are represented by the position of the characters.
 *    Position 0 is reserved for the side to move (B or W)
 *    Valid characters: b B w W 0
 *       b (black) B (black king) w (white) W (white king) 0 (empty)
 *    Examples:
 *       'B00000000000000000000000000000000000000000000000000'  (empty position)
 *       'Wbbbbbbbbbbbbbbbbbbbb0000000000wwwwwwwwwwwwwwwwwwww'  (start position)
 *       'WbbbbbbBbbbbb00bbbbb000000w0W00ww00wwwwww0wwwwwwwww'  (random position)
 *
 * External numbering      Internal Numbering
 * --------------------    --------------------
 *   01  02  03  04  05      01  02  03  04  05
 * 06  07  08  09  10      06  07  08  09  10
 *   11  12  13  14  15      12  13  14  15  16
 * 16  17  18  19  20      17  18  19  20  21
 *   21  22  23  24  25      23  24  25  26  27
 * 26  27  28  29  30      28  29  30  31  32
 *   31  32  33  34  35      34  35  36  37  38
 * 36  37  38  39  40      39  40  41  42  43
 *   41  42  43  44  45      45  46  47  48  49
 * 46  47  48  49  50      50  51  52  53  54
 * --------------------    --------------------
 *
 * Internal numbering has fixed direction increments for easy applying rules:
 *   NW   NE         -5   -6
 *     \ /             \ /
 *     sQr     >>      sQr
 *     / \             / \
 *   SW   SE         +5   +6
 *
 * DIRECTION-STRINGS
 * Strings of variable length for each of four directions at one square.
 * Each string represents the position in that direction.
 * Directions: NE, SE, SW, NW (wind directions)
 * Example for square 29 (internal number):
 *   NE: 29, 24, 19, 14, 09, 04     b00bb0
 *   SE: 35, 41, 47, 53             bww0
 *   SW: 34, 39                     b0
 *   NW: 23, 17                     bw
 * CONVERSION internal to external representation of numbers.
 *   N: external number, values 1..50
 *   M: internal number, values 0..55 (invalid 0,11,22,33,44,55)
 *   Formulas:
 *   M = N + floor((N-1)/10)
 *   N = M - floor((M-1)/11)
 *
 *==================================================================================
 */
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
        'w' => "\u{26C0}",
        'b' => "\u{26C2}",
        'B' => "\u{26C3}",
        'W' => "\u{26C1}",
        '0' => "\u{0020}\u{0020}"
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
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L126
     * @param null|string $fen
     * @return bool
     * @throws \Exception
     */
    public function load(string $fen = null): bool
    {
        if (is_null($fen) || $fen === $this->defaultFEN) {
            $this->position = $this->defaultPositionInternal;
            $this->updateSetup($this->generateFen());
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
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L122
     * @throws \Exception
     */
    public function reset()
    {
        $this->load($this->defaultFEN);
    }

    /**
     * Generate next valid moves.
     * Original source has this proxied as moves publicly.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L643
     * @param null|int $square
     * @return array|Move[]
     * @throws \Exception
     */
    public function generateMoves(int $square = null)
    {
        if (!is_null($square)) {
            $moves = $this->getLegalMoves($square);
        } else {
            /** @var Move[] $tmpCaptures */
            $tmpCaptures = $this->getCaptures(); // @todo make sure these are cloned objects....
            if (count($tmpCaptures) > 0) {
                // TODO change to be applicable to array
                foreach ($tmpCaptures as &$capture) {
                    $capture->flags = self::FLAG_CAPTURE;
                    $capture->captures = $capture->jumps;
                    $capture->piecesCaptured = $capture->piecesTaken;
                }
                unset ($capture);
                return $tmpCaptures;
            }
            $moves = $this->getMoves();
        }

        // TODO returns [] for on hovering for square no
        // moves = [].concat.apply([], moves)
        return $moves;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1080
     * @return bool
     * @throws \Exception
     */
    public function gameOver(): bool
    {
        // First check if any piece left
        for ($i = 0; $i < strlen($this->position); $i++) {
            if (strtolower($this->position[$i]) === strtolower($this->turn)) {
                // if moves left game not over
                return count($this->generateMoves()) === 0;
            }
        }
        return true;
    }

    /**
     * Returns true or false if the game is drawn (50-move rule or insufficient material).
     * Looks like the source library hard coded this.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1172
     * @todo finish this?
     * @return bool
     */
    public function inDraw()
    {
        return false;
    }

    /**
     * Original source has this proxied as a public method validate_fen.
     *
     * @param string $fen
     * @return FenValidator
     */
    public function validateFen(string $fen): FenValidator
    {
        return new FenValidator($fen);
    }

    /**
     * Original source has this proxied as a public method fen.
     *
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
     * Original source has this proxied as a public method pdn.
     *
     * @todo replace $options with two arguments
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L331
     * @param array|null $options
     * @return string
     */
    public function generatePDN(array $options = null)
    {
        $this->setHeader([
            'FEN' => $this->generateFen()
        ]);

        $newLine = (is_array($options) && isset($options['newline_char'])) ? $options['newline_char'] : "\n";
        $maxWidth = (is_array($options) && isset($options['maxWidth'])) ? $options['maxWidth'] : 0;
        $result = [];
        $headerExists = false;

        foreach ($this->header as $name => $header) {
            array_push($result, sprintf('[%s "%s"]', $name, $header) . $newLine);
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

        if (strlen($moveString) > 0) {
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
        for ($i = 0; $i < count($moves); $i++) {
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
        // @todo port
    }

    /**
     * Set the header properties from an array of $values.
     *
     * Originates from the private method `set_header`
     * mapped to public `header` method on
     * the JavaScript source.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L404
     * @param array $values
     * @return array
     */
    public function setHeader(array $values = []): array
    {
        foreach ($values as $key => $value) {
            $this->header[$key] = $value;
        }

        return $this->header;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1050
     * @param bool $unicode
     * @return string
     */
    public function ascii(bool $unicode = false): string
    {
        $extPosition = $this->convertPosition($this->position, 'external');
        $s = "\n+------------------------------+\n";
        $i = 1;
        for ($row = 1; $row <= 10; $row++) {
            $s .= "|\t";
            if ($row % 2 !== 0) {
                $s .= '  ';
            }
            for ($col = 1; $col <= 10; $col++) {
                if ($col % 2 === 0) {
                    $s .= '  ';
                    $i++;
                } else {
                    if ($unicode === true) {
                        $s .= ' ' . $this->unicodes[$extPosition[$i]];
                    } else {
                        $s .= ' ' . $extPosition[$i];
                    }
                }
            }
            if ($row % 2 === 0) {
                $s .= '  ';
            }
            $s .= "\t|\n";
        }
        $s .= "+------------------------------+\n";
        return $s;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1192
     * @return string
     */
    public function turn(): string
    {
        return strtolower($this->turn);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1196
     * @param Move $move
     * @return Move
     * @throws \Exception
     */
    public function move(Move $move): ?Move
    {
        $moves = $this->generateMoves();
        foreach ($moves as $gMove){
            if (($move->to === $gMove->to) && ($move->from === $gMove->from)) {
                $this->makeMove($gMove);
                return $gMove;
            }
        }
        return null;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L684
     * @return array
     * @throws \Exception
     */
    public function getMoves()
    {
        $moves = [];
        $us = $this->turn;

        for ($i = 1; $i < strlen($this->position); $i++) {
            if ($this->position[$i] === $us || $this->position[$i] === strtolower($us)) {
                $tempMoves = $this->movesAtSquare($i);
                if (count($tempMoves) > 0) {
                    $moves = array_merge($moves, $this->convertMoves($tempMoves, 'external'));
                }
            }
        }
        return $moves;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L666
     * @param int $index
     * @return array
     * @throws \Exception
     */
    public function getLegalMoves(int $index): array
    {
        $index = $this->convertNumber($index, 'internal');
        $captures = $this->capturesAtSquare($index, new CaptureState($this->position), new Move(['jumps' => [$index], 'takes' => [], 'piecesTaken' => []]));
        $captures = $this->longestCapture($captures);
        $legalMoves = $captures;
        if (count($legalMoves) === 0) {
            $legalMoves = $this->movesAtSquare($index);
        }

        return $this->convertMoves($legalMoves, 'external');
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L868
     */
    public function undo()
    {
        if (!$old = array_pop($this->history)) {
            return null;
        }

        $move = $old->move;
        $this->turn = $old->turn;
        $this->moveNumber = $old->moveNumber;

        $this->position = $this->setCharAt($this->position, $this->convertNumber((int)$move->from, 'internal'), $move->piece);
        $this->position = $this->setCharAt($this->position, $this->convertNumber((int)$move->to, 'internal'), 0);

        if ($move->flags === 'c') {
            for ($i = 0; $i < count($move->captures); $i++) { // @todo PORT: is captures a string or array?
                $this->position = $this->setCharAt($this->position, $this->convertNumber((int)$move->captures[$i], 'internal'), $move->piecesCaptured[$i]);
            }
        }

        if ($move->flags === 'p') {
            if (!empty($move->captures)) {
                for ($i = 0; $i < count($move->captures); $i++) {
                    $this->position = $this->setCharAt($this->position, $this->convertNumber((int)$move->captures[$i], 'internal'), $move->piecesCaptured[$i]);
                }
            }
            $this->position = $this->setCharAt($this->position, $this->convertNumber((int)$move->from, 'internal'), strtolower($move->piece));
        }

        return $move;
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
        $this->updateSetup($this->generateFen());
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L598
     * @param string $piece
     * @param int $square
     * @return bool
     */
    public function put(string $piece, int $square): bool
    {
        // check for valid piece string
        if (strpos($this->symbols, $piece) === false) {
            return false;
        }

        // check for valid square
        if ($this->outsideBoard($this->convertNumber($square, 'internal')) === false) {
            return false;
        }

        $this->position = $this->setCharAt($this->position, $this->convertNumber($square, 'internal'), $piece);
        $this->updateSetup($this->generateFen());
        return true;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L593
     * @param $square
     * @return string
     */
    public function get($square): string
    {
        return substr($this->position, $this->convertNumber($square, 'internal'), 1);
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L614
     * @param int $square
     * @return string
     */
    public function remove(int $square):string
    {
        $piece = $this->get($square);
        $this->position = $this->setCharAt($this->position, $this->convertNumber($square, 'internal'), 0);
        $this->updateSetup($this->generateFen());
        return $piece;
    }

    /**
     * Looks like this was a work in progress on the original js source...
     * @todo finish if possible?
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1134
     * @param $depth
     */
    public function perft($depth)
    {
        // ...
    }

    /**
     * This is proxied as a public method named history in the js source.
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1091
     * @param bool $verbose
     * @return array
     */
    public function getHistory(bool $verbose = false): array
    {
        $moveHistory = [];
        foreach ($this->history as $item) {
            $moveHistory = $item->history($verbose);
        }
        return $moveHistory;
    }

    /**
     * This was proxies by position on the js version.
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1107
     * @return string
     */
    public function getPosition(): string
    {
        return $this->convertPosition($this->position, 'external');
    }

    //
    // PRIVATE BELOW
    //

    /**
     * Called when the initial board setup is changed with put() or remove().
     * modifies the SetUp and FEN properties of the header object. If the FEN is
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
        if ($fen !== $this->defaultFEN) {
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
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L860
     * @param Move $move
     */
    private function push(Move $move)
    {
        array_push($this->history, new History($move, $this->turn, $this->moveNumber));
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L565
     * @param Move $move
     * @return void
     */
    private function makeMove(Move $move)
    {
        $move->piece = substr($this->position, $this->convertNumber($move->from, 'internal'), 1);
        $this->position = $this->setCharAt($this->position, $this->convertNumber($move->to, 'internal'), $move->piece);
        $this->position = $this->setCharAt($this->position, $this->convertNumber($move->from, 'internal'), 0);
        $move->flags = self::FLAG_NORMAL;

        // TODO refactor to either takes or capture

        if (count($move->takes) > 0) {
            $move->flags = self::FLAG_CAPTURE;
            $move->captures = $move->takes;
            $move->piecesCaptured = $move->piecesTaken;
            for ($i = 0; $i < count($move->takes); $i++) {
                $this->position = $this->setCharAt($this->position, $this->convertNumber($move->takes[$i], 'internal'), 0);
            }
        }
        // Promoting piece here
        if ($move->to <= 5 && $move->piece === 'w') {
            $move->flags = self::FLAG_PROMOTION;
            $this->position = $this->setCharAt($this->position, $this->convertNumber($move->to, 'internal'), strtoupper($move->piece));
        } else if ($move->to >= 46 && $move->piece === 'b') {
            $this->position = $this->setCharAt($this->position, $this->convertNumber($move->to, 'internal'), strtoupper($move->piece));
        }

        $this->push($move);

        if ($this->turn === self::BLACK) {
            $this->moveNumber += 1;
        }
        $this->turn = $this->swapColour($this->turn);
    }

    /**
     * @todo refactor so its void?
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L899
     * @param string $colour
     * @return string
     */
    private function swapColour(string $colour)
    {
        return $colour === self::WHITE ? self::BLACK : self::WHITE;
    }

    /**
     * This was also proxied by a public captures method on the original js source.
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L750
     * @return array
     * @throws \Exception
     */
    private function getCaptures()
    {
        $us = $this->turn;
        $captures = [];
        for ($i = 0; $i < strlen($this->position); $i++) {
            if ($this->position[$i] === $us || $this->position[$i] === strtolower($us)) {
                $posFrom = $i;
                $state = new CaptureState($this->position, '');
                $capture = new Move(['jumps' => [], 'takes' => [], 'from' => $posFrom, 'to' => '', 'piecesTaken' => []]);
                $capture->jumps[0] = $posFrom;
                $tempCaptures = $this->capturesAtSquare($posFrom, $state, $capture);
                if (count($tempCaptures) > 0) {
                    $captures = array_merge($captures, $this->convertMoves($tempCaptures, 'external'));
                }
            }
        }
        $captures = $this->longestCapture($captures);
        return $captures;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L769
     * @param int $posFrom
     * @param CaptureState $state
     * @param Move $capture
     * @return array
     * @throws \Exception
     */
    private function capturesAtSquare(int $posFrom, CaptureState $state, Move $capture): array
    {
        $piece = substr($state->position, $posFrom, 1);
        if (!in_array($piece, ['b', 'w', 'B', 'W'])) {
            return [$capture];
        }

        if ($piece === 'b' || $piece === 'w') {
            $dirString = $this->directionStrings($state->position, $posFrom, 3);
        } else {
            $dirString = $this->directionStrings($state->position, $posFrom);
        }

        $finished = true;
        $captureArrayForDir = [];

        foreach ($dirString as $dir => $str) {
            if ($dir === $state->dirFrom) {
                continue;
            }
            if (in_array($piece, ['b', 'w'])) {
                // matches: bw0, bW0, wB0, wb0
                if (preg_match('/^b[wW]0|^w[bB]0/', $str, $matchArray) > 0) {
                    $posTo = $posFrom + (2 * $this->steps[$dir]); // @todo should $posTo & $posTake be rounded?
                    $posTake = $posFrom + (1 * $this->steps[$dir]);

                    if (in_array($posTake, $capture->takes)) {
                        continue; // capturing twice forbidden
                    }

                    $updateCapture = clone($capture);
                    $updateCapture->to = $posTo;
                    $updateCapture->jumps[] = $posTo;
                    $updateCapture->takes[] = $posTake;
                    $updateCapture->piecesTaken[] = substr($this->position, $posTake, 1);
                    $updateCapture->from = $posFrom;

                    $updateState = clone($state);
                    $updateState->dirFrom = $this->oppositeDir($dir);
                    $pieceCode = substr($updateState->position, $posFrom, 1);
                    $updateState->position = $this->setCharAt($updateState->position, $posFrom, 0);
                    $updateState->position = $this->setCharAt($updateState->position, $posTo, $pieceCode);

                    $finished = false;
                    $captureArrayForDir[$dir] = $this->capturesAtSquare($posTo, $updateState, $updateCapture);
                }
            }
            if (in_array($piece, ['B', 'W'])) {
                // matches: B00w000, WB00
                if (preg_match('/^B0*[wW]0+|^W0*[bB]0+/', $str, $matchArray) > 0) {
                    $matchStr = $matchArray[0];

                    // // matches: w000, B00
                    preg_match('/[wW]0+$|[bB]0+$/', $matchStr, $matchArraySubstr);

                    $matchSubstr = $matchArraySubstr[0];
                    $takeIndex = strlen($matchStr) - strlen($matchSubstr);
                    $posTake = $posFrom + ($takeIndex * $this->steps[$dir]);

                    if (in_array($posTake, $capture->takes)) {
                        continue; // capturing twice forbidden
                    }

                    for ($i = 1; $i < strlen($matchSubstr); $i++) {
                        $posTo = $posFrom + (($takeIndex + $i) * $this->steps[$dir]);

                        $updateCapture = clone($capture);
                        $updateCapture->jumps[] = $posTo;
                        $updateCapture->to = $posTo;
                        $updateCapture->takes[] = $posTake;
                        $updateCapture->piecesTaken[] = substr($this->position, $posTake, 1);
                        $updateCapture->from = $posFrom;

                        $updateState = clone($state);
                        $updateState->dirFrom = $this->oppositeDir($dir);
                        $pieceCode = substr($updateState->position, $posFrom, 1);
                        $updateState->position = $this->setCharAt($updateState->position, $posFrom, 0);
                        $updateState->position = $this->setCharAt($updateState->position, $posTo, $pieceCode);

                        $finished = false;
                        $dirIndex = $dir . (string)$i;
                        $captureArrayForDir[$dirIndex] = $this->capturesAtSquare($posTo, $updateState, $updateCapture);
                    }
                }
            }
        }


        $captureArray = [];
        if ($finished === true && count($capture->takes) > 0) {
            // fix for mutiple capture
            $capture->from = $capture->jumps[0];
            $captureArray[0] = $capture;
        } else {
            foreach ($captureArrayForDir as $dir) {
                $captureArray = array_merge($captureArray, $dir);
            }
        }
        return $captureArray;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L912
     * @param array|Move[] $captures
     * @return array
     */
    private function longestCapture(array $captures): array
    {
        $maxJumpCount = 0;
        for ($i = 0; $i < count($captures); $i++) {
            $jumpCount = count($captures[$i]->jumps);
            if ($jumpCount > $maxJumpCount) {
                $maxJumpCount = $jumpCount;
            }
        }

        $selectedCaptures = [];
        if ($maxJumpCount < 2) {
            return $selectedCaptures;
        }

        for ($i = 0; $i < count($captures); $i++) {
            if (count($captures[$i]->jumps) === $maxJumpCount) {
                $selectedCaptures[] = $captures[$i];
            }
        }
        return $selectedCaptures;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L708
     * @param int $square
     * @return array
     * @throws \Exception
     */
    private function movesAtSquare(int $square)
    {
        $moves = [];
        $posFrom = $square;
        $piece = substr($this->position, $square, 1);

        if (in_array($piece, ['b', 'w'])) {
            $dirStrings = $this->directionStrings($this->position, $posFrom, 2);
            foreach ($dirStrings as $dir => $str) {
                // // e.g. b0 w0
                if (preg_match('/^[bw]0/', $str, $matchArray)) {
                    if ($this->validDir($piece, $dir) === true) { // validDir maybe shouldn't throw an exception?
                        $posTo = $posFrom + $this->steps[$dir];
                        $moves[] = new Move(['from' => $posFrom, 'to' => $posTo, 'takes' => [], 'jumps' => []]);
                    }
                }
            }
        }

        if (in_array($piece, ['B', 'W'])) {
            $dirStrings = $this->directionStrings($this->position, $posFrom, 2);
            foreach ($dirStrings as $dir => $str) {
                // e.g. B000, W0
                if (preg_match_all('/^[BW]0+/', $str, $matchArray)) {
                    for ($i = 1; $i < count($matchArray[0]); $i++) {
                        $posTo = $posFrom + ($i * $this->steps[$dir]);
                        $moves[] = new Move(['from' => $posFrom, 'to' => $posTo, 'takes' => [], 'jumps' => []]);
                    }
                }
            }
        }

        return $moves;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L934
     * @param array|Move[] $moves
     * @param string $type
     * @return array
     */
    private function convertMoves(array $moves, string $type): array
    {
        $tempMoves = [];
        if (!in_array($type, ['internal', 'external']) || count($moves) === 0) {
            return $tempMoves;
        }

        foreach($moves as $move){
            $moveObject = new Move(['jumps' => [], 'takes' => []]);
            $moveObject->from = $this->convertNumber($move->from, $type);
            foreach($move->jumps as $j => $jump) {
                $moveObject->jumps[$j] = $this->convertNumber($jump, $type);
            }
            foreach ($move->takes as $j => $take)
            {
                $moveObject->takes[$j] = $this->convertNumber($take, $type);
            }

            $moveObject->to = $this->convertNumber($move->to, $type);
            $moveObject->piecesTaken = $move->piecesTaken;
            $tempMoves[] = $moveObject;
        }
        return $tempMoves;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L955
     * @param int $number
     * @param string $notation
     * @return int
     */
    private function convertNumber(int $number, string $notation): int
    {
        if ($notation === 'internal') {
            return $number + floor(($number - 1) / 10);
        }

        if ($notation === 'external') {
            return $number - floor(($number - 1) / 11);
        }

        return $number;
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L971
     * @param string $position
     * @param string $notation
     * @return string
     */
    private function convertPosition(string $position, string $notation): string
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

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L996
     * @param int $square
     * @return bool
     */
    private function outsideBoard(int $square): bool
    {
        if ($square >= 0 && $square <= 55 && ($square % 11) !== 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create direction strings for square at position (internal representation)
     * Output object with four directions as properties (four rhumbs).
     * Each property has a string as value representing the pieces in that direction.
     * Piece of the given square is part of each string.
     * Example of output: {NE: 'b0', SE: 'b00wb00', SW: 'bbb00', NW: 'bb'}
     * Strings have maximum length of given maxLength.
     *
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1006
     * @param string $tempPosition
     * @param int $square
     * @param int $maxLength
     * @return array
     */
    private function directionStrings(string $tempPosition, int $square, int $maxLength = 100): array
    {
        if ($this->outsideBoard($square) === true) {
            return []; //return 334;
        }

        $dirStrings = [];

        foreach ($this->steps as $dir => $value) {
            $dirArray = [];
            $i = 0;
            $index = $square;
            do {
                $dirArray[$i] = substr($tempPosition, $index, 1);
                $i++;
                $index = $square + $i * $value;
                $outside = $this->outsideBoard($index);
            } while ($outside === false && $i < $maxLength);

            $dirStrings[$dir] = implode('', $dirArray);
        }

        return $dirStrings;
    }

    /**
     * @see @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1038
     * @param string $direction
     * @return string
     * @throws \Exception
     */
    private function oppositeDir(string $direction): string
    {
        $opposite = ['NE' => 'SW', 'SE' => 'NW', 'SW' => 'NE', 'NW' => 'SE'];
        if (!isset($opposite[$direction])) {
            throw new \Exception(sprintf('The direction [%s] is not valid.', $direction));
        }

        return $opposite[$direction];
    }

    /**
     * @see https://github.com/shubhendusaurabh/draughts.js/blob/master/draughts.js#L1043
     * @param string $piece
     * @param string $dir
     * @return bool
     * @throws \Exception
     */
    private function validDir(string $piece, string $dir): bool
    {
        $valid = [
            'w' => ['NE' => true, 'SE' => false, 'SW' => false, 'NW' => true],
            'b' => ['NE' => false, 'SE' => true, 'SW' => true, 'NW' => false],
        ];

        if (!isset($valid[$piece])) {
            throw new \Exception(sprintf('The piece [%s] is not valid.', $piece));
        }
        if (!isset($valid[$piece][$dir])) {
            throw new \Exception(sprintf('The direction [%s] is not valid.', $dir));
        }

        return $valid[$piece][$dir];
    }
}