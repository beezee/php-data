<?php

declare(strict_types=1);

namespace Data;

use Widmogrod\Monad\Maybe as Maybe;

function getOrThrow(Maybe\Maybe $m, \Exception $e) {
  $r = $m->extract();
  if (is_null($r)) { throw $e; }
  return $r;
}

function getIfSet($k, $o): Maybe\Maybe {
  return (isset($o[$k]))
    ? Maybe\maybeNull($o[$k])
    : Maybe\nothing();
}

function filterMaybe(callable $p, Maybe\Maybe $m): Maybe\Maybe {
   return $m->bind(function($v) use($p) {
     return $p($v) ? Maybe\just($v) : Maybe\nothing();
   });
}

function tail(array $a): array {
  $r = $a;
  array_shift($r);
  return $r;
}

function last(array $a) {
  $r = $a;
  return array_pop($r);
}
