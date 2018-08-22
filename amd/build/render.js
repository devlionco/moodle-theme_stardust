define(['core/yui' , 'theme_stardust/popup'], function(Y, popup) {
`use strict`;

  const davidson = document.querySelector(`.davidson`);

  let render = {

    url: '/theme/stardust/ajax.php',

    data: '',

    sesskey: M.cfg.sesskey,

    tabweek: function(){

      const targetBlock = davidson.querySelector(`#tabweek`);
      this.data.method = `render_learn_stat`;
      // this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                let result = JSON.parse(response.responseText);
                targetBlock.innerHTML = result.content;
              },
              failure: function () {
                popup.error();
              }
          }
      });
    }

    // publicCourse: function(){
    //
    //   const publicCourse = social.querySelector(`.public-course`);
    //   this.data.metod = `render_public_course`;
    //   // this.data.sesskey = this.sesskey;
    //
    //   Y.io(M.cfg.wwwroot + this.url, {
    //       method: 'POST',
    //       data: this.data,
    //       headers: {
    //           //'Content-Type': 'application/json'
    //       },
    //       on: {
    //           success: function (id, response) {
    //             popup.remove();
    //             let result = JSON.parse(response.responseText);
    //             publicCourse.innerHTML = result.content;
    //           },
    //           failure: function () {
    //             popup.error();
    //           }
    //       }
    //   });
    // },
    //
    // //Block Aside User Data
    // userData: function(userid){
    //
    //   const userData = social.querySelector(`.user`);
    //   this.data.metod = `render_block_user_data`;
    //   this.data.userid = userid;
    //   // this.data.sesskey = this.sesskey;
    //
    //   Y.io(M.cfg.wwwroot + this.url, {
    //       method: 'POST',
    //       data: this.data,
    //       headers: {
    //           //'Content-Type': 'application/json'
    //       },
    //       on: {
    //           success: function (id, response) {
    //             popup.remove();
    //             let result = JSON.parse(response.responseText);
    //             userData.innerHTML = result.content;
    //           },
    //           failure: function () {
    //             popup.error();
    //           }
    //       }
    //   });
    // },
    //
    //   //Block Aside Courses Pombim
    //   asideCoursesPombim: function(userid){
    //
    //       const coursesPombim = social.querySelector(`.public-course`);
    //       this.data.metod = `render_block_aside_courses_pombim`;
    //       this.data.userid = userid;
    //       // this.data.sesskey = this.sesskey;
    //
    //       Y.io(M.cfg.wwwroot + this.url, {
    //           method: 'POST',
    //           data: this.data,
    //           headers: {
    //               //'Content-Type': 'application/json'
    //           },
    //           on: {
    //               success: function (id, response) {
    //                   popup.remove();
    //                   let result = JSON.parse(response.responseText);
    //                   coursesPombim.innerHTML = result.content;
    //               },
    //               failure: function () {
    //                   popup.error();
    //               }
    //           }
    //       });
    //   },
    //
    //   //Block Courses Pombim
    //   coursesPombim: function(userid){
    //
    //       const coursesPombim = social.querySelector(`.course__wrapper`);
    //       this.data.metod = `render_block_courses_pombim`;
    //       this.data.userid = userid;
    //       // this.data.sesskey = this.sesskey;
    //
    //       Y.io(M.cfg.wwwroot + this.url, {
    //           method: 'POST',
    //           data: this.data,
    //           headers: {
    //               //'Content-Type': 'application/json'
    //           },
    //           on: {
    //               success: function (id, response) {
    //                   popup.remove();
    //                   let result = JSON.parse(response.responseText);
    //                   coursesPombim.innerHTML = result.content;
    //               },
    //               failure: function () {
    //                   popup.error();
    //               }
    //           }
    //       });
    //   },
    //   //Block Courses Pombim
    //   subjectsOercatalog: function(userid){
    //
    //       const subjectsOercatalog = social.querySelector(`.subj__wrap`);
    //       this.data.metod = `render_block_subject_oercatalog`;
    //       this.data.userid = userid;
    //       // this.data.sesskey = this.sesskey;
    //
    //       Y.io(M.cfg.wwwroot + this.url, {
    //           method: 'POST',
    //           data: this.data,
    //           headers: {
    //               //'Content-Type': 'application/json'
    //           },
    //           on: {
    //               success: function (id, response) {
    //                   popup.remove();
    //                   let result = JSON.parse(response.responseText);
    //                   subjectsOercatalog.innerHTML = result.content;
    //               },
    //               failure: function () {
    //                   popup.error();
    //               }
    //           }
    //       });
    //   }

  }

  return render

});
