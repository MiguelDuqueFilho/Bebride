/*
    Carousel
*/
$('#carousel-depositions').on('slide.bs.carousel', function (e) {
  /*
      CC 2.0 License Iatek LLC 2018 - Attribution required
  */
  var $e = $(e.relatedTarget);
  var idx = $e.index();
  var itemsPerSlide = 4;
  var totalItems = $('.carousel-item').length;

  var element = document.getElementById("carousel-depositions");

  if (window.innerWidth <= 576)
  {
    element.classList.remove("slide");
    console.log('class remove style: ' + element);
  }
  else
  {
    element.classList.add("slide");
    console.log('class add style: ' + element);
  }


  if (idx >= totalItems-(itemsPerSlide-1)) {
      var it = itemsPerSlide - (totalItems - idx);
      for (var i=0; i<it; i++) {
          // append slides to end
          if (e.direction=="left") {
              $('.carousel-item').eq(i).appendTo('.carousel-inner');
  
          }
          else {
              $('.carousel-item').eq(0).appendTo('.carousel-inner');
          }
      }
  }
});


  