<?php

use Minim\Shortcode;
use Minim\Page;
use Minim\Config;

Shortcode::register('article-list', function($options, Config $config, Page $page) {
    $articleList = '';
    $perPage = $config->get('articlesPerPage');

    if (isset($_GET['start'])) {
        $startPos = $_GET['start'];
    } else {
        $startPos = 0;
    }

    $pages = array_slice(array_reverse(glob("{$config->get('articleDir')}/*.*")), $startPos, $perPage);

    foreach($pages as $pageFilename) {
        if (!preg_match("~\d{4}-\d{2}-\d{2}~", $pageFilename)) {
            continue;
        }

        $pageObj = new Page($pageFilename);
        $newpage = mb_substr($pageFilename, 3, mb_strrpos($pageFilename, '.')-3);
        $articleList .= '<div class="article"><a href="'. $newpage . '">';
        $articleList .= '<h2 class="articletitle">'.$pageObj->getMetadata('title').'</h2><div class="articleinfo">by '.$pageObj->getMetadata('author').', on '.$pageObj->getMetadata('date').'</div></a>';
        $articleList .= $pageObj->render();
        $articleList .= '</div>';
    }

    if ($startPos > 0) {
        $articleList .= '<a href="' . $page->getMetadata('url') . (($startPos > $perPage) ? '?start=' . ($startPos - $perPage) : '') . '">Newer articles</a>&nbsp;';
    }

    if (count(array_slice(array_reverse(glob("{$config->get('articleDir')}/*.*")), $startPos, $perPage+1)) > $perPage) {
        $articleList .= '<a href="' . $page->getMetadata('url') . '?start=' . ($startPos + $perPage) . '">Older articles</a>';
    }

    return $articleList;
});
