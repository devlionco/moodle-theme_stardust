define(['jquery', 'jqueryui', 'core/str'], function($, jqui, str) {

  str.get_strings([

    {key: 'isrequired', component: 'theme_stardust'},
    {key: 'emailerror', component: 'theme_stardust'},
    {key: 'phoneerror', component: 'theme_stardust'},
    {key: 'teudatzeutnotnumerical', component: 'theme_stardust'},
    {key: 'teudatzeutwrong', component: 'theme_stardust'},
    {key: 'editionrestricted', component: 'theme_stardust'}

  ]).done(function(){});

  return {
    init: function(jscontext) {

      // get data from DB
      jscontext = typeof jscontext !== 'undefined' ? jscontext : 0;
      var dbdata = JSON.parse(jscontext);
      var restrictions = dbdata.locked;
      var allowonce = dbdata.unlockedifempty;
      // console.log(jscontext);

      // query spans and add events to check restrictions: if not restricted replace it with input
      var spanQuery = document.querySelectorAll('span.input');
      for (var i=0; i<spanQuery.length;i++){
        var item = spanQuery[i];
        item.addEventListener('click', function(e){
          // TODO: think about unlockedifempty
          if (restrictions.indexOf(e.target.id) > -1){
            $('#'+e.target.id).parent().removeClass('info').addClass('warning');
            if ($('#'+e.target.id+' ~ .input-label .warning').length > 0){
              $('#'+e.target.id+' ~ .input-label .warning').text(' ');
            } else {
              $('#'+e.target.id).next().append('<span class="warning"></span>');
            }
            $('#'+e.target.id+' ~ .input-label .warning').html(M.util.get_string('editionrestricted', 'theme_stardust'));
            $(this).unbind('click');
          } else {
            var target = document.querySelector('#'+e.target.id+'');
            if (target.nodeName === "SPAN"){
              var inputNode = document.createElement('input');
              inputNode.classList += "input";
              inputNode.type = 'text';
              inputNode.id = target.id;
              inputNode.dataset.edited = false;
              if (target.dataset.placeholder){
                inputNode.placeholder = target.dataset.placeholder;
              }
              inputNode.setAttribute('value', target.innerHTML);
              target.parentNode.insertBefore( inputNode , target.nextSibling);
              target.remove();
              switch(inputNode.id) {
                case 'knowledge':
                  inputNode.addEventListener('change', function(e){
                    // $('#mypublicpage-profile-shortform #knowledge-container').append('<div class = "knowledge-item" style = "background-color:#'+ (Math.random()*0xFFFFFF<<0).toString(16) +';" onClick = this.remove();>'+e.target.value+'</div>');
                    $('#mypublicpage-profile-shortform #knowledge-container').append('<div class="knowledge-item" style="background-color:rgb('+ Math.round(Math.random()*255) + ',' + Math.round(Math.random()*255) + ',' + Math.round(Math.random()*255) + ');" onClick = this.remove();>'+e.target.value+'</div>');
                    e.target.value = "";
                    e.target.dataset.edited = true;
                  });
                  break;
                case 'interests':
                  inputNode.addEventListener('change', function(e){
                    // $('#mypublicpage-profile-shortform #tag-container').append('<div class = "tag-item" style = "background-color:#'+ (Math.random()*0xFFFFFF<<0).toString(16) +';" onClick = this.remove();>'+e.target.value+'</div>');
                    $('#mypublicpage-profile-shortform #tag-container').append('<div class="tag-item" style="background-color:rgb(' + Math.round(Math.random()*255) + ',' + Math.round(Math.random()*255) + ',' + Math.round(Math.random()*255) + ');" onClick = this.remove();>'+e.target.value+'</div>');
                    e.target.value = "";
                    e.target.dataset.edited = true;
                  });
                  break;
                default:
                  inputNode.addEventListener('change', function(e){
                    //check unlockedifempty
                    if (restrictions.indexOf(e.target.id) > -1){
                      e.target.setAttribute('disabled');
                    }
                    e.target.dataset.edited = true;
                  });
              }
            }
          }
        });
      }

      $('#mypublicprofile-save-btn').click(function() {
        // get all fields from DB
        var userid = dbdata.id,
            username = dbdata.username,
            idnumber = dbdata.idnumber,
            fullname = dbdata.firstname +' '+ dbdata.lastname,
            email = dbdata.email,
            // email2 = dbdata.email2,
            aim = dbdata.aim,
            phone1 = dbdata.phone1,
            phone2 = dbdata.phone2,
            address = dbdata.address,
            skype = dbdata.skype,
            knowledge = dbdata.knowledge,
            birthday = dbdata.birthday,
            interests = dbdata.interests,
            institution = dbdata.institution,
            yahoo = dbdata.yahoo,
            errors = false;

        // hide additional fields
        if (email === ''){ $('input#email2').parent().addClass('d-none') }
        if (phone1 === ''){ $('input#phone2').parent().addClass('d-none') }

        // query only changed fields and validate them
        var queryInputs = document.querySelectorAll('#mypublicpage-profile-shortform input.input[data-edited="true"]');

        var changes = new Object();
        for (var i=0; i<queryInputs.length; i++){
          var item = queryInputs[i];
          switch (item.id){
            case "username":
              item.value = $.trim($('#username').val());
              break;
            case "fullname":
              item.value  = $.trim($('#fullname').val());
              break;
            case "idnumber":
              // check passport number
              item.value = $.trim(item.value);
              if (item.value === '') {
                $('#idnumber').parent().removeClass('info').addClass('danger');
                if ($('#idnumber ~ .input-label .error').length > 0){
                  $('#idnumber ~ .input-label  .error').text(' ');
                } else {
                  $('#idnumber').next().append('<span class="error"></span>');
                }
                $('#idnumber  ~ .input-label .error').html(M.util.get_string('isrequired', 'theme_stardust'));
                $('#idnumber').focus();
                errors = true;
              }

              if (isNaN(item.value)) {
                $('#idnumber').parent().removeClass('info').addClass('danger');
                if ($('#idnumber ~ .input-label .error').length > 0){
                  $('#idnumber ~ .input-label  .error').text(' ');
                } else {
                  $('#idnumber').next().append('<span class="error"></span>');
                }
                $('#idnumber  ~ .input-label .error').html(M.util.get_string('teudatzeutnotnumerical', 'theme_stardust'));
                $('#idnumber').focus();
                errors = true;
              }

              if (item.value.length  != 9) {
                $('#idnumber').parent().removeClass('info').addClass('danger');
                if ($('#idnumber ~ .input-label .error').length > 0){
                  $('#idnumber ~ .input-label  .error').text(' ');
                } else {
                  $('#idnumber').next().append('<span class="error"></span>');
                }
                $('#idnumber  ~ .input-label .error').html(M.util.get_string('teudatzeutwrong', 'theme_stardust'));
                $('#idnumber').focus();
                errors = true;
              }
              break;
            case "email":
              item.value = $.trim(item.value);
              // check required
              if (item.value === '') {
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
              }
            // case "email2":
            case "aim":  // additional_email stores here
              // if additional_email is empty
              if (item.id === 'aim' && item.value === ''){
                break;
              }
              // Validate email text
              var regEx = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
              if (!regEx.test(item.value)) {
                $('#'+item.id).parent().removeClass('info').addClass('danger');
                $('#'+item.id).attr('placeholder', 'mailbox@domain.zone');
                if ($('#'+item.id+' ~ .input-label .error').length > 0){
                  $('#'+item.id+' ~ .input-label .error').text(' ');
                } else {
                  $('#'+item.id).next().append('<span class="error"></span>');
                }
                $('#'+item.id+' ~ .input-label .error').html(M.util.get_string('emailerror', 'theme_stardust'));
                $('#'+item.id).focus();
                errors = true;
              }
              break;
            case "phone1":
              // reqiured not empty
              if ($.trim(item.value) === '') {
                $('#'+item.id).parent().removeClass('info').addClass('danger');
                $('#'+item.id).attr('placeholder', '123-456-7890');
                if ($('#'+item.id+' ~ .input-label .error').length > 0){
                  $('#'+item.id+' ~ .input-label  .error').text(' ');
                } else {
                  $('#'+item.id).next().append('<span class="error"></span>');
                }
                $('#'+item.id+'  ~ .input-label .error').html(M.util.get_string('isrequired', 'theme_stardust'));
                $('#'+item.id).focus();
                errors = true;
              }
            case "phone2":
              // not required, but also might be in format like 123-123-1234 or (123) 123 1234 or 123.123.1234
              var regEx = /^[(]{0,1}[0-9]{3}[)]{0,1}[-\s\.]{0,1}[0-9]{3}[-\s\.]{0,1}[0-9]{4}$/;
              if (!regEx.test(item.value)) {
                $('#'+item.id).parent().removeClass('info').addClass('danger');
                $('#'+item.id).attr('placeholder', 'xxx-xxx-xxxx');
                if ($('#'+item.id+' ~ .input-label .error').length > 0){
                  $('#'+item.id+' ~ .input-label  .error').text(' ');
                } else {
                  $('#'+item.id).next().append('<span class="error"></span>');
                }
                $('#'+item.id+' ~ .input-label .error').html(M.util.get_string('phoneerror', 'theme_stardust'));
                $('#'+item.id).focus();
                errors = true;
              }
              break;
            case "skype":
              item.value = $.trim(item.value);
              break;
            case "knowledge":
              var knowledge = [];
              var knowledgeElements = $('#knowledge-container .knowledge-item');
              if (knowledgeElements.length) {
                $.map(knowledgeElements, function(elem){
                  knowledge.push($.trim($(elem).text()));
                });
                item.value = JSON.stringify(knowledge);
              }
            case "birthday":
              birthday = $.trim($('#birthday').val());
              break;
            case "interests":
              var interests = [];
              var interestsElements = $('#tag-container .tag-item');
              if (interestsElements.length) {
                $.map(interestsElements, function(item){
                  interests.push($.trim($(item).text()));
                });
                item.value = JSON.stringify(interests);
              }
              break;
            case "institution":
              item.value = $.trim(item.value);
              break;
            case "yahoo":
              item.value = $.trim(item.value);
              break;
          }
          changes[item.id] = item.value;
        }

        if ($.isEmptyObject(changes)){
          return;
        } else {
          changes['userid'] = userid;
          changes['action'] = 'mypublicpage-save-shortform';
        }
        // remove values from knowledge and interests intputs
        document.getElementById('knowledge').value = '';
        document.getElementById('interests').value = '';

        // console.log(JSON.stringify(changes));
        // check all errors
        if (errors){
          return;
        }

        // animation for ajax
        function animateAjax (success){
          var spinner = '<div id="sendajax"><span class="spinner"></span></div>';
          var check = `<svg class="ajax-sent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2"><polyline class="path check" fill="none" stroke="#082567" stroke-width="15" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "/></svg>`;
          $('#mypublicpage-profile-shortform').append(spinner);
          if (success){
            setTimeout( function(){
              document.getElementById('sendajax').innerHTML = check;
            }, 2000);
          }
          setTimeout(function(){
            document.getElementById('sendajax').remove()
          }, 3000);
        }



        $.ajax({
          type: "POST",
          async: true,
          url: M.cfg.wwwroot + '/theme/stardust/mypublic-ajax.php',
          data:changes,

          success: function(data) {
            animateAjax (true);
            // alert(data);

          },
          error: function(requestObject, error, errorThrown) {
            animateAjax ();
            // alert(error);
          }
        });
      });
    }
  };
});
