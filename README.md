lazy-php
========

Brings lazily evaluated lists to PHP. Lazy lists don't store the entire list in memory, but rather evaluates at runtime.

Examples
========

```php
// $fibs is a lazy list of the first 20 fibonacci numbers
$fibs = lazy_list(function($m = 0, $n = 1) { return array($n, array($m + $n)); }, 20);

// print them out using a foreach loop
foreach($fibs as $i => $fib) {
   echo ($i + 1) . '. ' . $fib . '\n';
}

// $primes is a lazy list of all prime numbers
$primes = lazy_range(2)->where(function($x) {
   foreach(range(2, $x - 1) as $factor) {
      if($x % $factor == 0) return false;
   }
   return true;
});

// take the first 100 primes
$first_primes = $primes->take(100);