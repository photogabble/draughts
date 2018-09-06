<h1 align="center">Draughts</h1>
<p align="center"><em>A PHP port of <a href="https://github.com/shubhendusaurabh/draughts.js">draughts.js</a></em></p>

<p align="center">
  <a href="https://travis-ci.org/photogabble/draughts"><img src="https://travis-ci.org/photogabble/draughts.svg?branch=master" alt="Build Status"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/photogabble/php-confusable-homoglyphs.svg" alt="License"></a>
  <a href="https://gitmoji.carloscuesta.me/"><img src="https://img.shields.io/badge/gitmoji-%20😜%20😍-FFDD67.svg" alt="Gitmoji"></a>
</p>

## About this package
A PHP checkers library for move generation/validation, piece placement/movement and draw detection. Useful for writing the server side implementation of a multi-player checkers game. It has been ported from a JavaScript implementation by [@shubhendusaurabh](https://github.com/shubhendusaurabh).

## Install

Install using composer: `compoer require photogabble/draughts`

## Example Usage

The code below will play a complete game of draughts, outputting the result of each move with each move being picked randomly:

```php
$draughts = new Draughts();
echo $draughts->ascii();
while (!$draughts->gameOver())
{
    $moves = $draughts->generateMoves();
    $move = $moves[array_rand($moves, 1)];
    $draughts->move($move);
    echo $draughts->ascii();
}
```

## Public API

### Constructor

The `Draughts` class `__construct` method takes an optional `string` parameter that defines the initial board configuration in [Forsyth-Edwards Notation](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation).

```php
// Board defaults to the starting position when call with no parameter
$draughts = new Draughts;

// Pass in a FEN string to load a particular position
$draughts = new Draughts('W:W31-50:B1-20');
```

### ascii(bool $unicode = false): string
Returns a string containing an ASCII diagram of the current position.

### reset(): void

Reset the board to the initial starting position.

### generateFen(): string

Returns the [Forsyth-Edwards Notation](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation) (FEN) string for the current position.

### gameOver(): bool

Returns `true` if the game has ended via no moves left, or no pieces rule. Otherwise, returns `false`.

### inDraw(): bool

Under development, see issue #4

### inThreefoldRepetition()

Under development, see issue #5

### move(Move $move): ?Move

Attempts to make a move on the board, returning a `Move` object if the move was legal, otherwise `null`.

### generateMoves(int $square = null): array

Returns a list of legals moves from the current position. The function takes an optional parameter which controls the single-square move generation.

### turn(): string

Returns the current side to move.

### undo(): ?Move
Takeback the last half-move, returning a `Move` object if successful, otherwise `null`.

### get($square): string

Returns the piece on the square.

### remove(int $square): string

Removes the piece on the square.

### put(string $piece, int $square): bool

Puts the piece on the square and returns `true` if valid placement. Otherwise, returns `false`.

### getHistory(bool $verbose = false): array

Returns a list containing the moves of the current game.

### setHeader(array $values = []): array

Update the header properties.

### loadPDN(string $pdn, array $options = [])
Load the moves of a game stored in [Portable Draughts Notation](https://en.wikipedia.org/wiki/Portable_Draughts_Notation). Options is a optional parameter that contains a 'newline_char' which is a string representation of a RegExp (and should not be pre-escaped) and defaults to '\r?\n'). Returns `true` if the pdn was parsed successfully, otherwise `false`.

## Not invented here

This started as a PHP port of [shubhendusaurabh/draughts.js](https://github.com/shubhendusaurabh/draughts.js).