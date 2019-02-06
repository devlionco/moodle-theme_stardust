define(['jquery'], function ($) {
  'use strict';

  const questionsnav = function () {

    var wrapper = document.querySelector('.qn_buttons'),
        inner =  document.querySelector('.qn_buttons-inner'),
        collectionAll = wrapper.querySelectorAll('.qnbutton'),
        firstOnThisPage = wrapper.querySelector('.qnbutton.thispage'),
        wrapperCenterPos = wrapper.offsetLeft + wrapper.offsetWidth/2;
    var dir = document.dir, fixrtl = false;

    // set computed width for inner block;
    $('.qn_buttons-inner').css('width', 68.5*collectionAll.length-30 +'px');

    if (dir == 'rtl'){
        wrapper.style.direction = "ltr";
        inner.style.direction = "ltr";
        inner.style.flexDirection = "row-reverse";
        fixrtl = true;
    }

    function getXCenter(elem){
        return elem.offsetLeft + elem.offsetWidth/2;
    }
    var a = wrapper.offsetLeft+wrapper.offsetWidth;
    var b = firstOnThisPage.offsetLeft + firstOnThisPage.offsetWidth;
    var c = inner.offsetWidth+ inner.offsetLeft;

    // set padding to normalize center position
    var padding = wrapper.offsetWidth/2;
    $('.qn_buttons-inner').css('padding','0 '+ padding +'px');

    // first init active page
    if (fixrtl){
      var listStart = inner.offsetLeft + inner.offsetWidth;
      var startPos = firstOnThisPage.offsetLeft + firstOnThisPage.offsetWidth - wrapper.offsetWidth; //display ccenter
      firstOnThisPage.style.color = '#f00';
      $('.qn_buttons').animate({scrollLeft: startPos}, 1000);
    } else {
      var startPos = getXCenter(firstOnThisPage) - firstOnThisPage.offsetWidth/2;
      $('.qn_buttons').animate({scrollLeft: startPos}, 1000);
    }

    for(var i=0; i<collectionAll.length; i++){
      var item = collectionAll[i];
      item.addEventListener('click', function(){
        if (fixrtl){
          var itemCenterPos = this.offsetLeft + this.offsetWidth/2 - padding;
          $('.qn_buttons').animate({scrollLeft: itemCenterPos}, 1000);
        } else {
          var itemCenterPos = getXCenter(this) - wrapper.offsetWidth/2;
          $('.qn_buttons').animate({scrollLeft: itemCenterPos}, 1000);
        }
      });
    }

  }

  return {
    init: questionsnav
  }
});
