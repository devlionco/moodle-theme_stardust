define(['core/yui' , 'theme_stardust/popup'], function(Y, popup) {
`use strict`;

  let ajax = {

    url: '/theme/stardust/ajax.php',

    data: '',

    sesskey: M.cfg.sesskey,

    send: function(){

      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {

              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    runWithRender: function(blockToRender){

      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                blockToRender.innerHTML = response.responseText;
              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    runPopup: function(){

      let result;

      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                // popup.text = response.responseText;
                let result = JSON.parse(response.responseText);
                popup.textHead = result.header;
                popup.text = result.content;
                popup.show();
              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    setHTML: function(){

      this.data.sesskey = this.sesskey;
      const targetBlock = document.querySelector(this.data.target_block);

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                popup.remove();
                let result = JSON.parse(response.responseText);
                targetBlock.innerHTML = result.content;;
              },
              failure: function () {
                popup.error();
              }
          }
      });
    },

  }

  return ajax

});
