<?php

declare(strict_types=1);

namespace Data;

use Widmogrod as W;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Maybe as Maybe;

class Data {

  private $_k;
  private $_d;

  private function __construct($k, $d) {
    $this->_k = $k;
    $this->_d = $d;
  }

  protected static function constructors() {
    return [
      'new' => wf\constt([])
    ];
  }

  function __get($m) {
    return $this->_d[$m];
  }

  function fold($fns) {
    $self = $this;
    list($errs, $fn) = array_reduce(array_keys(static::constructors()),
      function($a, $e) use ($fns, $self) {
        return filterMaybe(
          wf\curryN(1, 'is_callable'), 
          getIfSet($e, $fns))->map(
            function($f) use ($self, $e, $a) { 
              return ($e == $self->_k)
                ? [$a[0], $f] : $a;
             })->extract() ?: [wf\push_($a[0], [$e]), $a[1]];
        }, [[], null]);
      $err = (count($errs) == 0)
        ? $this->_k : implode(", ", $errs);
      return getOrThrow(filterMaybe(
        wf\constt(count($errs) == 0),
        Maybe\maybeNull($fn))->map(function ($f) use ($self) {
          return $f($self->_d);
        }),
        new \Exception("No handler for {$err} provided"));
  }

  function at($k) {
    return ($k == $this->_k)
      ? Maybe\pure($this->_d)
      : Maybe\nothing();
  }

  static function __callStatic($m, $a) {
    $c = getOrThrow(getIfSet($m, static::constructors()), 
      new \Exception("${m} is not a valid constructor"));
    return new static($m, array_replace_recursive([], $a[0], $c($a[0])));
  }
}
