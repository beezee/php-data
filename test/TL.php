<?php

namespace Data\Test;

use Data as d;
use Eris\Generator;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Either as Either;

class TL extends d\Data {
  protected static function constructors() {
    return array_reduce(['Red', 'Green', 'Yellow'],
      function($a, $e) { 
        return array_merge($a, [$e => wf\identity]);
      }, []);
  }
}

class TrafficLightTest extends \PHPUnit\Framework\TestCase
{
    use \Eris\TestTrait;

    function mkColor($c) {
      return call_user_func_array("Data\\Test\\TL::${c}", [[]]);
    }

    function toInt() {
      return [
        'Red' => wf\constt(0),
        'Green' => wf\constt(1),
        'Yellow' => wf\constt(2)
      ];
    }

    function fromInt($i) {
      switch ($i) {
        case 0: return 'Red';
        case 1: return 'Green';
        case 2: return 'Yellow';
      }
    }

    public function testTrafficLightSum()
    {
      $self = $this;
      $this->forAll(
          Generator\oneOf('Red', 'Yellow', 'Green'),
          Generator\oneOf('Red', 'Yellow', 'Green')
      )
          ->then(function($c1, $c2) use($self) {
              $tl1 = $self->mkColor($c1);
              $tl2 = $self->mkColor($c2);
              $this->assertTrue(
                // folding
                ($c1 == $self->fromInt($tl1->fold($self->toInt()))) &&
                ($c2 == $self->fromInt($tl2->fold($self->toInt()))) &&
                // exhaustivity on fold
                (Either\tryCatch(function() use ($tl1, $self) {
                      $tl1->fold(array_merge($self->toInt(), 
                        ['Red' => null, 'Green' => null]));
                    })(wf\invoke('getMessage'), null)
                  ->either(wf\identity, wf\constt("")) ==
                  "No handler for Red, Green provided") &&
                // construction
                ((($c1 == $c2) && ($tl1 == $tl2)) ||
                (($c1 != $c2) && ($tl1 != $tl2)))
              );
          });
    }
}
