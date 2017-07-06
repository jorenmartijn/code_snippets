/**
* Author: Joren de Graaf <jorendegraaf@gmail.com>
**/
jQuery(document).ready(function($) {
  // Define elements
  var $coords = $("#coords"),
    $dots = $(".dot", $coords),
    $map = $("#map"),
    // Styling object for a dot
    dotConfig = {
      width: 30,
      border: "3px solid #108ac2",
      borderRadius: "100%",
      position: "absolute",
      backgroundColor: "#c28a10",
      cursor: "pointer"
    },
    // Create a dot element and place it
    placeDot = function(x, y, target) {
      var $elem = $("<div/>").css({
        left: x,
        top: y,
        height: dotConfig.width,
        fontSize: dotConfig.height / 2 + "px",
        lineHeight: dotConfig.height - 5 + "px",
        textAlign: "center",
        fontWeight: "bold"
      }),
        $target = $(target);
      $elem.css(dotConfig).appendTo($map);
      $elem.text("!");
      // Assign event listeners
      $elem.on("click", function() {
        $target.css({
          visibility: "visible",
          opacity: 1
        });
      });
      $elem.on("mouseleave", function() {
        $target.css({
          visibility: "hidden",
          opacity: 0
        });
      });
    };
  // Loop through each dot
  $dots.each(function(key, value) {
    var point = $(value);
    // Place a dot on the image
    placeDot(point.data("x"), point.data("y"), point.data("target"));
  });
});