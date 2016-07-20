<?php defined('SYSPATH') or die('No direct script access.');

return array(
    
    'example' => array(
        'settings' => array(),
        
        'fields' => array(
            
            'string' => array(
                'rules' => array(
                    'Formal::example' => array(':validation', ':field', ':value', 'foo' , 8)
                )
            ),
            
            'e-mail' => array(
                'rules' => array(
                    'email',
                    'not_empty'
                )
            ),
            
            'range' => array(
                'rules' => array(
                    'range' => array(':value', 3, 8), 'not_empty'
                )
            ),
            
            'numeric' => array(
                'rules' => array(
                    'numeric', 'not_empty', 'range' => array(':value', 3, 8), 'max_length' => array(':value', 1)
                )
            ),
            
            'password' => array(
                'rules' => array(
                    'not_empty'
                )
            ),
            
            'confirm' => array(
                'rules' => array(
                    'not_empty',
                    'matches' => array(':validation', 'confirm', 'password')
                )
            ),
            
            'date' => array(
                'rules' => array(
                    'not_empty',
                    'date'
                )
            )
            
        )
    )
);