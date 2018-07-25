define(["jquery"], function ($) {
  var hash2Tab = function() {
    setTimeout(function(){ // fait un micro delay. Sans = mauvais timing avec les Bootstrap events (error: $(...).tab is not a function)
      var hash = window.location.hash;
      $(".nav-tabs a[href='" + hash + "']").tab("show");
    }, 1000);
  };
  var eventListener = function () {
    // update hash dans l'url.. mais quid si plusieurs Tabs dans la page
    $(".nav-tabs .nav-item .nav-link").on("show.bs.tab", function (e) {
      var id = $(e.target).attr("href").substr(1);
      window.location.hash = id;
    });
  };
  return {
    init: function () {
      hash2Tab();
      eventListener();
    }
  };
});
