/* jshint ignore:start */
define(['jquery', 'core/ajax', 'core/log', 'core/str'], function($, Ajax, log, Str) {

  // Debugger;
  log.debug('Davidson Stardust AMD load src/helpmenu');

  var SELECTORS = {
    btnHelp: '#btn-help',
    btnClose: '#btn-close',
    btnShowTeachers: '#teacherList',
    mailBody: '#mailBody'
  };

  /**
   * Error handler for requests.
   *
   * @method handleEsc
   * @param {event} e
   */
  function handleEsc(e) {
      if (e.keyCode == 27) {
          $('#help-menu .menu').fadeToggle('fast');
          $(document).off('keyup', handleEsc);
      }
  }

  /**
 * Error handler for requests.
 *
 * @method handleError
 */
  function handleError() {
      $(SELECTORS.mailBody).attr("disabled", true);
      $(SELECTORS.mailBody).addClass('error');
      Str.get_string('error_sending_message', 'theme_stardust')
        .done(function(string) {
          $(SELECTORS.mailBody).val(string);
      });
  }

  /**
 * Init State for the mail body node element.
 *
 * @method initState
 */
  function initState() {
      $(SELECTORS.mailBody).attr("disabled", false);
      $(SELECTORS.mailBody).val('');
      $(SELECTORS.mailBody).removeClass('error');
  }

  $(SELECTORS.btnHelp + ',' + SELECTORS.btnClose).on('click', function() {
      $('#help-menu .menu').fadeToggle('fast');
      $(document).on('keyup', handleEsc);
  });

  $(SELECTORS.btnShowTeachers).on('click', function() {
    $(this).next().slideToggle();
  });

  // Open popup for sending window
  $('#sendMail').on('show.bs.modal', function(e) {
      var target = e.relatedTarget;
      $(this).removeClass('showtechsupport');
      if ($(target).data('ref') === 'techsupport') {
        $(this).addClass('showtechsupport');
      }
      initState();
      $('#teacherName').text($(target).attr("data-name"));
      Str.get_string('send', 'theme_stardust')
        .done(function(string) {
          $('#sendMailToTeacher').attr({
            "data-userid": $(target).attr("data-userid") || 0,
            "data-courseid": $(target).attr("data-courseid") || 0
          }).html(string).attr("disabled", false);
      });
  });

  $('#sendMailToTeacher').on('click', function() {
      var text = $(SELECTORS.mailBody).val();
      var userid = $('#sendMailToTeacher').attr("data-userid");
      var courseid = $('#sendMailToTeacher').attr("data-courseid");

      Str.get_string('sending', 'theme_stardust')
        .done(function(string) {
          $('#sendMailToTeacher').html(string).attr("disabled", true);
      });

      Ajax.call([{
          methodname: 'theme_stardust_send_mail_to_teacher',
          args: {text: text, userid: userid, courseid: courseid},
          done: function(response) {
              if (response === '1') {
                  Str.get_string('sent', 'theme_stardust')
                    .done(function(string) {
                      $('#sendMailToTeacher').html(string);
                  });
                  setTimeout(function() {
                    $('[data-dismiss="modal"]').trigger('click');
                  }, 3000);
              } else {
                  handleError();
              }
          },
          fail: handleError
      }]);
  });
});
/* jshint ignore:end */
