<?php defined('SYSPATH') or die('No direct script access.'); ?>
<!DOCTYPE html> 
<html> 
    <head> 
        <title>Formal - Kohana Form handling module</title>
        <link rel="stylesheet" type="text/css" href="<?php echo URL::base() . 'assets/css/formal.css'; ?>" />
        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script> 
        <script type="text/javascript" src="<?php echo URL::base() . 'assets/js/formal.js'; ?>"></script>
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#example-basic').formal({
                    messageContainerSelector: '.formal-messages.example-basic'
                });
                
                $('#example').formal({
                    messageContainerSelector: '.formal-messages.example',
                    beforeSubmit: function(e, widget) {
                        alert('Before submit. Key: ' + widget.options.key);
                    },
                    submit_: function(e) {
                        alert('Too bad, have to do it myself...');
                    },
                    collectData: function(data) {
                        return $.merge(data, [{name: 'customAdded', value: 'custom value'}])
                    },
                    response: function(status, messages, response, widget) {
                        alert('Got a response from the server. Status: ' + status);
                    }
                });
            });
        </script>
    </head>
    
    <body>
        <div class="container">
            <h1>Formal</h1>
            <p>
                Refer to <a href="https://github.com/wgiog/kohana-formal/">GitHub</a>
                more info.
            </p>
        
            <h1>Examples</h1>
            
            <h2>Basic</h2>
            
            <form action="" method="post" id="example-basic">
                <fieldset>
                    <div class="formal-messages example-basic"></div>
                    
                    <div class="row">
                        Input just a value<br>
                        <input type="text" name="textfield" value="" />
                    </div>
                    
                    <button type="submit" class="submit"><span>Send</span></button>
                </fieldset>
            </form>

            <h2>A large form</h2>
            
            <form action="" method="post" id="example">
                <fieldset>
                    <div class="formal-messages example"></div>
                    
                    <div class="row">
                        String<br>
                        <input type="text" name="string" class="required" />
                    </div>
                    <div class="row">
                        E-mail<br>
                        <input type="text" name="e-mail" class="required" />
                    </div>
                    <div class="row">
                        Range<br>
                        <input type="text" name="range" class="required" />
                    </div>
                    <div class="row">
                        Numeric<br>
                        <input type="text" name="numeric" class="required" />
                    </div>
                    <div class="row">
                        Date<br>
                        <input type="text" name="date" class="required" />
                    </div>
                    <div class="row">
                        Password<br>
                        <input type="text" name="password" class="required" />
                    </div>
                    <div class="row">
                        Confirm<br>
                        <input type="text" name="confirm" class="required" />
                    </div>

                    <button type="submit" class="button"><span>Validate and submit!</span></button>
                    <div class="clear"></div>
                </fieldset>
            </form>
            
        </div> <!-- eo. container -->
    </body>
</html>