(function () {
    'use strict';

    // Add CustomEvent to Internet Explorer
    if (!('CustomEvent' in window)) {
        // Credit: https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent
        function CustomEvent(event, options) {
            options = options || {bubbles: false, cancelable: false, detail: undefined};
            var e = document.createEvent('CustomEvent');
            e.initCustomEvent(event, !!options.bubbles, !!options.cancelable, options.detail);
            return e;
        }

        CustomEvent.prototype = window.Event.prototype;
        window.CustomEvent = CustomEvent;
    }
}());
