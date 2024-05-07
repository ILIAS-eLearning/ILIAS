il.Util.addOnLoad(function() {

  let interval_reference = null;
  const pbEl = document.querySelector("[data-ui-pb-ajax-url]");
  if (!pbEl) {
    return;
  }
  const ajaxUrl = pbEl.dataset.uiPbAjaxUrl;
  const ajaxTimeout = pbEl.dataset.uiPbAjaxTimeout;

  var refresh = function() {
    console.log(ajaxUrl);
    var status = $.ajax({
      type:		'GET',
      cache:		false,
      dataType:	'json',
      url:		ajaxUrl
    });

    status.done(function(response) {

        var max = parseInt($('#progress_div_' + response.id).attr("valmax"));
        var required_steps = parseInt(response.required_steps);
        var percentage = 100;

        //console.log('Required steps: ' + required_steps);
        //console.log('Max steps: ' + max);

        if (required_steps > 0) {
          if (max > 0) {
            percentage = parseInt(100 - ((required_steps / max) * 100));
          }
        }

        if (percentage >= 100) {
          console.log('Stop ajax');
          console.log(interval_reference);
          clearInterval(interval_reference);

          $('#progress_container_' + response.id).toggle();
          $('#progress_done_' + response.id).toggle();
        }

        $('#progress_div_' + response.id).css('width', percentage + '%');
        $('#progress_div_' + response.id).text(percentage + '%');
        $('#progress_div_' + response.id).attr('aria-valuenow', percentage);

      }
    )
  };

  interval_reference = setInterval(refresh, ajaxTimeout);
});