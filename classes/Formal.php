<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main validation interface
 *
 * @package    Kohana/Formal
 * @author     WGioG
 * @copyright  (c) 2014 WGioG
 * @license    http://www.gnu.org/licenses/gpl.txt
 */
class Formal {
    /**
     * @var String Unique key of the posted form
     */
    private $_formal_key = null;
    
    /**
     * @var ConfigGroup The configuration for the current form
     */
    private $_configuration = null;
    
    /**
     * @var Kohana_Validation The validation object
     */
    private $_validation_object = null;
    
    /**
     * @var Array List of registered fields
     */
    private $_fields = array();
    
    /**
     * @var Array List of error messages not belonging to a field
     */
    private $_custom_error_messages = array();
    
    /**
     * @var Array List of key/value pairs send back with the response
     */
    private $_data = array();
    
    /***************************************************************************
     * Initialization
     **************************************************************************/
    
    /**
     * As there can only be sent one form at a time, it has no use to load more 
     * than one instance. Hence the Singleton
     */
    public static function factory($key=null) {
        $o = new Formal();
        
        if(!is_null($key)) {
            $o->key($key);
        } else {
            $o->key(Request::initial()->post('formal-key'));
        }
        
        $o->register();
        
        return $o;
    }
    
    private function __construct() {}
    
    /**
     * Register the form.
     * 
     * @return boolean True if everything went well
     * @throws Kohana_Exception When something went wrong
     */
    private function register() {
        $this->verify_key();
        
        // Create new validation object
        $this->_validation_object = Validation::factory(Request::initial()->post());
        
        // Load configuration
        $this->_configuration = Kohana::$config->load('formal/rules')->get($this->key());
        if(is_null($this->_configuration)) {
            throw new Kohana_Exception('No configuration found for key :key', array(':key'  => $this->key()));
        }
        
        if(!array_key_exists('fields', $this->_configuration)) {
            $this->error(null, 'No field configuration found');
        } else {
            foreach($this->_configuration['fields'] as $field_name => $field_config) {
                $this->_fields[$field_name] = new Formal_Field($this->_validation_object, $field_name, $field_config);
                if(array_key_exists('label', $field_config)) {
                    $this->_fields[$field_name]->label($field_config['label']);
                }
            }
        }
        
        return true;
    }
    
    /***************************************************************************
     * Core
     **************************************************************************/
    
    /**
     * Traverses and validates all fields
     * 
     * @return boolean True if every field validates, false otherwise.
     */
    public function check() {
        return $this->_validation_object->check() && count($this->_custom_error_messages) <= 0;
    }
    
    /**
     * If field is set the params will be passed to the Kohana validation 
     * object. Of field is null, an error message will be added that will be 
     * parsed and send back to the jQuery plugin, but does not belong to a 
     * particular field
     * 
     * @param string $field Field name
     * @param string $error Error message
     * @param array $params parameters
     * @return Kohana_Validation
     */
    public function error($field, $error=null, array $params = null) {
        if(is_null($error)) { // no field set, @param $field is handled as @param $error
            return $this->_custom_error_messages[] = $field;
        }
        
        if(!array_key_exists($field, $this->_fields)) {
            throw new Kohana_Exception('Field ":field" does not exist.', array(':field' => $field));
        }
        
        return $this->_fields[$field]->error($error, $params);
    }
    
    /**
     * Return all error messages
     * 
     * @param type $message_template
     * @return type
     */
    public function errors($message_file = null) {
        return array_merge($this->_validation_object->errors($message_file), $this->_custom_error_messages);
    }
    
    /**
     * Return JSON representation of status and messages
     * 
     * @return String/JSON
     */
    public function json_report($message_file = 'validation') {
        $_report = array();
        $_report['status'] = $this->check() ? 'ok' : 'error';
        
        foreach($this->errors($message_file) as $field => $message) {
            $_report['messages'][$field] = $message;
        }
        $_report['data'] = $this->_data;
        
        return json_encode($_report);
    }
    
    public function data($key, $value=null) {
        if(is_null($value)) return Arr::get($this->_data, $key, null);
        return $this->_data[$key] = $value;
    }
    
    /**
     * Reset this object (needs you to re-register!). Only the key is saved.
     */
    public function reset() {
        $this->_configuration = null;
        $this->_validation_object = null;
        $this->_fields = array();
    }
    
    /***************************************************************************
     * Getters/setters/checks
     **************************************************************************/
    
    public function add_field($field, Array $config = null, $label=null) {
        if(!is_null($this->field($field))) return null;
        return $this->_fields[$field] = new Formal_Field($this->_validation_object, $field, $config);
    }
    
    public function field($field) {
        return array_key_exists($field, $this->_fields) ? $this->_fields[$field] : null;
    }
    
    public function fields() {
        return $this->_fields;
    }
    
    
    /**
     * Get/set the key
     * 
     * @param String $key The key
     * @return String Current key when called as getter, new key otherwise
     */
    public function key($key=null) {
        if(is_null($key)) {
            return $this->_formal_key;
        }
        
        $this->reset(); // new key, current object is useless!
        
        return $this->_formal_key = $key;
    }
    
    public function &validation_object() {
        return $this->_validation_object;
    }


    /**
     * Verifies if (1) a key is posted with the form, (2) if strict checking is 
     * enabled if the posted key matches the key that was passed when 
     * instantiating the Formal object.
     * 
     * @return boolean true if all checks passed
     * @throws Kohana_Exception When something went wrong.
     */
    public function verify_key() {
        if(is_null(Request::initial()->post('formal-key'))) {
            throw new Kohana_Exception('No key posted.');
        }
        
        if(Kohana::$config->load('formal')->key_required && Request::initial()->post('formal-key') != $this->_formal_key) {
            throw new Kohana_Exception('The key known to this instance (:ckey) does not match the key found in the POST request (:pkey).', 
                    array(
                        ':ckey' => $this->_formal_key,
                        ':pkey' => Request::initial()->post('formal-key')
                    )
            );
        }
        return true;
    }
    
    /**
     * Example rule. Should be deleted
     * 
     * @param type $validation
     * @param type $field
     * @param type $value
     * @return boolean
     */
    public static function example($validation, $field, $value) {
        if($value != 'string') $validation->error($field, 'example_rule');
        return true;
    }
}