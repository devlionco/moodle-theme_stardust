/* jshint ignore:start */
define(['core/yui', 'core/log'], function(Y, log) {

    "use strict"; // jshint ;_;
    return {
        init: function() {
            //debugger;

            log.debug('Davidson Stardust AMD load src/usermenu');

            //var initiate_usermenu = function () {
            Y.use('node-base', 'node-event-simulate', 'node-menunav', function (Y) {
                var menus = Y.all('#region-main .profilepicture .useractionmenu');

                menus.each(function(menu) {
                    Y.on("contentready", function() {
                        this.plug(Y.Plugin.NodeMenuNav, {autoSubmenuDisplay: false});
                        var submenus = this.all('.yui3-loading');
                        submenus.each(function(n) {
                            n.removeClass('yui3-loading');
                        });

                    }, "#" + menu.getAttribute('id'));
                });
            });
        }
    };
});
/* jshint ignore:end */
