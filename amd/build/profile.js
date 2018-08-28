/* jshint ignore:start */
define(['jquery', 'jqueryui', 'core/str'], function($, jqui, str) {

                str.get_strings([

                    {key: 'enterfirstname', component: 'theme_remui'},
                    {key: 'enterlastname', component: 'theme_remui'},
                    {key: 'enteremailid', component: 'theme_remui'},
                    {key: 'enterfirstname', component: 'theme_remui'},
                    {key: 'enterproperemailid', component: 'theme_remui'},
                    {key: 'detailssavedsuccessfully', component: 'theme_remui'},
                    {key: 'actioncouldnotbeperformed', component: 'theme_remui'},
                    {key: 'saving', component: 'theme_remui'},
                    {key: 'enterfullname', component: 'theme_remui'},
                    {key: 'enterusername', component: 'theme_remui'},
                    {key: 'enterteudatzeut', component: 'theme_remui'},
                    {key: 'teudatzeutnotnumerical', component: 'theme_remui'},
                    {key: 'teudatzeutwrong', component: 'theme_remui'},
                    {key: 'enterphone', component: 'theme_remui'},
                    {key: 'phonenotnumerical', component: 'theme_remui'},
                    {key: 'wrongphone', component: 'theme_remui'},
                    {key: 'enteremail', component: 'theme_remui'},
                    {key: 'enterproperemail', component: 'theme_remui'},
                    {key: 'detailssavedsuccessfullycustom', component: 'theme_remui'},
                    {key: 'actioncouldnotbeperformed', component: 'theme_remui'},
                    {key: 'wrongpassword', component: 'theme_remui'}

                ]).done(function(){});

            $('#editprofile .form-horizontal #btn-save-profile').click(function() {
                $('div#error-message').show();
                $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                $('div#error-message p').html("Saving...");
                var fname = $.trim($('#first_name').val());
                var lname = $.trim($('#surname').val());
                var emailid = $('#standard_email').val();
                var description = $.trim($('#description').val());
                var city = $.trim($('#city').val());
                var country = $('#editprofile .form-horizontal #country option:selected').val();
                // console.log(fname+lname+emailid+description+city+country);
                // return false;
                if (fname === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterfirstname', 'theme_remui'));
                    $('#first_name').focus();
                    return false;
                }
                if (lname === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterlastname', 'theme_remui'));
                    $('#surname').focus();
                    return false;
                }
                if (emailid === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enteremailid', 'theme_remui'));
                    $('#standard_email').focus();
                    return false;
                }
                // Validate email text
                var regEx = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
                if (!regEx.test(emailid)) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterproperemailid', 'theme_remui'));
                    $('#inputEmail').focus();
                    return false;
                }
                emailid = encodeURIComponent(emailid);
                /*if (country === M.util.get_string('selectcountry', 'theme_remui')) {
                    countryname = '';
                    country = '';
                }*/
                $.ajax({
                    type: "GET",
                    async: true,
                    url: M.cfg.wwwroot + '/theme/remui/request_handler.php?action=save_user_profile_settings&fname=' + fname + '&lname=' + lname + '&emailid=' + emailid + '&description=' + description + '&city=' + city + '&country=' + country,
                    success: function(data) {
                        // alert("Saved"+data);
                        $('div#error-message').show();
                        $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                        $('div#error-message p').html(M.util.get_string('detailssavedsuccessfully', 'theme_remui'));
                        $('.profile-user').text(fname + " " + lname);
                        $('.usermenu a.navbar-avatar span.username').text((fname + " " + lname));
                            $('#user-description').text( description);
                    },
                     error: function(requestObject, error, errorThrown) {
                        /*alert(error);
                        alert(errorThrown);*/
                        $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                        $('div#error-message p').html(error + ' : ' + errorThrown + ', '+ M.util.get_string('actioncouldnotbeperformed', 'theme_remui'));
                    }
                });
            });

            //Save custom editprofile
            $('#editprofilecustom #btn-save-profile').click(function() {
                $('div#error-message').show();
                $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                $('div#error-message p').html(M.util.get_string('saving', 'theme_remui'));

                var fullname = $.trim($('#fullname').val());
                var email = $.trim($('#email').val());
                var teudatzeut = $.trim($('#teudatzeut').val());
                var phone = $.trim($('#phone').val());
                var userid = $.trim($('#userid').val());
                // var username = $.trim($('#username').val());
                // var password = $('#password').val();
                var username = '';
                var password = '';

                // return false;
                if (fullname === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterfullname', 'theme_remui'));
                    $('#fullname').focus();
                    return false;
                }

                if (teudatzeut === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterteudatzeut', 'theme_remui'));
                    $('#teudatzeut').focus();
                    return false;
                }

                if (isNaN(teudatzeut)) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('teudatzeutnotnumerical', 'theme_remui'));
                    $('#teudatzeut').focus();
                    return false;
                }

                if (teudatzeut.length  != 9) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('teudatzeutwrong', 'theme_remui'));
                    $('#teudatzeut').focus();
                    return false;
                }

                if (phone === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterphone', 'theme_remui'));
                    $('#phone').focus();
                    return false;
                }

                if (isNaN(phone)) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('phonenotnumerical', 'theme_remui'));
                    $('#phone').focus();
                    return false;
                }

                if (phone.length  != 9 && phone.length  != 10) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('wrongphone', 'theme_remui'));
                    $('#phone').focus();
                    return false;
                }

                if (email === '') {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enteremail', 'theme_remui'));
                    $('#email').focus();
                    return false;
                }
                // Validate email text
                var regEx = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
                if (!regEx.test(email)) {
                    $('div#error-message').show();
                    $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                    $('div#error-message p').html(M.util.get_string('enterproperemail', 'theme_remui'));
                    $('#email').focus();
                    return false;
                }
                email = encodeURIComponent(email);

                // if (password !== '' && password.length < 8) {
                //     $('div#error-message').show();
                //     $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                //     $('div#error-message p').html(M.util.get_string('wrongpassword', 'theme_remui'));
                //     $('#password').focus();
                //     return false;
                // }

                $.ajax({
                    type: "POST",
                    async: true,
                    url: M.cfg.wwwroot + '/theme/remui/request_handler.php?action=save_user_profile_custom_settings',
                    data: {fullname:fullname, username:username, password:password, email:email, teudatzeut:teudatzeut, phone:phone, userid:userid},
                    success: function(data) {
                        // alert("Saved"+data);
                        $('div#error-message').show();
                        $('div#error-message').removeClass('alert-danger').addClass('alert-success');
                        $('div#error-message p').html(M.util.get_string('detailssavedsuccessfullycustom', 'theme_remui'));
                        //$('.profile-user').text(fname + " " + lname);
                        //$('.usermenu a.navbar-avatar span.username').text((fname + " " + lname));
                        //$('#user-description').text( description);
                    },
                    error: function(requestObject, error, errorThrown) {
                        /*alert(error);
                        alert(errorThrown);*/
                        $('div#error-message').removeClass('alert-success').addClass('alert-danger');
                        $('div#error-message p').html(error + ' : ' + errorThrown + ', '+ M.util.get_string('actioncouldnotbeperformed', 'theme_remui'));
                    }
                });
            });

    require(['theme_remui/jquery-asPieProgress'], function (pieprogress) {
        // initiliaze coruse progress in profile
        jQuery('.remui-course-progress').asPieProgress({
            namespace: 'asPieProgress'
        });
    });

});
/* jshint ignore:end */
