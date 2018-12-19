<?php

declare(strict_types=1);

namespace Data\Optics;

use Data as D;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Either as Either;
use Widmogrod\Monad\Identity as Id;

class Prism {

  private $_lens;

  public function __construct(Lens $lens) {
    $this->_lens = $lens;
  }

  private function maybeEval(Callable $c) {
    return Either\toMaybe(Either\tryCatch(
        $c, wf\identity, null));
  }

  function view(array $path) {
    return $this->maybeEval(function() use ($path) {
      return $this->_lens->view($path);
    });
  }

  function set(array $path, $v) {
    return $this->maybeEval(function() use ($path, $v) {
      return $this->_lens->set($path, $v);
    });
  }

  function modify(array $path, callable $f) {
    return $this->maybeEval(function() use ($path, $f) {
      return $this->_lens->modify($path, $f);
    });
  }
}
