define(['core/str'], function(str) {
`use strict`;

  // str.get_strings([
  //     {key: 'close', component: 'local_social'},
  //     {key: 'error_message', component: 'local_social'}
  // ]).done(function(){});

  const mainBlock = document.querySelector(`.davidson`);

  // const textError = M.util.get_string('error_message', 'local_social');
  const textError = `Failure in the system, operation failed`;
  // const closeBtn = M.util.get_string('close', 'local_social');
  const closeBtn = `Close`;

  const addStyleToPage = () => {
    const styleText = `
      .modal {
        position: fixed;
        display: flex;
        overflow: hidden;
        flex-direction: column;
        z-index: 10000;
        width: 561px;
        left: calc(50% - 280px);
        top: 20vh;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px 0 #7b7b7b;
        bottom: unset;
        min-height: 30vh;
      }
      .modal-error {
        background-color: #fff5f6;
        align-items: center;
      }
      .modal-error span {
          color: #dc3545;
          display: flex;
          height: 24vh;
          align-items: center;
          justify-content: center;
      }
      .modal-error-abs {
        position: absolute;
        width: 96%;
        left: 2%;
        right: 2%;
        bottom: -10px;
        z-index: 100;
        text-align: center;
        background-color: rgba(255, 230, 230, 0.9);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }
      .modal-error-abs span {
        color: #dc3545;
      }
      .modal_inner {
        text-align: center;
      }
      .modal_header {
        display: flex;
        padding: 0 12px;
        font: 400 30px/50px 'assistant';
        background-color: $purple;
        color: #fff;
      }
      .modal_head {
        margin: 0;
        margin-right: auto;
      }
      .modal_close  {
        margin-left: auto;
      }

      .modal_close {
        cursor: pointer;
        position: absolute;
        right: 7px;
        top: 9px;
        width: 33px;
        height: 33px;
        transition: .5s;
      }
      .modal_close:hover {
        transform: rotate(180deg);
      }
      .modal_close:before, .modal_close:after {
        position: absolute;
        left: 15px;
        content: ' ';
        height: 32px;
        width: 2px;
        background-color: #fff;
      }
      .modal_close:before {
        transform: rotate(45deg);
      }
      .modal_close:after {
        transform: rotate(-45deg);
      }
      .warning_wrap {
        height: 17vh;
        padding-top: 70px;
      }
    }
    `;
    const styleBlock = document.createElement(`style`);
    if (!document.querySelector(`.modal`)) {
      mainBlock.appendChild(styleBlock);
    }
  }

  const popup = {

    textHead: ``,
    text: ``,
    textError: textError,

    show: function () {

      addStyleToPage();
      const popup = document.createElement(`div`);
        popup.innerHTML = `
          <div class = "modal_header">
            <p class = "modal_head">${this.textHead}</p>
            <span class = "modal_close"></span>
          </div>
          <div class = "modal_inner"></div>

        `;
        popup.classList.add(`modal`);
      const popupInner = popup.querySelector(`.modal_inner`);

        popupInner.innerHTML = this.text;
        this.remove();
        mainBlock.appendChild(popup);
    },

    error: function () {

      if (mainBlock.querySelector(`.modal`)) {
        const errorBlock = document.createElement(`div`);
        errorBlock.classList.add(`modal-error-abs`, `alert`, `alert-warning`);
        errorBlock.innerHTML = `
          <span>${this.textError}</span>
          <button class = "btn btn-error close_popup">${closeBtn}</button>
        `;
        mainBlock.querySelector(`.modal`).appendChild(errorBlock);
      }else {

        const popup = document.createElement(`div`);
          popup.innerHTML = `
            <span>${this.textError}</span>
            <button class = "btn btn-error close_popup">${closeBtn}</button>
          `;
          popup.classList.add(`modal`, `modal-error`);

          this.remove();
          mainBlock.appendChild(popup);
      }

    },

    remove: function () {
      if(mainBlock.querySelector(`.modal`)) {
        mainBlock.querySelector(`.modal`).remove();
      }
    }

  };

  return popup

});
