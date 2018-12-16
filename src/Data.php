<?php

declare(strict_types=1);

namespace Data;

use Functional as f;
use Widmogrod as W;
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
      'new' => f\const_function([])
    ];
  }

  function __get($m) {
    return $this->_d[$m];
  }

  function fold($fns) {
    $self = $this;
    return getOrThrow(
      filterMaybe(
        f\curry_n(1, 'is_callable'), 
        getIfSet($this->_k, $fns))->map(
          function($f) use ($self) { return $f($self->_d); }),
      new \Exception("No handler for {$this->_k} provided"));
  }

  function at($k) {
    return ($k == $this->_k)
      ? Maybe\pure($this->_d)
      : Maybe\nothing();
  }

  static function __callStatic($m, $a) {
    $c = getOrThrow(getIfSet($m, static::constructors()), 
      new \Exception("${m} is not a valid constructor"));
    return new self($m, array_replace_recursive([], $a[0], $c($a[0])));
  }
}
