/**
 * Proximity event for jQuery
 * ---
 * @author James Padolsey (http://james.padolsey.com)
 * @version 0.1
 * @updated 28-JUL-09
 * ---
 * @info http://github.com/jamespadolsey/jQuery-plugins/proximity-event/
 */

(function($){

    var elems = $([]),
        doc = $(document);

    $.event.special.proximity = {

        defaults: {
            max: 100,
            min: 0,
            throttle: 0,
            fireOutOfBounds: 1
        },

        setup: function(data) {

            if (!elems[0])
                doc.mousemove(handle);

            elems = elems.add(this);

        },

        add: function(o) {

            var handler = o.handler,
                data = $.extend({}, $.event.special.proximity.defaults, o.data),
                lastCall = 0,
                nFiredOutOfBounds = 0,
                hoc = $(this),
                t = this;

            o.handler = function(e, clientX, clientY) {

                var max = data.max,
                    min = data.min,
                    throttle = data.throttle,
                    date = +new Date,
                    distance,
                    proximity,
                    inBounds,
                    fireOutOfBounds = data.fireOutOfBounds;

                if (throttle && lastCall + throttle > date) {
                    return;
                }

                lastCall = date;

                distance = calcDistance(t, clientX, clientY);
                inBounds = distance < max && distance > min;

                if (fireOutOfBounds || inBounds) {

                    if (inBounds) {
                        nFiredOutOfBounds = 0;
                    } else {

                        // If fireOutOfBounds is a number then keep incrementing a
                        // counter to determine how many times the handler's been
                        // called out of bounds. Note: the counter is reset whenever
                        // the cursor goes back inBounds...

                        if (typeof fireOutOfBounds === 'number' && nFiredOutOfBounds > fireOutOfBounds) {
                            return;
                        }
                        ++nFiredOutOfBounds;
                    }

                    proximity = e.proximity = 1 - (
                        distance < max ? distance < min ? 0 : distance / max : 1
                    );

                    e.distance = distance;
                    e.clientX = clientX;
                    e.clientY = clientY;
                    e.data = data;

                    return handler.call(this, e, proximity, distance);

                }

            };

        },

        teardown: function(){

            elems = elems.not(this);

            if (!elems[0])
                doc.unbind('mousemove', handle);

        }

    };

    function calcDistance(el, x, y) {

        // Calculate the distance from the closest edge of the element
        // to the cursor's current position

        var box, left, right, top, bottom,
            cX, cY, dX, dY,
            distance = 0;

        box = el.getBoundingClientRect();
        left = box.x;
        top = box.y;
        right = box.right;
        bottom = box.bottom;

        cX = x > right ? right : x > left ? x : left;
        cY = y > bottom ? bottom : y > top ? y : top;

        dX = Math.abs( cX - x );
        dY = Math.abs( cY - y );

        return Math.sqrt( dX * dX + dY * dY );

    }

    function handle(e) {

        var x = e.clientX,
            y = e.clientY,
            i = -1,
            fly = $([]);

        while (fly[0] = elems[++i]) {
            fly.triggerHandler('proximity', [x,y]);
        }

    }

}(jQuery));
