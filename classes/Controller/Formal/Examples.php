<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Formal_Examples extends Controller {
    function action_index() {
        if($this->request->method() == Request::POST) {
            $_formal = Formal::factory();
            
            $_formal->error(null, 'This is just an error');
            
            if(!$_formal->check() || $this->request->is_ajax()) {
                return $this->response->body($_formal->json_report());
            }
            
            echo 'congrats';
        }
        
        $this->response->body(View::factory('formal/examples/index'));
    }
}