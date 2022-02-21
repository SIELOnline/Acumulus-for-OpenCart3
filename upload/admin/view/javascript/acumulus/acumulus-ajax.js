"use strict";
(function($) {
  function addAcumulusAjaxHandling(elt) {
    const buttonSelector = "button, input[type=button], input[type=submit]";
    $(buttonSelector, ".acumulus-area").addClass("btn btn-primary"); // jQuery
    $(".acumulus-ajax", elt).click(function() { // jQuery
      // Area is the element that is going to be replaced and serves as the
      // parent in which we will search for form elements.
      const clickedElt = this;
      const area = $(clickedElt).parents(".acumulus-area").get(0); // jQuery
      $(buttonSelector, area).prop("disabled", true); // jQuery
      clickedElt.value = area.getAttribute('data-acumulus-wait');

      // The URL we are going to send to.
      const ajaxUrl = area.getAttribute('action');
      // The data we are going to send consists of:
      // - ajax: 1 (To recognize it as an ajax call)
      // - clicked: the name of the element that was clicked, the name should
      //   make clear what action is requested on the server and, optionally, on
      //   what object.
      // - {values}: values of all form elements in area: input, select and
      //   textarea, except buttons (inputs with type="button").
      //noinspection JSUnresolvedVariable
      const data = {
        ajax: 1,
        clicked: clickedElt.name,
      };
      // Area is a form node, so FormData will work.
      const formData = new FormData(area);
      for(let entry of formData.entries()) {
        data[entry[0]] = entry[1];
      }

      // Send the ajax request.
      $.post(ajaxUrl, data, function(response) { // jQuery
        area.insertAdjacentHTML('beforebegin', response.content);
        const newArea = area.previousElementSibling;
        area.parentNode.removeChild(area);
        addAcumulusAjaxHandling(newArea);
      });
    });
  }

  $(document).ready(function() { // jQuery
    addAcumulusAjaxHandling(document);
    $(".acumulus-auto-click").click(); // jQuery
  });
}(jQuery));
