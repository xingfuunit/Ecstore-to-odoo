/*
 Lib-v1.js 
 Date: 2013-07-23 
 */

"object" != typeof JSON && (JSON = {}),
(function(t) {
    t.fn.Jdropdown = function(e, i) {
        if (this.length) {
            "function" == typeof e && (i = e, e = {});
            var n = t.extend({
                event: "mouseover",
                current: "hover",
                delay: 0
            },
            e || {}),
            a = "mouseover" == n.event ? "mouseout": "mouseleave";
            t.each(this, 
            function() {
                var e = null,
                r = null,
                o = !1;
                t(this).bind(n.event, 
                function() {
                    if (o) clearTimeout(r);
                    else {
                        var a = t(this);
                        e = setTimeout(function() {
                            a.addClass(n.current),
                            o = !0,
                            i && i(a)
                        },
                        n.delay)
                    }
                }).bind(a, 
                function() {
                    if (o) {
                        var i = t(this);
                        r = setTimeout(function() {
                            i.removeClass(n.current),
                            o = !1
                        },
                        n.delay)
                    } else clearTimeout(e)
                })
            })
        }
    }
})(jQuery);