<?php
use Minim\Shortcode;

Shortcode::register('datetime', function() {
    return date('Y-m-d H:i:s');
});
