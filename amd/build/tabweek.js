define([
  'theme_stardust/ajax',
  'theme_stardust/render'
  ], function (ajax, render) {
  'use strict';

  const tabWeek = document.querySelector('#tabweek');


  const get_timetable_next_week = (firstday) => {
    ajax.data =  {
      method: 'get_timetable_week',
      firstday: firstday,
      direction: `nextweek`
    };
    ajax.runWithRender(tabWeek);
  }

  const get_timetable_prev_week = (firstday) => {
    ajax.data =  {
      method: 'get_timetable_week',
      firstday: firstday,
      direction: `prevweek`
    };
    ajax.runWithRender(tabWeek);
  }

  const get_timetable_current_week = (firstday) => {
    ajax.data =  {
      method: 'get_timetable_week',
      firstday: firstday,
      direction: ``
    };
    ajax.runWithRender(tabWeek);
  }


  const targetContainer = document.querySelector(`.tasks-container`);
  return {
    init: function () {

      if (!targetContainer) return;

      targetContainer.addEventListener('click', function(e){

        let target = e.target;

        while(target != targetContainer) {

          if (target.id === `week-prev`) { // get and show prev week
            let firstday = target.dataset.firstday;
            get_timetable_prev_week(firstday);
            return
          }

          if (target.id === `week-next`) {// get and show next week
            let firstday = target.dataset.firstday;
            get_timetable_next_week(firstday);
            return
          }

          if (target.id === `pills-tab-week`) {// get and show current week
            let firstday = document.getElementById(`week-next`).dataset.firstday;
            // let firstday = target.dataset.firstday;
            get_timetable_current_week(firstday);
            return
          }
          target = target.parentNode;
        }

      });

    }
  }


});
