<?php
   class StdLazyList implements Iterator {
      private $seg = array();
      private $iter;
      private $num_iters;
      private $max_iters;
      
      public function __construct($iterator, $max_elements = null) {
         $this->iter = $iterator;
         $this->max_iters = $max_elements;
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
         if(isset($this->method_next)) call_user_func($this->method_next);
         else {
            array_shift($this->seg);
            $this->num_iters++;
         }
      }
      
      public function valid() {
         return ((is_null($this->max_iters) || $this->num_iters < $this->max_iters)) &&
                (isset($this->method_valid) ? call_user_func($this->method_valid) : true);
      }
      
      public function where($filter) {
         $ret = new StdLazyList($this->iter, $this->max_iters);
         $ret->method_next = function() use ($filter, &$ret) {
            do {
               array_shift($ret->seg);
            } while($ret->valid() && !call_user_func($filter, $ret->current()));
            $ret->num_iters++;
         };
         return $ret;
      }
      
      public function until($pred) {
         $ret = new StdLazyList($this->iter, $this->max_iters);
         $ret->method_valid = function() use ($pred, $ret) {
            return !call_user_func($pred, $ret->current());
         };
         return $ret;
      }
      
      public function take($length = 1, $offset = 0) {
         $result = array();
         foreach($this as $i => $item) {
            if($i >= $offset) {
               if($i < $offset + $length) {
                  $result[] = $item;
               } else return $result;
            }
         }
         return $result;
      }
   }
   
   function lazy_list($iterator, $max_elements = null) {
      return new StdLazyList($iterator, $max_elements);
   }
   
   function lazy_range($start = 0, $end = null, $step = 1) {
      if($step == 0) throw new Exception("Invalid args");
         
      return lazy_list(function($i = null) use($step, $start) {
         $i = is_null($i) ? $start : $i;
         return array($i, array($i + $step));
      }, is_null($end) ? null : floor(($end - $start) / $step));
   }