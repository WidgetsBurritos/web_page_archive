/**
 * Initializes the WPA comparison slider.
 */
function wpaInitializeSlider() {
  var images = document.querySelectorAll('.image-style-web-page-archive-full');
  var ct = 0;

  /**
   * Callback function when image is loaded.
   */
  function imageReady() {
    if (++ct >= 2) {
      jQuery('.ba-slider').beforeAfter();
    }
  }

  /**
   * Loops through all the images to ensure they load properly.
   */
  for (i=0; i<images.length; i++) {
    var image = images[i];
    if (image.complete) {
      imageReady();
    }
    else {
      image.addEventListener('load', imageReady);
      image.addEventListener('error', function() {
        alert(Drupal.t('Could not load image.'));
      });
    }
  }
}
