var il = il || {};
il.UI = il.UI || {};

(function($, UI) {

    UI.modal = (function ($) {

        var defaultShowOptions = {
            backdrop: true,
            keyboard: true,
            ajaxRenderUrl: '',
            trigger: 'click'
        };

        var initializedModalboxes = {};


        var showModal = function (id, options) {
            options = $.extend(defaultShowOptions, options);
            if (options.ajaxRenderUrl) {
                var $container = $('#' + id);
                $container.load(options.ajaxRenderUrl, function() {
                    var $modal = $(this).find('.modal');
                    if ($modal.length) {
                        $modal.modal(options);
                    }
                });
            } else {
                var $modal = $('#' + id);
                $modal.modal(options);
            }
        };

        var closeModal = function (id) {
            $('#' + id).modal('close');
        };

        /**
         * Show a modal for a triggerer element (the element triggering the show signal) with the given options.
         *
         * @param signalData Object containing all data from the signal
         * @param options Object with modalbox options
         */
        var showFromSignal = function (signalData, options) {
            var $triggerer = signalData.triggerer;
            if (!$triggerer.length) {
                return;
            }
            var triggererId = $triggerer.attr('id');
            if (signalData.event === 'mouseenter') {
                options.trigger = 'hover';
            }
            var initialized = show($triggerer, options);
            if (initialized === false) {
                initializedModalboxes[signalData.id] = triggererId;
            }
        };

        /**
         * Replace the content of the modalbox showed by the given showSignal with the data returned by the URL
         * set in the signal options.
         *
         * @param showSignal ID of the show signal for the modalbox
         * @param signalData Object containing all data from the replace signal
         */
        var replaceContentFromSignal = function (showSignal, signalData) {
            console.log(signalData);
            // Find the ID of the triggerer where this modalbox belongs to
            var triggererId = (showSignal in initializedModalboxes) ? initializedModalboxes[showSignal] : 0;
            if (!triggererId) return;
            // Find the content of the modalbox
            var $triggerer = $('#' + triggererId);
            var url = signalData.options.url;
            replaceContent($triggerer, url);
        };

        /**
         * Replace the content of the modalbox of the $triggerer JQuery object with the data returned by the
         * given url.
         *
         * @param $triggerer JQuery object where the modalbox belongs to
         * @param url The URL where the ajax GET request is sent to load the new content
         */
        var replaceContent = function($triggerer, url) {
            var $content = $('#' + $triggerer.attr('data-target')).find('.modal-content');
            if (!$content.length) return;
            $content.html('<i class="icon-refresh"></i><p>&nbsp;</p>');
            $content.load(url, function() {
                console.log('loaded');
            });
        };

        return {
            showModal: showModal,
            closeModal: closeModal,
            showFromSignal: showFromSignal,
            replaceContentFromSignal: replaceContentFromSignal,
            replaceContent: replaceContent
        };

    })($);

})($, il.UI);