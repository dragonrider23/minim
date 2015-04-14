<?php
if (php_sapi_name() !== 'cli') {
    exit();
}

define('CLI', true);

require 'index.php';

function printUsage()
{
    echo "\nUsage: php cli.php [command]\n";
    echo "Commands: genall - Generate a full site cache\n";
    echo "purge - Purge current cache\n\n";
}

if ($argc === 1) {
    printUsage();
    exit();
}

$command = $argv[1];

// genall - Generate a full site cache
if ($command === 'genall') {
    // Purge current cache
    array_map('unlink', glob($parsedHtmlPath.'/*'));

    // Generate pages
    $pageSource = glob('./page/*');
    foreach ($pageSource as $page) {
        $type = 'page';
        list(, $pagecontent, $pagemeta) = getpage($page);
        if ($page === './page/index.md') {
            // For the index page, no matter what it's title is in
            // the metadata, its url is always index
            $pagemeta['url'] = 'index';
        } elseif ($page === $http404page) {
            // Don't cache the 404 page
            continue;
        }

        if (!trim($pagecontent)) {
            // Don't cache pages with no content
            continue;
        }

        $parsedHtml = generatePage([
            'sitename' => $sitename,
            'type' => $type,
            'title' => $pagemeta['title'] ? $sitename.' - '.$pagemeta['title'] : $sitename,
            'pagemeta' => $pagemeta,
            'content' => $pagecontent,
            'basepath' => rtrim($siteurl, '/') . '/'
          ]);

        saveCache($page, $parsedHtml, $parsedHtmlPath, $type, $pagemeta['url']);
    }

    // Generate articles
    $articleSource = glob('./article/*');
    foreach ($articleSource as $article) {
        $type = 'article';
        list(, $articleContent, $articlemeta) = getpage($article);

        if (!trim($articleContent)) {
            // Don't cache articles with no content
            continue;
        }

        $parsedHtml = generatePage([
            'sitename' => $sitename,
            'type' => $type,
            'title' => $articlemeta['title'] ? $sitename.' - '.$articlemeta['title'] : $sitename,
            'pagemeta' => $articlemeta,
            'content' => $articleContent,
            'basepath' => rtrim($siteurl, '/') . '/'
          ]);

        $articleurl = explode('/', $article);
        $articleurl = explode('.', $articleurl[2]);

        saveCache($article, $parsedHtml, $parsedHtmlPath, $type, $articleurl[0]);
    }
} elseif ($command === 'purge') {
    array_map('unlink', glob($parsedHtmlPath.'/*'));
} else {
    printUsage();
    exit();
}
