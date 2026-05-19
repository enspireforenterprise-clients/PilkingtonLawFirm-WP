'use strict';

(() => {

    document.addEventListener( 'DOMContentLoaded', event => {

        document.addEventListener('submit', function (event) {

            let formId = event.target.id; 

            if(  -1 === formId.indexOf("gform") ) {

                return;

            }

            let data = serializeForm(event.target);

            if( false != data ) {

                if ( typeof submitWebLead === "function" ) {

                    submitWebLead(data);
    
                }

            }

        });

    });

    let serializeForm = function ( form ) {

        let obj = {};
        let formData = new FormData( form );

        for ( let key of formData.keys() ) {

            if(  -1 != key.indexOf("gform") ) {

                continue;

            }

            if(  -1 != key.indexOf("is_submit") ) {

                continue;

            }

            if(  -1 != key.indexOf("captcha") ) {

                continue;

            }

            let nameInput = form.querySelector('[name="' + key + '"]');
            
            if(nameInput) {

                let inputId = nameInput.id;

                let label = form.querySelector('label[for="' + inputId + '"]');

                let ariaRequired = nameInput.getAttribute('aria-required');

                if(ariaRequired) {                    

                    if( ariaRequired == "true" ) {

                        let inputValue = formData.get(key);

                        if( inputValue == "" || inputValue == null ) {

                            return false;

                        }

                    }

                }

                if( label ) {

                    let labelRaw = label.innerHTML;
                    let labelText = labelRaw.replace(/(<([^>]+)>)/gi, "");

                    obj[labelText] = formData.get(key);
    
                }

            }

        }

        return obj;
        
    };

})();
