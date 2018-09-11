define(['jquery'], function ($) {
  'use strict';

  const questionsnav = function () {

    var wrapper = document.querySelector('.qn_buttons'),
        inner =  document.querySelector('.qn_buttons-inner'),
        collectionAll = wrapper.querySelectorAll('.qnbutton'),
        firstOnThisPage = wrapper.querySelector('.qnbutton.thispage'),
        wrapperCenterPos = getXCenter(wrapper);
    var dir = document.dir;

    function getXCenter(elem){
      if (dir == 'ltr'){
        return elem.offsetLeft + elem.offsetWidth/2;
      } else {
        return elem.offsetLeft + elem.offsetWidth*3;
      }
        // not for IE
    }
    // var scrollNegative = startPos.scrollLeft > 0 ? false : true;
    // console.log('startOffsetPos:'+firstOnThisPage.offsetLeft);
    // console.log('startPos:'+ inner.offsetWidth);
    // console.log('wrapperScrL:'+wrapper.scrollLeft);

    // set padding to normalize center position
    var padding = wrapper.offsetWidth/2;
    $('.qn_buttons-inner').css('padding','0 '+ padding +'px');

    // first init active page - 6 visible at once
    if (dir == 'ltr'){
      var startPos = getXCenter(firstOnThisPage) - firstOnThisPage.offsetWidth/2;
      $('.qn_buttons').animate({scrollLeft: startPos}, 1000);
    } else {
      // NOTE: fix scroll rtl direction for webkit|gecko|IE - https://github.com/othree/jquery.rtl-scroll-type
      var cachedType = 0; // IE
      var startPos = wrapper.scrollWidth - wrapper.clientWidth - getXCenter(firstOnThisPage);
      // console.log('reverse IE:'+startPos);
      if (wrapper.scrollLeft > 0) {
        cachedType = 1; //webkit
        startPos = getXCenter(firstOnThisPage);
        // console.log('default webkit'+startPos);
      } else {
        wrapper.scrollLeft = 1;
        if (wrapper.scrollLeft === 0) {
          cachedType = -1; //gecko
          startPos = wrapper.scrollWidth - wrapper.clientWidth + getXCenter(firstOnThisPage);
          // console.log('negative gecko'+startPos);
        }
      }
      // console.log('init'+startPos);
      $('.qn_buttons').animate({scrollLeft: startPos}, 1000);
    }

    for(var i=0; i<collectionAll.length; i++){
      var item = collectionAll[i];
      item.addEventListener('click', function(){
        if (dir == 'ltr'){
          var itemCenterPos = getXCenter(this) - wrapper.offsetWidth/2;
          $('.qn_buttons').animate({scrollLeft: itemCenterPos}, 1000);
        } else {
          var itemCenterPos = getXCenter(this) + wrapper.offsetWidth/2;
          $('.qn_buttons').animate({scrollLeft: itemCenterPos}, 1000);
          // console.log('init'+itemCenterPos);
        }
      });
    }

    // set computed width for inner block;
    // $('.qn_buttons-inner').css('width', $('.qnbutton.thispage').outerWidth(true)*collectionAll.length +'px');
    $('.qn_buttons-inner').css('width', 68.5*collectionAll.length-30 +'px');
  }

  return {
    init: questionsnav
  }
});
