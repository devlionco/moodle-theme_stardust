// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Reminder init.
 *
 * @module     local_oercatalog/init
 * @package    local_oercatalog
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
   'jquery',
   'core/str',
   'core/ajax',
   'core/templates',
   'core/notification',
], function($, Str, Ajax, templates, notification) {
    `use strict`;

    return {
        
        TEMPLATE: {
            reminders: {
              src: 'theme_stardust/reminders',
              id: 'reminders'
            },
            reminder: {
              src: 'theme_stardust/reminder',
              id: 'reminder'
            },
        },
        
        init: function() {
            var context = this;
            context.getReminders();
            
            // Events.
            $(document).on('click', '#opennewreminder', function(){
                context.openReminderForm();
            });
            
            $(document).on('click', '#addnewreminder', function(){
                context.addReminder();
            });
            
            $(document).on('click', '.deletereminder', function(){
                context.delReminder($(this));
            });
        },
        
        getReminders: function() {
            Ajax.call([{
                methodname: 'theme_stardust_get_reminders',
                args: {
                },
                done: function (res) {
                    var response = JSON.parse(res);
                    var data = response;
                    
                    templates.render(this.TEMPLATE.reminders.src, data)
                    .then(function(html, js) {
                         $('.popover-region-notifications .popover-region-reminder-div').append(html);
                    }.bind(this))
                    .fail(notification.exception);
            
                    if (data.counthappend > 0) {
                        setTimeout(function () {
                            var allcount = $('.popover-region-notifications .count-container').html();
                            if (allcount) {
                                allcount = + allcount + data.counthappend;
                            } else {
                                allcount = data.counthappend;
                            }
                            $('.popover-region-notifications .count-container').html(allcount).removeClass('hidden');
                        }, 1000);
                    }
                }.bind(this),
                fail: notification.exception
            }]);
        },
        
        addReminder: function() {
            var text = $('#newremindertext').val();
            var date = $('#newreminderdate').val();
            var time = $('#newremindertime').val();
            $('#addnewreminder .fa-plus').addClass('fa-spin');
            Ajax.call([{
                methodname: 'theme_stardust_add_reminder',
                args: {
                    text: text,
                    date: date,
                    time: time
                },
                done: function (res) {
                    if (res) {
                        var response = JSON.parse(res);
                        var data = response;

                        templates.render(this.TEMPLATE.reminder.src, data)
                        .then(function(html, js) {
                             $('.popover-region-notifications .popover-region-reminder-div .allreminders').append(html);
                        }.bind(this))
                        .fail(notification.exception);
                    }
                    $('#opennewreminder').click();
                }.bind(this),
                fail: notification.exception
            }]);
        },
        
        delReminder: function(target) {
            var reminder = target.parents('.reminder');
            var reminderid = reminder.data('reminderid');
            reminder.find('.fa-close').addClass('fa-spin');
            Ajax.call([{
                methodname: 'theme_stardust_del_reminder',
                args: {
                    reminderid: reminderid
                },
                done: function (response) {
                    if (response == 1) {
                        reminder.slideUp().remove();
                    }
                }.bind(this),
                fail: notification.exception
            }]);
        },
        
        openReminderForm: function() {
            $('.addanewreminder').toggle('fast');
            $('#addnewreminder .fa-plus').removeClass('fa-spin');
            $('#newremindertext').val('');
            $('#newreminderdate').val('');
            $('#newremindertime').val('');
            
        }
    };
});
