'use strict';

(() => {

    let xhr, locations;

    document.addEventListener('efeInfowindowOpen', event => {

        updateInfoWindow();    

    });

    window.addEventListener( 'load', event => {

        checkCookie();
        swapLocationNumbers();
        trackLinks();
        setTimeout(trackPageConversion, 1000);
        
    });


    function swapLocationNumbers() {

        let efeSwapDiv = document.querySelector('.efe-centermark-swap-results');

        if( !efeSwapDiv ) {
            return;
        }

        let provider = getCookie( 'efe-centermark-provider' );
        let bucket = "unpaid";

        if( "" !== provider ) {

            bucket = "paid";
            
        }

        let queryString = `bucket=${bucket}`;

        xhr = new XMLHttpRequest();

        xhr.open( 'GET', '/efe-centermark/sites/select-all/?' + queryString, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

        xhr.onreadystatechange = function() {

			if( this.readyState === XMLHttpRequest.DONE && this.status === 200 ) {

				let response = JSON.parse( this.response );

				if( true === response.success ) {

                    if(response.payload.sites) {

                        locations = response.payload.sites;

                        locations.forEach( function( location ) {

                            swapPhones( location.original_phones, location.phoneNumbers[0], location.clientId );

                        });

                    }
					
				} else {
					
					console.log( 'FAIL', response );

				}

			}

		};

		xhr.send();

    }

    function checkCookie() {

        let links = document.querySelectorAll('a');

        let provider = getCookie( 'efe-centermark-provider' );

        if( "" !== provider ) {

            updateLinks( links, provider );
            
        } else {

            const urlParams = new URLSearchParams(window.location.search);
            const efeProvider = urlParams.get('provider');

            if( efeProvider) {

                setCookie('efe-centermark-provider', efeProvider)
                updateLinks( links, efeProvider );

            }

        }

    }


    function updateInfoWindow() {

        console.log(locations);

        if( locations ) {

            locations.forEach( function( location ) {

                swapPhones( location.original_phones, location.phoneNumbers[0], location.clientId );

            });

        }

    }



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

    function setCookie(cname, cvalue, exdays) {

        let d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";

    }

    function updateLinks(links, provider) {

        if( !provider || 0 === links.length ) {
            return;
        }

        links.forEach( function( link ) {

            if(  -1 < link.href.indexOf("tel:") || '#' === link.href || '' === link.href ) {

                return;

            }

            let url = new URL( link.href );
            let linkParams = new URLSearchParams( url.search );
            

            if( true === linkParams.has('provider') ) {

                return;

            }

            if( 0 < linkParams.length ) {

                linkParams.append( 'provider', provider);

            } else {

                linkParams.set('provider', provider);

            }

            link.href = link.href + '?' + linkParams.toString();

        });

    }

    function trackLinks() {

        let trackingLinks = document.querySelectorAll('a[data-efe-track="true"]');

        if(! trackingLinks ) {
            return;
        }

        if( 0 > trackingLinks.length ) {
            return;
        }

        trackingLinks.forEach( function(trackingLink) {

            let source = 'Web Link';

            trackingLink.addEventListener('click', function( event ) {

                if( trackingLink.dataset.efeSource ) {

                    source = trackingLink.dataset.efeSource;
    
                    let href    = trackingLink.getAttribute( 'href' );
                    let email   = Date.now() + '@efeleadclick.com';
                    let payload = {
                        href: href, 
                        source:  source,
                        email: email
                    };

                    let yoState = YoTrack.getYoState();

                    if( 'failed' === yoState.initializationData.status ) {
                        return;
                    }
                
                    if( typeof yoApi !== "undefined" ) {

                        yoApi.trackData(payload, function(err, data) {
                            /* Payload submission complete */
                            //console.log("data", data); // Payload sent to dashboard as JSON Object
                        });

                    } else {

                        submitWebLead(payload);

                    }

                    
    
                }

            });

        });
    }

    function trackPageConversion() {

        let conversionElement = document.querySelector('[data-efe-conversion="true"]');

        let source = 'Page Conversion';

        if(! conversionElement ) {
            return;
        }

        if( 0 > conversionElement.length ) {
            return;
        }

        if( conversionElement.dataset.efeSource ) {

            source = conversionElement.dataset.efeSource;

        }   

        let href    = window.location.href;
        let email   = Date.now() + '@efeleadpage.com';
        let payload = {
            href: href, 
            source:  source,
            email: email
        };

        if( typeof yoApi !== "undefined" ) {

            yoApi.trackData(payload, function(err, data) {
                /* Payload submission complete */
                //console.log("data", data); // Payload sent to dashboard as JSON Object
            });

        } else {

            submitWebLead(payload);

        }


    }

    function cleanNumber(number) {

        number = number.replace(/\D/g, '');
        if (number.length === 11 && number[0] === '1') {
            number = number.substring(1);
        }
        if (number.length !== 10) {
            throw new Error("Number has invalid length and/or invalid country.");
        }
        return [number.substring(0, 3), number.substring(3, 6), number.substring(6, 10)];

    }

    function buildNumberRegex(number) {

        if (number) {

            var numberParts = cleanNumber(number);

            return RegExp("((1[\\s-.\\/]*)?)((\\(" + numberParts[0] + "\\)?)|(" + numberParts[0] + "))([\\s-.\\/]*)(" + numberParts[1] + ")([\\s-.\\/]*)(" + numberParts[2] + ")", "g");

        } else {

            return RegExp("((1[\\s-.\\/]*)?)((\\(\\d{3}\\)?)|(\\d{3}))([\\s-.\\/]*)(\\d{3})([\\s-.\\/]*)(\\d{4})", "g");

        }

    }

    function swapNumber(el, numberToSwap, trackingLine, clientId ) {

        let phoneRegex = buildNumberRegex(numberToSwap);
        let trackingFormatted;

        let match = trackingLine.match(/^(\d{3})(\d{3})(\d{4})$/);

        if (match) {
            trackingFormatted =  match[1] + '-' + match[2] + '-' + match[3]
        };


        textNodesUnder(el).forEach(function (textNode) {
            
            if( textNode.data && phoneRegex.test(textNode.data.toString() ) ) {

                let parentNode = textNode.parentElement; 
                
                if ( parentNode.dataset.efeCid ) {

                    if ( parentNode.dataset.efeCid == clientId ) {

                        textNode.data = textNode.data.toString().replace(phoneRegex, trackingFormatted);

                    }

                }

            }

            if (textNode.href && phoneRegex.test(textNode.href.toString())) {
                
                if ( textNode.dataset.efeCid ) {

                    if ( textNode.dataset.efeCid == clientId ) {

                        textNode.href = textNode.href.toString().replace(phoneRegex, trackingLine);

                    }

                }

            }

        });

    }

    function swapPhones(swapFromPhoneList, trackingLine, clientId ) {
        
        var el = document.documentElement;

        var arrayLen = swapFromPhoneList.length;
        for (var i = 0; i< arrayLen; i++) {
            swapNumber(el, swapFromPhoneList[i], trackingLine, clientId );
        }

    }

    function textNodesUnder(el) {
        var n, a = [], walk = document.createTreeWalker(el, NodeFilter.SHOW_ALL, null, false);
        while (n = walk.nextNode()) {
            a.push(n);
        }
        return a;
    }

})();