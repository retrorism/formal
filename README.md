#Formal

Kohana module and jQuery plugin to handle form validation at client and server side. It makes use of the Kohana Validation object and adds some useful functionality (eg. adding error messages not specific to fields).

#### What it does

The jQuery plugin and Kohana module work closely together to validate posted (form) data and report back to the user.

For validation the `Kohana_Validation` object is used.

Validation is done fully at server side: your validation rules will _not_ be exposed to the public!

A regular form submission flow looks like this:

1. When the user submits the form, data is sent to the server using Ajax
2. Server checks the input. On fail: step 3. On success: step 4
3. Error messages are sent back to the client, the jQuery plugin displays all errors
    - Step 1 to 3 get repeated until form is validated
4. A 'success' flag is sent back to the client. Submit event is removed from the form and the form will be POST-ed as a normal form
5. Server checks the input one last time (someone could have tried to evade the form...)

Several hooks enable you to control what data is sent for validation, how errors are handled, etc. Building your own validation rules is as simple as adding a php callback.

#### What it does not

Build a form. It can be used to validate forms that are built by a form builder though!

## Setup

#### Requirements

- [Kohana version 3.3+](http://kohanaframework.org/)
- [jQuery](http://jquery.com/)
- [jQuery UI Widget Factory](http://jqueryui.com/widget/)

#### Installation

- Load the jQuery plugin on your html page
- Add the module to your Kohana bootstrap

## Basic usage

#### Attach widget to form

```html
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#myForm').formal();
    });
</script>
```
    
#### Adding rules

Internally Formal uses the [Kohana Validation](http://kohanaframework.org/3.3/guide-api/Validation) object to validate the form. You can use every `Valid` rule, or any [php callback](http://php.net/manual/en/language.types.callable.php) if you'd like.

Add rules to `config/formal/rules.php`. The key of the array element is also the Formal key, an identifier used to distinguish different forms.

```php
return array(
    'myForm' => array( // The form key
        'fields' => array( // list of fields
            'text_field' => array( // <input name="text_field" ... />
                'label' => 'Text field',
                'rules' => array('not_empty', 'max_length' => array(':value', 10)
            ),
            
            ....
        )
    )
);
```
    
#### Validate your form

In your controller, you use a Formal object to validate the form and create responses for the jQuery plugin. You should check if the form is validated, _and_ if the current request is an ajax request. If the latter case Formal will just tell the jQuery plugin that everything is fine and it can go on with either submitting the form for real, or confirm the submission of the form on page.

```php
$formal = Formal::factory('myForm');
    
if(!$formal->check() || $this->request->is_ajax()) {
    return $this->response->body($formal->json_report());
}
    
// do stuff with the validated form
    
$this->response->body('Thanks for filling in my form!');
```

## Api/Hooks

#### jQuery options ####

jQuery options are passed when creating the widget instance, or using the jQuery Widget 'option':

```javascript
$('#myForm').formal({ // ... options here });
$('#myForm').formal('option', 'optionKey', 'option value');
```

Option            | Default           | Description
------------------|-------------------|--------------------------
key               | formal            | Unique identifier to distinguish individual form. Should be the same as the key in your rule configuration
debug             | false             | Log stuff in the browser console


Event             | Default           | Description
------------------|-------------------|--------------------------
beforeSubmit      | undefined         | Called before the form is submitted to the server
collectData       | undefined         | If set, return value of this function is sent to the server
submit            | undefined         | If set, this function will handle the submission of the form (WARNING: you should attach any response handlers yourself!)
response          | undefined         | Return data will be passed to this callback. If it returns false, execution halts. If it returns anything else, after the function is called submission or error displaying will continue

Template          | Default           | Description
------------------|-------------------|---------------------------
messageContainerTemplate | &lt;div class="formal-report"&gt;&lt;div class="formal-report-list"&gt;&lt;/div&gt;&lt;/div&gt; | Should contain at least a div with class `formal-report-list`
messageContainerItemTemplate | &lt;div class="formal-report-list-item"&gt;&lt;div class="message"&gt;&lt;/div&gt;&lt;/div&gt; | Should contain at least a div with class `message`
messageContainerSelector | .formal-messages | Outer container of the message box


## Examples

First we build a HTML form and load the jQuery plugin

```html
<form action="form/validate" method="post" id="myForm">
    Name: <br>
    <input type="text" name="name" value="" /><br>
    <br>
    <input type="text" name="email" value="" /><br>
    <br>
    <button type="submit">Send!</button>
</form>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#myForm').formal();
    });
</script>
```
    
We also need to setup a few rules for this form. Open up (or create) the file `config/formal/rules.php` in the cascading file system.

```php
return array(

    'myForm' => array(
    
        'fields' => array(
        
            'name' => array(
                'label' => 'Name',
                'rules' => array(
                    'not_empty',
                    'max_length' => array(':value', 12)
                )
            ),
            
            'email' => array(
                'label' => 'E-mail address',
                'rules' => array(
                    'not_empty',
                    'email'
                )
            )
            
        )
    )
);
```

Now setup your controller to receive and check form input

```php
class Controller_Form extends Controller {
    function action_validate() {
        $formal = Formal::factory();
        if(!$formal->check() || $this->request->is_ajax()) {
            return $this->response->body($formal->json_report());
        }
        
        $this->response->body('Thanks for your input!');
    }
}
```
