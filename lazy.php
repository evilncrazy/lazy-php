<?php
   class StdLazyList implements Iterator {
      private $seg = array();
      private $iter;
      private $num_iters = 0;
      private $method_next;
      
      public function __construct($iterator) {
         $this->iter = $iterator;
      }
      
      public function rewind() {
         $this->seg = array(array());
         $this->num_iters = 0;
      }
      
      public function current() {
         if(is_array($this->seg[0])) {
            $expand = $this->seg[0];
            array_shift($this->seg);
            $this->seg = array_merge(call_user_func_array($this->iter, $expand), $this->seg);
         }
         return $this->seg[0];
      }
      
      public function key() { return $this->num_iters; }
      
      public function next() {
         if(isset($this->method_next) && is_callable($this->method_next)) {
            call_user_func($this->method_next);
         } else {
            $this->num_iters++;
            array_shift($this->seg);
         }
      }
      
      public function valid() {
         return $this->num_iters < 1000;
      }
      
      public function where($filter) {
         $ret = new StdLazyList($this->iter);
         $ret->method_next = function() use ($filter, &$ret) {
            do {
               $ret->num_iters++;
               array_shift($ret->seg);
            } while($ret->valid() && !call_user_func($filter, $ret->current()));
         };
         return $ret;
      }
   }