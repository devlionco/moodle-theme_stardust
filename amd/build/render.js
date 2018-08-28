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

  }

  return render

});
