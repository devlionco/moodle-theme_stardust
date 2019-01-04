define(['jquery', 'theme_stardust/ajax'], function($, ajax){

  const message = document.querySelector('#course-main-content .message-input');
  const submit = document.querySelector('#course-main-content .message-submit');
  const hider = document.querySelector('#course-main-content .message-hider');

  // on send spinner
  const spinner = '<span class="message-spin"></span>';
  // on send success
  const check = '<svg class="message-sent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2"><polyline class="path check" fill="none" stroke="#83D3AE" stroke-width="15" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "/></svg>';

  const getCourseId = function (){
    var queries = window.location.search.replace(/^\?/, '').split('&');
    for( i = 0; i < queries.length; i++ ) {
      var q = queries[i].trim();
      if (q.indexOf('id') == 0){
        var courseid = q.substring(3, q.length);
      }
    }
    return courseid;
  };

  const targetBlock = document.querySelector('.submit-icons');

  const animation = function() {
    targetBlock.innerHTML = spinner;
    setTimeout(function(){
      targetBlock.innerHTML = check;
    },1500);
  };

  $('form.message').submit(function(e){
    return false;
  });


  return {
    init: function () {
      hider.addEventListener('click', function(){
        if(hider.classList.contains('show')){
          var hiderstatus = 0;
          $('.messages-container').removeClass('show');
          $(hider).removeClass('show');
        } else {
          var hiderstatus = 1;
          $('.messages-container').addClass('show');
          $(hider).addClass('show');
        }
        ajax.data = {
          method: "toggle_course_message_status",
          url: "{{config.wwwroot}}/theme/stardust/classes/classAjax.php",
          user: document.querySelector('form.message').dataset.userid,
          courseid: getCourseId(),
          sesskey: M.cfg.sesskey,
          status: hiderstatus
        };
        ajax.send();
      });

      message.addEventListener('focus', function(e){
        e.preventDefault();
        message.removeAttribute('readonly');
      }, true);
      message.addEventListener('blur', function(e) {
        submit.click();
      }, true);

      message.addEventListener('keyup', function(e) {
        e.preventDefault();
        // Enter
        if (event.keyCode === 13) {
          submit.click();
        } else if (event.keyCode === 8) {
          $(submit).css({'display':'inline-block'});
        } else {
          $(submit).css({'display':'inline-block'});
        }
      });
      submit.addEventListener('click', function(){

        ajax.data = {
          method: "send_course_message",
          url: "{{config.wwwroot}}/theme/stardust/classes/classAjax.php",
          user: document.querySelector('form.message').dataset.userid,
          courseid: getCourseId(),
          sesskey: M.cfg.sesskey,
          message: message.value
        };
        animation();
        ajax.send();
      });
    }
  }

});
