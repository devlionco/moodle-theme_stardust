define(['jquery'], function ($) {
  'use strict';

  const filter = $(`.filter_wrap`);

  return {
    init: function () {

        $(document).on('click', '.filter_toggle', function(e){

            let target = $(this);//.target;

            if (!filter.hasClass('quiz_all_questions')) { // The first click lead to reload page containing all questions.

                let allquestionspage = filter.data('allquestionspage');
                let buttontarget = target;
                if (buttontarget.hasClass('filter_pin')) {
                    buttontarget = buttontarget.parent();
                }
                if (buttontarget.data('handler') === 'filter_flag' 
                      || buttontarget.data('handler') === 'filter_answered' 
                      || buttontarget.data('handler') === 'filter_notanswered') {
                    allquestionspage += "&filter=" + buttontarget.data('handler');
                }
                window.location = allquestionspage;

            } else {  
                
                let buttontarget = target;
                if (buttontarget.hasClass('filter_pin')) {
                    buttontarget = buttontarget.parent();
                }
                
                $('.que').removeClass('hidden_question');
                if (buttontarget.data('handler') === 'filter_flag') {
                    if (buttontarget.hasClass('filter_active')) {
                        buttontarget.removeClass('filter_active');
                    } else {
                        $.each($('form .que'), function(){
                            var flaggedCollection = $(this).has('.questionflagvalue[value="1"]');
                            if(flaggedCollection.length > 0){
                              $(this).removeClass("hidden_question");
                            } else {
                              $(this).addClass("hidden_question");
                            }
                        });
                        $('.filter_toggle').removeClass('filter_active');
                        buttontarget.addClass('filter_active');
                    }
                }
                
                if (buttontarget.data('handler') === 'filter_answered') {
                    if (buttontarget.hasClass('filter_active')) {
                        buttontarget.removeClass('filter_active');
                    } else {
                        $('form .que:not(.answersaved)').addClass("hidden_question");
                        $('.filter_toggle').removeClass('filter_active');
                        buttontarget.addClass('filter_active');
                    }
                }
                
                
                if (buttontarget.data('handler') === 'filter_notanswered') {
                    if (buttontarget.hasClass('filter_active')) {
                        buttontarget.removeClass('filter_active');
                    } else {
                        $('form .que:not(.notyetanswered)').addClass("hidden_question");
                        $('.filter_toggle').removeClass('filter_active');
                        buttontarget.addClass('filter_active');
                    }
                }
                
                $('.que.hidden_question').fadeOut();
                $('.que:not(.hidden_question)').fadeIn();

                let hiddenquestions = $('.que.hidden_question').length;
                let allquestions = $('.que').length;

                if (allquestions > hiddenquestions) {
                    $('.no_questions_matched_criteria').fadeOut();
                } else {
                    $('.no_questions_matched_criteria').fadeIn();
                }
            }
        });
      
        $.each($('.filter_toggle.filter_preset'), function(){
            this.click();
        });
    }
  }
});
