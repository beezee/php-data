<?php

declare(strict_types=1);

namespace Data\Optics;

use Data as D;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Identity as Id;

class Lens {

  private $_d;

  public function __construct(D\Data $d) {
    $this->_d = $d;
  }

  function view(array $path) {
    return array_reduce($path, function($a, $e) { 
      return is_a($a, 'Data\Data') ?  
        $a->$e : 
        D\getOrThrow(
          D\getIfSet($e, $a),
          new \Exception("Property $e is not defined"));
    }, $this->_d);
  }

  function set(array $path, $v) {
    // a: ([(Data, [path])], (Data, [path]), Data|{path:...})
    // (acc, curr, focus) = ([], ($this, [$path[0]]), $this->$path[0])
    // spath: [(Data, [path])]
    $spath = Id::of(array_reduce(D\tail($path), function($a, $e) {
      return is_a($a[2], '\Data\Data')
        ? [wf\push_($a[0], [$a[1]]), [$a[2], [$e]], $a[2]->$e]
        : [$a[0], [$a[1][0], wf\push_($a[1][1], [$e])], 
            D\getOrThrow(
              D\getIfSet($e, $a),
              new \Exception("Property $e is not defined"))];
    }, [[], [$this->_d, [$path[0]]], $this->_d->{$path[0]}]))
      ->map(function($a) { return wf\push_($a[0], [$a[1]]); })
      ->map('array_reverse')
      ->extract();
    $mkArr = function(array $keys, $v) {
      return array_reduce(array_reverse($keys), function($a, $e) {
        return [$e => $a];
      }, $v);
    };
    return array_reduce($spath,
      function($a, $e) use($mkArr) { return $e[0]->copy($mkArr($e[1], $a)); },
      $v);
  }

  function modify(array $path, callable $f) {
    return $this->set($path, $f($this->view($path)));
  }
}
