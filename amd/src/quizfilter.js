define(['jquery'], function ($) {
  'use strict';

  const filter = document.querySelector(`.filter_wrap`);
  // const hideClass = `visuallyhidden`;
  const allQuestions = Array.from(document.querySelectorAll('form .que'));

  const filterFlagItems = () => {

      $('form input[alt="Flagged"]').each(function(item){
        $(item).parents('.que').fadeToggle();
      });

  }

  const filterAnsweredItems = () => {
    $(`form .answersaved`).each(function(item){
      $(item).fadeToggle();
    });
    // allQuestions.forEach((item)=>{
    //   if (item.classList.contains(`answersaved`)) {
    //     $(item).fadeToggle();
    //   }
    // });
  }

  const filterNotAnsweredItems = () => {
    allQuestions.forEach((item)=>{
      if (item.classList.contains(`notyetanswered`)) {
        $(item).fadeToggle();
      }
    });
  }


  return {
    init: function () {

      filter.addEventListener('click', function(e){
        let target = e.target;
        while(target != filter) {

          if (target.dataset.handler === `filter_flag`) {
            // $(target).sibling().removeClass('filter_active');
            target.classList.toggle('filter_active');
            filterFlagItems();
            return
          }

          if (target.dataset.handler === `filter_answered`) {
            // $(target).sibling().removeClass('filter_active');
            target.classList.toggle('filter_active');
            filterAnsweredItems();
            return
          }

          if (target.dataset.handler === `filter_notanswered`) {
            // $(target).sibling().removeClass('filter_active');
            target.classList.toggle('filter_active');
            filterNotAnsweredItems();
            return
          }

          target = target.parentNode;
        }
      });

    }
  }
});
