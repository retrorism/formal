<?php defined('SYSPATH') or die('No direct script access.');

class Validation extends Kohana_Validation {
    public function offsetExists($offset) {
        $temp = Arr::path($this->_data, $offset, null);
        return isset($temp);
    }
    
    public function offsetGet($offset) {
        return Arr::path($this->_data, $offset);
    }
}