'use strict';

(() => {

    document.addEventListener( 'DOMContentLoaded', event => {

        if ( typeof Marionette === 'undefined' ) {

            return;

        }

        let efeSubmitController = Marionette.Object.extend( {
    
            initialize: function() {
                this.listenTo( nfRadio.channel( 'forms' ), 'after:submitValidation', this.sendLead ); 
            },
    
            sendLead: function( formModel ) {

                let fields = {};
                _.each( formModel.get( 'fields' ).models, function( field ) {

                    let key = field.get('key');

                    let label = field.get('label');

                    if( 'submit' == key || 'recaptcha' == key ) {
                        return;
                    }

                    fields[ label ] = field.get('value');

                } );

                if( fields ) {

                    if ( typeof submitWebLead === "function" ) {
    
                        submitWebLead(fields);
        
                    }
                    
                }

            }, 
    
        });

        new efeSubmitController();

    });


})();
