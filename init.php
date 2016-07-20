<?php defined('SYSPATH') or die('No direct script access.');

Route::set('formal', 'formal(/<controller>(/<action>))')
        ->defaults(array(
            'directory' => 'formal',
            'controller' => 'examples',
            'action' => 'index'
        ));