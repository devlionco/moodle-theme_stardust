define(['jquery'], function ($) {
  'use strict';

  const filter = document.querySelector(`.filter_wrap`);

  return {
    init: function () {

        filter.addEventListener('click', function(e){

            let target = e.target;

            if (!filter.classList.contains('quiz_all_questions')) { // The first click lead to reload page containing all questions.

                let allquestionspage = filter.dataset.allquestionspage;
                let buttontarget = target;
                if (buttontarget.classList.contains('filter_pin')) {
                    buttontarget = buttontarget.parentElement;
                }
                if (buttontarget.dataset.handler === 'filter_flag' 
                      || buttontarget.dataset.handler === 'filter_answered' 
                      || buttontarget.dataset.handler === 'filter_notanswered') {
                    allquestionspage += "&filter=" + buttontarget.dataset.handler;
                }
                window.location = allquestionspage;

            } else {  

                while(target != filter) {

                  if (target.dataset.handler === `filter_flag`) {
                    $.each($('.filter_toggle.filter_active:not(.filter_flag)'), function(){
                      this.click();
                    });
                    if (target.classList.contains('filter_active')) {
                      $.each($('form .que'), function(){
                        $(this).fadeIn();
                      });
                      target.classList.remove('filter_active');
                    }else {
                      $.each($('form .que'), function(){
                        // var flaggedCollection = $(this).has('input[alt="Flagged"]');
                        var flaggedCollection = $(this).has('.questionflagvalue[value="1"]');
                        if(flaggedCollection.length > 0){
                          $(this).fadeIn();
                        } else {
                          $(this).fadeOut();
                        }
                      });
                      target.classList.add('filter_active');
                    }

                    return
                  }

                  if (target.dataset.handler === `filter_answered`) {
                    $.each($('.filter_toggle.filter_active:not(.filter_answered)'), function(){
                      this.click();
                    });
                    if (target.classList.contains('filter_active')) {
                      $.each($('form .que'), function(){
                        $(this).fadeIn();
                      });
                      target.classList.remove('filter_active');
                    }else {
                      $.each($('form .que'), function(){
                        if ($(this).hasClass('answersaved')){
                          $(this).fadeIn();
                        } else {
                          $(this).fadeOut();
                        }
                      });
                      target.classList.add('filter_active');
                    }
                    return
                  }

                  if (target.dataset.handler === `filter_notanswered`) {
                    $.each($('.filter_toggle.filter_active:not(.filter_notanswered)'), function(){
                      this.click();
                    });
                    if (target.classList.contains('filter_active')) {
                      $.each($('form .que'), function(){
                        $(this).fadeIn();
                      });
                      target.classList.remove('filter_active');
                    } else {
                      $.each($('form .que'), function(){
                        if ($(this).hasClass('notyetanswered')){
                          $(this).fadeIn();
                        } else {
                          $(this).fadeOut();
                        }
                      });
                      target.classList.add('filter_active');
                    }
                  }

                  target = target.parentNode;
                }
            }
        });
      
        $.each($('.filter_toggle.filter_preset'), function(){
            this.click();
        });
    }
  }
});
