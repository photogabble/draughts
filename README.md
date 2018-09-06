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

The `Draughts` class `__construct` method takes an optional `string` parameter that defined the initial board configuration in [Forsyth-Edwards Notation](https://en.wikipedia.org/wiki/Forsyth%E2%80%93Edwards_Notation).

```php
// Board defaults to the starting position when call with no parameter
$draughts = new Draughts;

// Pass in a FEN string to load a particular position
$draughts = new Draughts('W:W31-50:B1-20');
```

## Not invented here

This started as a PHP port of [shubhendusaurabh/draughts.js](https://github.com/shubhendusaurabh/draughts.js).