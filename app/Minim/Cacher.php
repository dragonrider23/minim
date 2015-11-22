<?php
namespace Minim;

class Cacher
{
    private $cache = null;

    public function __construct($cachePath)
    {
        $this->cache = new Cache($cachePath);
    }

    public function cacheContent($pagefilename, $parsedHtml, $type, $requestedpage)
    {
        $file = realpath($pagefilename) ."\n". md5_file($pagefilename) ."\n". $parsedHtml;
        $this->cache->set("$type-$requestedpage.html", $file);
        //$this->cache->set("menu.conf", $menuHash);
    }

    public function getCachedContent($type, $page)
    {
        $file = $this->cache->get("$type-$page.html");
        if ($file === false) {
            return false;
        }

        // Cache structure, Line 1: Source path, 2: Source hash, 3: HTML Content
        $hash = explode("\n", $file, 3);
        $currentHash = md5_file($hash[0]);

        // If the source doesn't match its current form, bad cache
        if ($currentHash != $hash[1]) {
            return false;
        }

        // // Check valid menu
        // $menu = $this->cache->getArray('menu.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // $menuItems = 0;
        //
        // $pages = glob("./page/*.*");
        // foreach($pages as $page) {
        //     $pageObj = new Page($page);
        //     if ($pageObj->getMetadata('menu') && $pageObj->getMetadata('url') && $pageObj->getMetadata('title')) {
        //         $menuItems++;
        //         if (!in_array(md5_file($page), $menu)) {
        //             $this->cache->purge();
        //             return false;
        //         }
        //     }
        // }
        //
        // if ($menuItems != count($menu)) {
        //     $this->cache->purge();
        //     return false;
        // }

        return $hash[2];
    }

    public function displayCachedContent($type, $page)
    {
        $content = $this->getCachedContent($type, $page);
        if ($content === false) {
            return false;
        }
        echo $content;
        return true;
    }
}
