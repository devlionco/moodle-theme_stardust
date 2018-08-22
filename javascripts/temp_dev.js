`use strict`;

const container = document.querySelector(`.tab-pane.active ul`);
const courses = Array.from(container.querySelectorAll(`li`));
const filters = document.querySelector(`.filters`);

const filterDate = document.querySelector(`.filter-date`);
const filterAbc = document.querySelector(`.filter-abc`);

const compareByCourseName = (a, b) => {
  return (
    a.dataset.coursename > b.dataset.coursename ?  1 :
    a.dataset.coursename < b.dataset.coursename ? -1 :
    0
  )
}

const compareNumeric = (a, b) => {
  return Number(a.dataset.date) - Number(b.dataset.date);
}

filters.addEventListener('click', function(e){
  e.target.classList.add(`active`);
  if (e.target.classList.contains(`filter-abc`)) {
    filters.querySelector(`.filter-date`).classList.remove(`active`);
    courses.sort(compareNumeric);
  } else if  (e.target.classList.contains(`filter-date`)) {
    filters.querySelector(`.filter-abc`).classList.remove(`active`);
    courses.sort(compareByCourseName)
  }

  container.innerHTML = ``;
  courses.forEach((course)=>{
    container.appendChild(course);
  });
});
