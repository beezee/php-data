<?php

declare(strict_types=1);

namespace Data;

use Widmogrod as W;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Either as Either;
use Widmogrod\Monad\Maybe as Maybe;
use Widmogrod\Monad\Identity as Id;

class Data implements \JsonSerializable {

  private $_k;
  private $_d;

  private function __construct($k, $d) {
    $this->_k = $k;
    $this->_d = $d;
  }

  protected static function constructors() {
    return [
      'Data' => wf\identity
    ];
  }

  function __get($m) {
    return getOrThrow(
      getIfSet($m, $this->_d),
      new \Exception("Property $m is not defined"));
  }

  function lens(): Optics\Lens {
    return new Optics\Lens($this);
  }

  function prism(): Optics\Prism {
    return new Optics\Prism($this->lens());
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
          return $f($self);
        }),
        new \Exception("No handler for {$err} provided"));
  }

  function at($k) {
    return ($k == $this->_k)
      ? Maybe\pure($this->_d)
      : Maybe\nothing();
  }

  function copy($d) {
    return static::__callStatic($this->_k, 
      [array_replace_recursive([], $this->_d, $d)]);
  }

  static function __callStatic($m, $a) {
    $c = getOrThrow(getIfSet($m, static::constructors()), 
      new \Exception("${m} is not a valid constructor"));
    return new static($m, array_replace_recursive([], $a[0], $c($a[0])));
  }

  function jsonSerialize() {
    $class = get_class($this);
    return ["{$class}::{$this->_k}" => $this->_d];
  }

  function __toString() {
    $class = get_class($this);
    return Either\tryCatch(function() {
          return json_encode($this);
        }, wf\constt("${class}::{$this->_k}\n" . print_r($this->_d, true)), null)
      ->either(wf\identity, wf\identity);
  }
    
}
