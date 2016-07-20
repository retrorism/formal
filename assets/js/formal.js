(function($){
    $.widget('wgiog.formal', {

        options: {
            // key of the form, unique identifier to this particular form
            key: null,
            
            // Events
            beforeSubmit: undefined,
            collectData: undefined,
            submit: undefined,
            response: undefined,
            finalSubmit: null,
            
            // Message Container options
            messageContainerSelector: '.formal-messages',
            messageContainerTemplate: '<div class="formal-report"><div class="formal-report-list"></div></div>',
            messageContainerItemTemplate: '<div class="formal-report-list-item"><div class="message"></div></div>',
            
            // Settings
            serializeFilter: false, // only serialize fields that have the data attribute 'serialize' set to true
            debug: true,
        },

        _create: function() {
            if(!this.options.key) {
                this.options.key = this.element.attr('id') || 'unknown';
            }
            
            $('<input/>').attr({ type: 'hidden', name: 'formal-key'}).data('serialize', true).val(this.options.key).appendTo(this.element);
            
            // Attach events to form
            this._on({
                'submit': this._handleSubmit 
            });
            
            this.log('Instance created (key: ' + this.options.key + '}');
        },

        _handleSubmit: function(e) {
            e.preventDefault();
            var that = this;
            var serializedFormData;
            
            this.log('form submitted');
            this.log('Options in handlesubmit');
            
            // fire events
            if(typeof this.options.beforeSubmit === 'function') this._trigger('beforeSubmit', null, that);
            if(typeof this.options.submit === 'function') return this._trigger('submit');
            
            if(this.options.serializeFilter) {
                serializedFormData = $(this.element[0].elements).filter(function() { return $(this).data('serialize') == true; }).serializeArray();
            } else {
                serializedFormData = this.element.serializeArray();
            }
            
            if(typeof this.options.collectData === 'function') {
                serializedFormData = this.options.collectData(serializedFormData);
            }
            
            this.log('serialized data to be sent to server:');
            this.log(serializedFormData);
            
            $.post(this.element.attr('action'), serializedFormData, function(data) {
                that.handleResponse(data);
            }, 'json')
                    .fail(function() { 
                        alert('something went wrong') 
                    });
                    
            this.log('data sent to server');
        },

        handleResponse: function(response) {
            this.log('got a response:');
            this.log(response);
    
            var status = response.status || 'error';
            var messages = response.messages || [];
            var data = response.data || [];
            
            if(typeof this.options.response === 'function') {
                if(this.options.response(status, messages, data, this)) return;
            }
            
            if(status === 'ok') {
                if(typeof this.options.finalSubmit === 'function') {
                    return this.options.finalSubmit(this);
                }
                this.element.off('submit');
                return this.element.trigger('submit');
            }
            
            this._displayErrors(messages);
        },

        _displayErrors: function(messages) {
            this.log('displaying errors');
    
            var that = this;
            var messageContainer = $(this.options.messageContainerTemplate);
            $.each(messages, function(field, message) {
                var messageContainerItem = $(that.options.messageContainerItemTemplate);
                $('.message', messageContainerItem).html(message);
                messageContainerItem.appendTo($('.formal-report-list', messageContainer));
            });
            $(this.options.messageContainerSelector).html(messageContainer.html()).show();
            
            this.log('done displaying errors');
        },

        log: function(message) {
            if(!console.log || !this.options.debug) return;
            
            if((typeof message) === 'object') return console.log(message);
            
            console.log('[Formal] ' + message);
        }
    });
})(jQuery);