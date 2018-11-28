const windowWidth = $(window).width();
const blockWidth = $(".card-block").width();
const curentLang = document
    .querySelector("html")
    .dir;
const margin = (windowWidth - blockWidth) / 2;

$(document).ready(function () {
    if (curentLang == "ltr") {
    
        $(".slidingblockpanel").animate({right: "-1000px"});
    } else {
        $(".slidingblockpanel").animate({left: "-1000px"});
  
    }
});

$("#blocksliderbutton").click(function () {
    $(this).toggleClass("blocksliderbutton_open");

    if ($("#blocksliderbutton").hasClass("blocksliderbutton_open")) {
        var rightMarg = {};

        

        var theOffset = $(this).offset();

        $("body,html").animate({
            scrollTop: theOffset.top - 50
        }, 400);

        if (curentLang == "ltr") {
            rightMarg.right = -margin;
            $(".slidingblockpanel").animate(rightMarg, 800);
        } else {
            rightMarg.left = -margin;
            $(".slidingblockpanel").animate(rightMarg, 800);
        }
    } else {
        if (curentLang == "ltr") {
            $(".slidingblockpanel").animate({right: "-1000px"});
        } else {
            $(".slidingblockpanel").animate({left: "-1000px"});
        }
    }

    $(this)
        .find("i")
        .delay(550)
        .toggleClass("fa fa-bars");
    $(this)
        .find("i")
        .delay(550)
        .toggleClass("fa fa-times");
});
