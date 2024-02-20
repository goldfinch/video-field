(function ($) {

  // function() {} ..

  $(document).ready(() => {
    //
  });

  $('.cms-edit-form').entwine({
    onmatch(e) {
      this._super(e);
    },
    onunmatch(e) {
      this._super(e);
    },
    onaftersubmitform(event, data) {
      // ..
    },
  });

  $.entwine('ss', ($) => {
    $('[data-goldfinch-video-field]').entwine({
      onmatch() {
        // ..
      },
    });
  });
})(jQuery);
