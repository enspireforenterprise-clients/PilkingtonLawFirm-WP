'use strict';

(() => {

    let jotforms;

    window.addEventListener( 'load', event => {

        if (typeof yoShortname === "undefined" || typeof yoCid === "undefined" || typeof yoPhones === "undefined" ) {

            return;

        }

        jotforms = document.querySelectorAll('form.jotform-form');

        if( ! yoApi ) {

            YoTrack(yoShortname, yoCid, function(err, api) {

                yoApi = api;

                api.swapPhones(yoPhones, function(err, line) {
                    //console.log("line", line); // Tracking Line formatted as XXX-YYY-ZZZZ
                });

            });

            let timer = window.setInterval( function() {

                if( undefined != yoApi ) {

                    if ( undefined != yoApi.getMetadata  ) {

                        window.clearInterval(timer);
                        getYotrackMetadata( jotforms )

                    }

                }

            }, 100);

        } else {

            if ( jotforms.length <= 0 ) {

                return; 
    
            }

            let timer = window.setInterval( function() {

                if( undefined != yoApi ) {

                    if ( undefined != yoApi.getMetadata  ) {

                        window.clearInterval(timer);
                        getYotrackMetadata( jotforms )

                    }

                }

            }, 100);

        }

    });

    function getCookie(cname) {

        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');

        for(let i = 0; i < ca.length; i++) {

            let c = ca[i];

            while (c.charAt(0) == ' ') {

                c = c.substring(1);

            }
            if (c.indexOf(name) == 0) {

                return c.substring(name.length, c.length);

            }

        }

        return "";

    }

    function setFormData( jotforms, metadata, uid ) {

        if ( jotforms.length <= 0 ) {

            return; 

        }

        jotforms.forEach( function( jotform ) {

            jotform.querySelector('input[name*="metadata"').value = metadata;
            jotform.querySelector('input[name*="uid"').value = uid;

        });

    }

    function getYotrackMetadata( jotforms ) {

        if ( jotforms.length <= 0 ) {

            return; 

        }

        yoApi.getMetadata().then( function( metadata ) {

            let yoUid = getCookie('uid');

            setFormData( jotforms, metadata, yoUid );

        }).catch( function( err ) {

            console.log("Failed to get metadata. Error:", err);

        });


    }


})();


