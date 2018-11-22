$('#blocksliderbutton').click(function(){

    $('.slidingblockpanel').animate({'right':'-1000px'});   
       $(this).toggleClass('blocksliderbutton_open');

   if($('#blocksliderbutton').hasClass('blocksliderbutton_open')) {
 

        var theOffset = $(this).offset();
       $('body,html').animate({
       scrollTop: theOffset.top - 50
       }, 400); 
       $('.slidingblockpanel').animate({'right':'0px'}, 800);   
   } else {
    $('.slidingblockpanel').animate({'right':'-1000px'});   
   }
      
       $(this).find('i').delay(550).toggleClass('fa fa-bars');
       $(this).find('i').delay(550).toggleClass('fa fa-times');
   });