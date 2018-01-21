/** global: jQuery */
/** global: Drupal */

(function ($, Drupal) {
  Drupal.behaviors.wpaBeforeAfterSlider = {
    attach: function (context, settings) {
      var $modal = $('#drupal-modal');
      var $images = $modal.find('.image-style-web-page-archive-full');

      // When the modal resizes, we need to ensure the images are no wider than
      // the modal itself, otherwise before/after will scale the resize
      // image differently.
      $modal.on('dialogContentResize', function() {
        $images.css('max-width', $(this).width() + 'px');
      })

      // Ensure both images load properly and then trigger before/after.
      $modal.once('wpaBeforeAfterSlider').each(function () {
        var imageLoadedCt = 0;

        // Only trigger before/after once both images have loaded.
        $images.on('load', function() {
          if (++imageLoadedCt == 2) {
            $('.wpa-slider').beforeAfter();
          }
        });

        // If either image fails to load for now we just open an alert.
        // We may want to find a better solution for this in the future.
        $images.on('error', function() {
          alert(Drupal.t('Could not load image.'));
        });
      });
    }
  };
})(jQuery, Drupal);
