define(['jquery', 'jqueryui', 'core/str'], function($, jqui, str) {

  str.get_strings([

      // {key: 'enterfirstname', component: 'theme_remui'},
      // {key: 'enterlastname', component: 'theme_remui'},
      // {key: 'enteremailid', component: 'theme_remui'},
      // {key: 'enterfirstname', component: 'theme_remui'},
      // {key: 'enterproperemailid', component: 'theme_remui'},
      // {key: 'detailssavedsuccessfully', component: 'theme_remui'},
      // {key: 'actioncouldnotbeperformed', component: 'theme_remui'},
      // {key: 'saving', component: 'theme_remui'},
      // {key: 'enterfullname', component: 'theme_remui'},
      // {key: 'enterusername', component: 'theme_remui'},
      // {key: 'enterteudatzeut', component: 'theme_remui'},
      // {key: 'teudatzeutnotnumerical', component: 'theme_remui'},
      // {key: 'teudatzeutwrong', component: 'theme_remui'},
      // {key: 'enterphone', component: 'theme_remui'},
      // {key: 'phonenotnumerical', component: 'theme_remui'},
      // {key: 'wrongphone', component: 'theme_remui'},
      // {key: 'enteremail', component: 'theme_remui'},
      // {key: 'enterproperemail', component: 'theme_remui'},
      // {key: 'detailssavedsuccessfullycustom', component: 'theme_remui'},
      // {key: 'actioncouldnotbeperformed', component: 'theme_remui'},
      // {key: 'wrongpassword', component: 'theme_remui'}

  ]).done(function(){});


  return {
    init: function() {

      //Process form at mypublicpage
      $('#mypublicpage-profile-shortform #mypublicprofile-save-btn').click(function() {
          var userid = $.trim($('#userid').val());
          var username = $.trim($('#username').val());
          // var password = $.trim($('#password').val());
          var fullname = $.trim($('#fullname').val());
          var phone1 = $.trim($('#phone1').val());
          var institution = $.trim($('#institution').val());
          var address = $.trim($('#address').val());
          var icq = $.trim($('#icq').val());
          var birthday = $('#birthday').val();
          var interests = [];
          var interestsElements = $('#tag-container .tag-item');
          if (interestsElements.length) {
            $.map(interestsElements, function(item){
              interests.push($(item).text());
            });
            interests = JSON.stringify(interests);
          }
          $.ajax({
              type: "POST",
              async: true,
              url: M.cfg.wwwroot + '/theme/stardust/mypublic-ajax.php',
              data: {
                userid:userid,
                username:username,
                // password:password,
                fullname:fullname,
                phone1:phone1,
                institution:institution,
                address:address,
                icq:icq,
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
                  // alert(error);
                  // alert(errorThrown);
                  // $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                  // $('div#error-message p').html(error + ' : ' + errorThrown + ', '+ M.util.get_string('actioncouldnotbeperformed', 'theme_remui'));
              }
          });

      }); // end processing form

    }
  };

});
