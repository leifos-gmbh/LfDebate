il = il || {};
il.Debate = il.Debate || {};
(function($, il) {
  il.Debate = (function($) {

    var toggle = function (url, glyph_id, widget_id, exp_id) {
      var val;
      if ($("#" + glyph_id).hasClass("highlighted")) {
        $("#" + glyph_id).removeClass("highlighted");
        val = 0;
      } else {
        $("#" + glyph_id).addClass("highlighted");
        val = 1;
      }
      il.Util.ajaxReplace(url + "&cmd=saveExpression&exp=" + exp_id + "&val=" + val + "&dom_id=" + widget_id, widget_id + "_ec");
    };

    return {
      toggle: toggle
    };
  })($);
  $(() => {
    const pin = document.querySelector("[data-debate='pin'] a");
    if (pin) {

      const topPosting = document.querySelector(".debate-item");
      const debateReply = document.querySelector(".debate-reply");
      const headerInner = document.querySelector(".il_HeaderInner");
      const ilTab = document.getElementById("ilTab");


      pin.addEventListener("click", (e) => {
        console.log("click");
        if (pin.classList.contains("debate-highlighted")) {
          pin.classList.remove("debate-highlighted");
          topPosting.classList.remove("debate-sticky");
          debateReply.classList.remove("debate-sticky");
          headerInner.classList.remove("debate-sticky");
          ilTab.classList.remove("debate-sticky");
        } else {
          pin.classList.add("debate-highlighted");
          topPosting.classList.add("debate-sticky");
          debateReply.classList.add("debate-sticky");
          headerInner.classList.add("debate-sticky");
          ilTab.classList.add("debate-sticky");
        }
      });
    }

    /*
    console.log("---1---");
    console.log(topPosting);
    const offset = document.querySelector(".ilTabsContentOuter").offsetTop;
    console.log(offset);
    const contentElement = document.querySelector("main");
    contentElement.addEventListener("scroll", function() {
      console.log("top Posting offset: " + offset);
      console.log("content el scroll: " + contentElement.scrollTop);
      if (contentElement.scrollTop > offset) {
        console.log("add");
        topPosting.classList.add("debate-sticky");
        debateReply.classList.add("debate-sticky");
      } else {
        console.log("remove");
        topPosting.classList.remove("debate-sticky");
        debateReply.classList.remove("debate-sticky");
      }
    });*/


  });
})($, il);