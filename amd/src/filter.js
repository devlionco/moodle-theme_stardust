define(['jquery'], function ($) {
  'use strict';

  // DOM nodes
  const tabtoDay = document.querySelector('#tabtoday');
  const tabs = tabtoDay.querySelector('thead');
  const tableBody = tabtoDay.querySelector('tbody');
  const mods = Array.from(tabtoday.querySelectorAll('tbody tr'));

  //function
  const setSortedClass = (target) => {
    if (target.classList.contains(`sorted`)) {
      let storedItems = Array.from(tabs.querySelectorAll(`.sorted`));
      storedItems.forEach((item)=>{
        item.classList.remove(`sorted`);
      });
    }else {
      let storedItems = Array.from(tabs.querySelectorAll(`.sorted`));
      storedItems.forEach((item)=>{
        item.classList.remove(`sorted`);
      });
      target.classList.add(`sorted`);
    }
  }

  const compareByType = (a, b) => {
    return (
      a.dataset.type > b.dataset.type ?  1 :
      a.dataset.type < b.dataset.type ? -1 :
      0
    )
  }

  const compareByName = (a, b) => {
    return (
      a.dataset.modname > b.dataset.modname ?  1 :
      a.dataset.modname < b.dataset.modname ? -1 :
      0
    )
  }

  const compareByCourseName = (a, b) => {
    return (
      a.dataset.coursename > b.dataset.coursename ?  1 :
      a.dataset.coursename < b.dataset.coursename ? -1 :
      0
    )
  }

  const compareNumeric = (a, b) => {
    return Number(a.dataset.cutofdate) - Number(b.dataset.cutofdate);
  }

  const sortTableByOreder = (sortOrder, reverse = false) =>{

    mods.sort(sortOrder);
    if (reverse) mods.reverse();

    tableBody.innerHTML = ``;
    mods.forEach((item)=>{
      tableBody.appendChild(item);
    });

  }

// init handlers
  return {
    init: function () {
      tabs.addEventListener('click', function(event){
        let target = event.target;
        while(target != tabs){

          if (target.id === `sort_type`) {
            if (target.classList.contains(`sorted`)){
              sortTableByOreder(compareByType, true);
            } else {
              sortTableByOreder(compareByType);
            }
            setSortedClass(target);
            return
          }

          if (target.id === `sort_modname`) {
            if (target.classList.contains(`sorted`)){
              sortTableByOreder(compareByName, true);
            } else {
              sortTableByOreder(compareByName);
            }
            setSortedClass(target);
            return
          }

          if (target.id === `sort_coursename`) {
            if (target.classList.contains(`sorted`)){
              sortTableByOreder(compareByCourseName, true);
            } else {
              sortTableByOreder(compareByCourseName);
            }
            setSortedClass(target);
            return
          }

          if (target.id === `sort_cutofdate`) {
            if (target.classList.contains(`sorted`)){
              sortTableByOreder(compareNumeric, true);
            } else {
              sortTableByOreder(compareNumeric);
            }
            setSortedClass(target);
            return
          }

          target = target.parentNode;
        }
      });

    }
  }
});
