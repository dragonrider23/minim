<?php
use Minim\Shortcode;

Shortcode::register('datetime', function(array $options) {
    $format = array_key_exists('format', $options) ? $options['format'] : 'Y-m-d H:i:s';
    return date($format);
});
