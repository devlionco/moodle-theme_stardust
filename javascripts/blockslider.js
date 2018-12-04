const windowWidth = $(window).width();
const blockWidth = $(".card-block").width();
const curentLang = document.querySelector("html").dir;
const besidesMargin = (windowWidth - blockWidth) / 2;
const sliderWidth = $(".slidingblockpanel").width();

const Margin = {
  right: -besidesMargin,
  left: -besidesMargin
};

const BlockMargin = {
  right: -(besidesMargin + sliderWidth),
  left: -(besidesMargin + sliderWidth)
};


$(document).ready(function() {
  if (curentLang == "ltr") {
    $(".slidingblockpanel").css({ right: BlockMargin.right });
  } else {
    $(".slidingblockpanel").css({ left: BlockMargin.left });
  }
});

$("#blocksliderbutton").click(function() {
  $(this).toggleClass("blocksliderbutton_open");
    
  if ($("#blocksliderbutton").hasClass("blocksliderbutton_open")) {
    var theOffset = $(this).offset();
    $("body, html").animate(
      {
        scrollTop: theOffset.top - 50
      },
      400
    );
    $(".slidingblockpanel").css({ display: "none" });
    if (curentLang == "ltr") {
      $(".slidingblockpanel").css({ display: "block" });
      $(".slidingblockpanel")
        .delay(600)
        .animate({ right: Margin.right }, 600);
    } else {
      $(".slidingblockpanel").css({ display: "block" });
      $(".slidingblockpanel")
        .delay(600)
        .animate({ left: Margin.left }, 600);
    }
  } else {
    if (curentLang == "ltr") {
      $(".slidingblockpanel").animate({ right: BlockMargin.right }, 300);
    } else {
      $(".slidingblockpanel").animate({ left: BlockMargin.left }, 300);
    }
  }

  $(this).find("i").delay(550).toggleClass("fa fa-bars");
  $(this).find("i").delay(550).toggleClass("fa fa-times");
});