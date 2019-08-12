
define(['jquery', 'core/config', 'core/notification'],
    function($, config, notification) {

        //debugger;
        var preftiming = performance.timing;
        var params = {
            contextid: config.contextid,
            preftiming: JSON.stringify(preftiming),
            sesskey: config.sesskey
        };

        //$(document).ready(function() {
        //$(function() {
        $(window).on('load', function () {
            //console.log("ready!");
            //notification.alert('I am ready');
            //debugger;
            $.post(config.wwwroot + '/log_user_page_pref.php', params)
                .done(function(data, status) {
                    try {
                        console.log(data + status);
                    } catch (err) {
                        //notification.exception(err);
                    }
                })
                .fail(function (jqXHR, status, error) {
                    //notification.exception(error);
                });
        });
    }
);