'use strict';

(() => {

    document.addEventListener( 'DOMContentLoaded', event => {


        if (typeof yoShortname === "undefined" || typeof yoCid === "undefined" || typeof yoPhones === "undefined" ) {
            return;
        }

        if( ! yoApi ) {

            YoTrack(yoShortname, yoCid, function(err, api) {

                api.swapPhones(yoPhones, function(err, line) {
                    //console.log("line", line); // Tracking Line formatted as XXX-YYY-ZZZZ
                });
    
    
                yoApi = api;
    
            });

        }

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

                    yoApi.trackData(fields, function(err, data) {
                        // console.log("data", data);
                    });
                    
                }

            }, 
    
        });

        new efeSubmitController();

    });


})();
