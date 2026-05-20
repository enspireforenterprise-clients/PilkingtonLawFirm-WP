'use strict';

(() => {

    // -----------------------------
    // Helper: Apply metadata to hidden field
    // -----------------------------
    function applyMetadata(value) {
        var $field = jQuery('.form-matadata input');
        if ($field.length) {
            $field.val(value);
            //console.log('Metadata assigned:', value);
        }
    }

    function setYoTrackMetaData(metadata, err) {
        var value = err ? err : metadata;
        YoTrack.metadata = value;
        applyMetadata(value);

        // Re-apply after Gravity Forms renders
        jQuery(document).on('gform_post_render', function () {
            applyMetadata(value);
        });
    }

    // -----------------------------
    // Initialize YoTrack
    // -----------------------------
    function initiateYoTrack() {
        if (typeof YoTrack === "undefined") {
            console.log("YoTrack not loaded");
            return;
        }

        try {
            YoTrack(yoShortname, yoCid, function(err, api) {
                if (typeof api === "undefined") {
                    console.log("YoTrack blocked");
                    return;
                }

                window.yoApi = api;

                if (api.getMetadata !== undefined) {
                    api.getMetadata(function(err, metadata) {
                        setYoTrackMetaData(metadata, err);
                    });
                    YoTrack.getMetadata = api.getMetadata;
                }

                if (typeof yoPhones !== 'undefined') {
                    api.swapPhones(yoPhones, function(err, line) {
                        console.log("YoTrack line swapped:", line);
                    });
                }
            });
        } catch (e) {
            console.log("Error initializing YoTrack:", e);
        }
    }

    (function() {
        console.log('Initializing YoTrack');
        initiateYoTrack();
    })();

    // -----------------------------
    // Form Submission Handler
    // -----------------------------
    document.addEventListener('submit', function(event) {

        let formId = event.target.id;

        if (-1 === formId.indexOf("gform")) {
            return; // Not a Gravity Form
        }

        let data = serializeForm(event.target);

        // Only track via YoTrack if Enspire API is NOT enabled
        var enspireEnabled = false;
        if (typeof efeCentermarkSettings !== 'undefined' && typeof efeCentermarkSettings.enspire_api_enabled !== 'undefined') {
            enspireEnabled = !!efeCentermarkSettings.enspire_api_enabled;
        }

        if (!enspireEnabled) {

            if (!data) return;

            // Retry queue in case YoTrack hasn't loaded yet
            let trySend = function(retries = 5) {
                if (typeof window.yoApi !== "undefined") {
                    window.yoApi.trackData(data, function(err, res) {
                        console.log('Lead sent via YoTrack', err, res);
                    });
                } else if (retries > 0) {
                    setTimeout(function() {
                        trySend(retries - 1);
                    }, 300);
                } else {
                    console.log('YoTrack never loaded, lead lost');
                }
            };

            trySend();
        }
    });

    // -----------------------------
    // Serialize form into object
    // -----------------------------

    function serializeForm(form) {
    let obj = {};
    let formData = new FormData(form);

    for (let key of formData.keys()) {

        //  Skip Gravity Forms system/internal fields
        if (
            key.indexOf("gform") !== -1 ||
            key.indexOf("is_submit") !== -1 ||
            key.indexOf("captcha") !== -1 ||
            key.indexOf("state") !== -1 ||
            key.indexOf("version_hash") !== -1
        ) continue;

        //  Skip multi-input fragments like 6.1, 6.2
        if (key.match(/^\d+\.\d+$/)) continue;

        let input = form.querySelector('[name="' + key + '"]');
        if (!input || input.closest('.no-yotrack')) continue;

         if (input.closest('.form-matadata')) continue;

        //  Skip hidden inputs EXCEPT metadata
        if (input.type === "hidden" && key.toLowerCase() !== "metadata") continue;

        let value = formData.get(key);

        //  Skip empty values
        if (!value) continue;

        let ariaRequired = input.getAttribute('aria-required');
        if (ariaRequired === "true" && !value) {
            return false;
        }

        let label = form.querySelector('label[for="' + input.id + '"]');

        //  Skip if no proper label (prevents "1:" issue)
        if (!label) continue;

        let labelText = label.textContent.trim();

        if (labelText.toLowerCase() === 'metadata') continue;

        //  Skip garbage labels like "1", "2", etc.
        if (!labelText || labelText.match(/^\d+$/)) continue;

        obj[labelText] = value;
    }

    return obj;
}

})();