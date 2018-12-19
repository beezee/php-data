<?php

namespace Data\Test;

use Data as d;
use Eris\Generator;
use Widmogrod\Functional as wf;
use Widmogrod\Monad\Either as Either;
use Widmogrod\Monad\Maybe as Maybe;

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

    function mkColor($c, $i) {
      return call_user_func_array("Data\\Test\\TL::${c}", [$i]);
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
          Generator\oneOf('Red', 'Yellow', 'Green'),
          Generator\associative([
            'k1' => Generator\string(),
            'k2' => Generator\string()]),
          Generator\oneOf(
            function($lp, $path) { return $lp->set($path, 1); },
            function($lp, $path) { return $lp->view($path); },
            function($lp, $path) { return $lp->modify($path, wf\constt(1)); })
      )
          ->then(function($c1, $c2, $in, $lensOp) use($self) {
              $tl1 = $self->mkColor($c1, $in);
              $tl2 = $self->mkColor($c2, $in);
              $tl3 = $tl2->copy(['tl1' => $tl1, 'tl2' => $tl2]);
              $this->assertTrue(
                // lens laws
                $tl3 == $tl3->lens()->modify(['tl1', 'k1'], wf\identity) &&
                ($tl3->lens()->modify(['k1'], wf\concatStrings($c1))
                  ->lens()->modify(['k1'], wf\concatStrings($c2)) ==
                 $tl3->lens()->modify(['k1'], wf\compose(
                  wf\concatStrings($c2), wf\concatStrings($c1)))) &&
                ($tl3->lens()->set(['tl2', 'k2'], $c2)
                  ->lens()->view(['tl2', 'k2']) == $c2) &&
                ($tl3->lens()->set(['k1'], $c1)
                  ->lens()->set(['k1'], $c2) ==
                 $tl3->lens()->set(['k1'], $c2)) &&
                // prism consistency
                (Maybe\just($lensOp($tl3->lens(), ['k1'])) ==
                 $lensOp($tl3->prism(), ['k1'])) &&
                (Maybe\nothing() == $lensOp($tl3->prism(), ['non-existent'])) &&
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
