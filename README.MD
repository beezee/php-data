# php-data

API and underlying representation is still experimental.

This library defines Algebraic Data Types for PHP, 
with lenses and prisms for free.


```php
require('./vendor/autoload.php');

class Pet extends Data\Data {
  static function constructors() {
    // eventually you would validate inputs here
    // php-data library to provide refined "types" before stable
    return [
      'Dog' => Widmogrod\Functional\identity,
      'Cat' => Widmogrod\Functional\identity,
      'Fish' => Widmogrod\Functional\identity
    ];
  }
}

php>  $dog = Pet::Dog(['fur' => 'brown']); 
/* Pet Object
(
    [_k:Data\Data:private] => Dog
    [_d:Data\Data:private] => Array
        (
            [fur] => brown
        )

)
 */
php>  $cat = Pet::Cat(['fur' => 'tuxedo']); 
/* Pet Object
(
    [_k:Data\Data:private] => Cat
    [_d:Data\Data:private] => Array
        (
            [fur] => tuxedo
        )

)
 */
php>  $fish = Pet::Fish(['color' => 'gold']); 
/* Pet Object
(
    [_k:Data\Data:private] => Fish
    [_d:Data\Data:private] => Array
        (
            [color] => gold
        )

)
 */

$describe = [
  'Dog' => function($d) { return "The dog has {$d->fur} fur"; },
  'Cat' => function($c) { return "The cat has {$c->fur} fur"; },
  'Fish' => function($f) { return "The fish has {$f->color} scales"; }
];

php>  $dog->fold($describe); 
/* The dog has brown fur */
php>  $cat->fold($describe); 
/* The cat has tuxedo fur */
php>  $fish->fold($describe); 
/* The fish has gold scales */

class Tree extends Data\Data {
  static function constructors() {
    return [
      'Branch' => Widmogrod\Functional\identity,
      'Leaf' => Widmogrod\Functional\identity
    ];
  }
}

$t = Tree::Branch(
  ['l' => Tree::Leaf(['value' => 1]), 
   'r' => Tree::Branch([
      'l' => Tree::Leaf(['value' => 2]),
      'r' => Tree::Branch([
        'l' => Tree::Leaf(['value' => 3]),
        'r' => Tree::Leaf(['value' => 4])])
    ])]);

$toString = function($a) { return json_encode($a, JSON_PRETTY_PRINT); };

php>  $toString($t); 
/* {
    "Tree::Branch": {
        "l": {
            "Tree::Leaf": {
                "value": 1
            }
        },
        "r": {
            "Tree::Branch": {
                "l": {
                    "Tree::Leaf": {
                        "value": 2
                    }
                },
                "r": {
                    "Tree::Branch": {
                        "l": {
                            "Tree::Leaf": {
                                "value": 3
                            }
                        },
                        "r": {
                            "Tree::Leaf": {
                                "value": 4
                            }
                        }
                    }
                }
            }
        }
    }
} */
php>  $t->lens()->view(['r', 'l'])->value; 
/* 2 */
php>  $toString($t->lens()->set(['r', 'r', 'l', 'value'], 5)); 
/* {
    "Tree::Branch": {
        "l": {
            "Tree::Leaf": {
                "value": 1
            }
        },
        "r": {
            "Tree::Branch": {
                "l": {
                    "Tree::Leaf": {
                        "value": 2
                    }
                },
                "r": {
                    "Tree::Branch": {
                        "l": {
                            "Tree::Leaf": {
                                "value": 5
                            }
                        },
                        "r": {
                            "Tree::Leaf": {
                                "value": 4
                            }
                        }
                    }
                }
            }
        }
    }
} */
php>  $toString($t->lens()->modify(['r', 'r', 'r', 'value'], function($i) { return $i + 3; })); 
/* {
    "Tree::Branch": {
        "l": {
            "Tree::Leaf": {
                "value": 1
            }
        },
        "r": {
            "Tree::Branch": {
                "l": {
                    "Tree::Leaf": {
                        "value": 2
                    }
                },
                "r": {
                    "Tree::Branch": {
                        "l": {
                            "Tree::Leaf": {
                                "value": 3
                            }
                        },
                        "r": {
                            "Tree::Leaf": {
                                "value": 7
                            }
                        }
                    }
                }
            }
        }
    }
} */
```

More to come...
