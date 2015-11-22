<?php

use Minim\Shortcode;
use Minim\Page;
use Minim\Config;

Shortcode::register('site', function(array $options, Config $config, Page $page) {
    if (array_key_exists('config', $options)) {
        return $config->get($options['config']) ?: '';
    } elseif (array_key_exists('page', $options)) {
        return $page->getMetadata($options['page']) ?: '';
    }
});
