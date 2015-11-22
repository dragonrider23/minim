<?php
return [
    'sitename' => 'Some Website',              // Name of site dispayed on every page
    'siteurl' => 'http://localhost/minim',    // URL for the Minim site
    'http404page' => '../page/404.md',        // Page to display when a 404 error is encountered
    'cacheDir' => '../cache',                 // Location of HTML cache, relative to public directory
    'pageDir' => '../page',                   // Location of pages, relative to public directory
    'articleDir' => '../article',             // Location of articles, relative to public directory
    'defaultPageType' => 'md',                // Type to assign page if one isn't given, md, html, txt
    'cachePages' => false,                    // Cache generated HTML
    'debug' => false,                          // Enabled debug mode, set to false in production
    'articlesPerPage' => 10,                   // Number of articles to display per page
];
