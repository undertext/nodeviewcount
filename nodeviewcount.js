/**
 * @file
 * Nodeviewcount statistics functionality.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  $(document).ready(function () {
    $.ajax({
      type: 'POST',
      cache: false,
      url: drupalSettings.nodeviewcount.url,
      data: drupalSettings.nodeviewcount.data
    });
  });
})(jQuery, Drupal, drupalSettings);
