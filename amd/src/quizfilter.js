define(['jquery'], function ($) {
  'use strict';

  const filter = document.querySelector(`.filter_wrap`);

  return {
    init: function () {

      filter.addEventListener('click', function(e){
        let target = e.target;
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
                var flaggedCollection = $(this).has('input[alt="Flagged"]');
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
      });

    }
  }
});

// define(['jquery'], function ($) {
//   'use strict';
//
//   const filter = document.querySelector(`.filter_wrap`);
//
//
//
//   return {
//     init: function () {
//
//       filter.addEventListener('click', function(e){
//
//         let target = e.target;
//         while(target != filter) {
//
//           if (target.dataset.handler === `filter_flag`) {
//             $.each($('.filter_toggle.filter_active:not(.filter_flag)'), function(){
//               this.click();
//             });
//
//             if (target.classList.contains('filter_active')) {
//               $.each($('form input[alt="Flagged"]'), function(key, value){
//                 $(value).parents('.que').fadeOut();
//               });
//               target.classList.remove('filter_active');
//             }else {
//               $.each($('form input[alt="Flagged"]'), function(key, value){
//                 $(value).parents('.que').fadeIn();
//               });
//               target.classList.add('filter_active');
//             }
//
//             return
//           }
//
//           if (target.dataset.handler === `filter_answered`) {
//             $.each($('.filter_toggle.filter_active:not(.filter_answered)'), function(){
//               this.click();
//             });
//             if (target.classList.contains('filter_active')) {
//               $.each($('form .answersaved'), function(key, value){
//                 $(value).fadeOut();
//               });
//               target.classList.remove('filter_active');
//             }else {
//               $.each($('form .answersaved'), function(key, value){
//                 $(value).fadeIn();
//               });
//               target.classList.add('filter_active');
//             }
//             return
//           }
//
//           if (target.dataset.handler === `filter_notanswered`) {
//             $.each($('.filter_toggle.filter_active:not(.filter_notanswered)'), function(){
//               this.click();
//             });
//             if (target.classList.contains('filter_active')) {
//               $.each($('form .notyetanswered'), function(key, value){
//                 $(value).fadeOut();
//               });
//               target.classList.remove('filter_active');
//             }else {
//               $.each($('form .notyetanswered'), function(key, value){
//                 $(value).fadeIn();
//               });
//               target.classList.add('filter_active');
//             }
//           }
//
//           target = target.parentNode;
//         }
//       });
//
//     }
//   }
// });
