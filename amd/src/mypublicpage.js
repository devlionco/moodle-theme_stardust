define(['jquery', 'jqueryui', 'core/str'], function($, jqui, str) {

  str.get_strings([

    {key: 'isrequired', component: 'theme_stardust'},
    {key: 'emailerror', component: 'theme_stardust'},
    {key: 'phoneerror', component: 'theme_stardust'}
      // {key: 'username', component: 'theme_stardust'},
      // {key: 'password', component: 'theme_stardust'},
      // {key: 'lastname_firstname', component: 'theme_stardust'},
      // {key: 'passport', component: 'theme_stardust'},
      // {key: 'email', component: 'theme_stardust'},
      // {key: 'additional_email', component: 'theme_stardust'},
      // {key: 'actioncouldnotbeperformed', component: 'theme_stardust'},
      // {key: 'saving', component: 'theme_stardust'},
      // {key: 'enterfullname', component: 'theme_stardust'},
      // {key: 'enterusername', component: 'theme_stardust'},
      // {key: 'enterteudatzeut', component: 'theme_stardust'},
      // {key: 'teudatzeutnotnumerical', component: 'theme_stardust'},
      // {key: 'teudatzeutwrong', component: 'theme_stardust'},
      // {key: 'enterphone', component: 'theme_stardust'},
      // {key: 'phonenotnumerical', component: 'theme_stardust'},
      // {key: 'wrongphone', component: 'theme_stardust'},
      // {key: 'enteremail', component: 'theme_stardust'},
      // {key: 'enterproperemail', component: 'theme_stardust'},
      // {key: 'detailssavedsuccessfullycustom', component: 'theme_stardust'},
      // {key: 'actioncouldnotbeperformed', component: 'theme_stardust'},
      // {key: 'wrongpassword', component: 'theme_stardust'}

  ]).done(function(){});

  return {
    init: function() {
      //Process form at mypublicpage
      $('#mypublicprofile-save-btn').click(function() {
          var userid = $.trim($('#userid').val());
          var username = $.trim($('#username').val());
          // var password = $.trim($('#password').val());
          var passport = $.trim($('#passport').val());
          var fullname = $.trim($('#fullname').val());
          var email = $.trim($('#email').val());
          var aim = $.trim($('#aim').val());
          var phone1 = $.trim($('#phone1').val());
          var phone2 = $.trim($('#phone2').val());
          var institution = $.trim($('#institution').val());
          var address = $.trim($('#address').val());
          var skype = $.trim($('#skype').val());
          var icq = $.trim($('#icq').val());
          var yahoo = $.trim($('#yahoo').val());
          var birthday = $('#birthday').val();
          var interests = [];
          var interestsElements = $('#tag-container .tag-item');
          var errors = false;

          // hide additional fields
          if (email === ''){ $('#aim').parent().addClass('d-none') }
          if (phone1 === ''){ $('#phone2').parent().addClass('d-none') }

          // check required
          if (email === '') {
            $('#email').parent().removeClass('info').addClass('danger');
            $('#email').attr('placeholder', 'mailbox@domain.zone');
            if ($('#email ~ .input-label .error').length > 0){
              $('#email ~ .input-label .error').text(' ');
            } else {
              $('#email').next().append('<span class="error"></span>');
            }
            $('#email ~ .input-label .error').append( M.util.get_string('isrequired', 'theme_stardust') );
            $('#email').focus();
            errors = true;
          } else {
            errors = false;
          }
          // Validate email text
          var regEx = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
          if (!regEx.test(email)) {
            $('#email').parent().removeClass('info').addClass('danger');
            $('#email').attr('placeholder', 'mailbox@domain.zone');
            if ($('#email ~ .input-label .error').length > 0){
              $('#email ~ .input-label .error').text(' ');
            } else {
              $('#email').next().append('<span class="error"></span>');
            }
            $('#email ~ .input-label .error').html(M.util.get_string('emailerror', 'theme_stardust'));
            $('#email').focus();
            errors = true;
          } else {
            errors = false;
          }

          if (phone1 === '') {
            $('#phone1').parent().removeClass('info').addClass('danger');
            $('#phone1').attr('placeholder', '123-456-7890');
            if ($('#phone1 ~ .input-label .error').length > 0){
              $('#phone1 ~ .input-label  .error').text(' ');
            } else {
              $('#phone1').next().append('<span class="error"></span>');
            }
            $('#phone1  ~ .input-label .error').html(M.util.get_string('isrequired', 'theme_stardust'));
            $('#phone1').focus();
            errors = true;
          } else {
            errors = false;
          }

          var regEx = /^[(]{0,1}[0-9]{3}[)]{0,1}[-\s\.]{0,1}[0-9]{3}[-\s\.]{0,1}[0-9]{4}$/; // formats like 123-123-1234 or (123) 123 1234 or 123.123.1234
          if (!regEx.test(phone1)) {
            $('#phone1').parent().removeClass('info').addClass('danger');
            $('#phone1').attr('placeholder', 'xxx-xxx-xxxx');
            if ($('#phone1 ~ .input-label .error').length > 0){
              $('#phone1 ~ .input-label  .error').text(' ');
            } else {
              $('#phone1').next().append('<span class="error"></span>');
            }
            $('#phone1 ~ .input-label .error').html(M.util.get_string('phoneerror', 'theme_stardust'));
            $('#phone1').focus();
            errors = true;
          } else {
            errors = false;
          }
          if (interestsElements.length) {
            $.map(interestsElements, function(item){
              interests.push($(item).text());
            });
            interests = JSON.stringify(interests);
          }

          // check all errors
          if (errors){
            return;
          }
          $.ajax({
              type: "POST",
              async: true,
              url: M.cfg.wwwroot + '/theme/stardust/mypublic-ajax.php',
              data: {
                userid:userid,
                username:username,
                passport:passport,
                // password:password,
                fullname:fullname,
                email:email,
                aim:aim,
                phone1:phone1,
                phone2:phone2,
                skype:skype,
                institution:institution,
                // address:address,
                icq:icq,
                yahoo:yahoo,
                birthday:birthday,
                interests:interests,
                action:'mypublicpage-save-shortform'
              },
              success: function(data) {
                  alert("Profile is updated");
                  // $('div#error-message').show();
                  // $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                  // $('div#error-message p').html(M.util.get_string('detailssavedsuccessfullycustom', 'theme_remui'));
                  //$('.profile-user').text(fname + " " + lname);
                  //$('.usermenu a.navbar-avatar span.username').text((fname + " " + lname));
                  //$('#user-description').text( description);
              },
              error: function(requestObject, error, errorThrown) {
                  alert(error);
                  // alert(errorThrown);
                  // $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                  // $('div#error-message p').html(error + ' : ' + errorThrown + ', '+ M.util.get_string('actioncouldnotbeperformed', 'theme_remui'));
              }
          });

      }); // end processing form

    }
  };

});
