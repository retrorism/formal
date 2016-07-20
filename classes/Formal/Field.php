<?php defined('SYSPATH') or die('No direct script access.');

class Formal_Field {
    private $_validation;
    private $_field;
    private $_label;
    
    function __construct(Kohana_Validation &$validation_object, $field, Array $configuration = null) {
        $this->_validation = $validation_object;
        
        $this->field($field);
        if(!is_null($configuration)) $this->parse_configuration($configuration);
    }
    
    private function parse_configuration($configuration) {
        if(!is_array($configuration) || empty($configuration)) {
            throw new Kohana_Exception('Configuration not found');
        }
        
        foreach($configuration['rules'] as $callback => $parameters) {
            if(is_numeric($callback)) { // singular rule, without parameters        
                $callback = $parameters;
                $parameters = null;
            }
            $this->rule($callback, $parameters);
        }
    }
    
    public function label($label = null) {
        if(is_null($label)) {
            if(is_null($this->_label)) {
                return $this->field();
            }
        }
        $this->_label = $label;
        return $this->_validation->label($this->field(), $label);
            
    }
    
    public function field($field = null) {
        if(is_null($field)) return $this->_field;
        return $this->_field = $field;
    }
    
    public function rule($callback, $parameters) {
        return $this->_validation->rule($this->field(), $callback, $parameters);
    }
    
    public function error($error, $params) {
        return $this->_validation->error($this->field(), $error, $params);        
    }
}