<?php
// User settings
$sitename = 'SomeWebsite';
$http404page = './page/404.md';
$parsedHtmlPath = './parsed';
$defaultPageType = 'md'; // md, html, txt
$cachePages = true;
$debug = false;

// Don't edit below here
//
if ($debug) {
  ini_set('display_errors', true);
  ini_set('display_startup_errors', true);
} else {
  error_reporting(0);
}

require 'vendor/autoload.php';

// Parse file and get metadata
function getpage($page)
{
  global $defaultPageType;
  $pagestr = file_get_contents($page);
  if (substr($pagestr, 0, 3) !== '---') {
    return [
      null,
      $pagestr,
      [
        'title' => '',
        'author' => '',
        'date' => '',
        'menu' => '0',
        'url' => '',
        'type' => 'txt'
      ]
    ];
  }

  list(, $pageheader, $pagecontent) = preg_split('/---/', $pagestr, 3);   // split into 3 parts : above the first --- (blank), metadata, content

  preg_match("/^TITLE:\s*(.*)$/m", $pageheader, $titleMatch);       // for articles: title, for pages: title displayed in top-menu
  preg_match("/^AUTHOR:\s*(.*)$/m", $pageheader, $authorMatch);     // for articles only
  preg_match("/^DATE:\s*(.*)$/m", $pageheader, $dateMatch);         // for articles only
  preg_match("/^MENU:\s*([01])$/m", $pageheader, $menuMatch);       // for pages only: if MENU:1, link in top-menu, MENU:0 or not specified, no link
  preg_match("/^URL:\s*(.*)$/m", $pageheader, $urlMatch);           // for pages only: top-menu's link  (=TITLE if no URL is set)
  preg_match("/^TYPE:\s*(.*)/m", $pageheader, $typeMatch);          // type of document, HTML, markdown, text, etc.

  $metadata = [
    'title' => isset($titleMatch[1]) ? $titleMatch[1] : '',
    'author' => isset($authorMatch[1]) ? $authorMatch[1] : '',
    'date' => isset($dateMatch[1]) ? $dateMatch[1] : '',
    'menu' => isset($menuMatch[1]) ? $menuMatch[1] : '0',
    'url' => isset($urlMatch[1]) ? $urlMatch[1] : '',
    'type' => isset($typeMatch[1]) ? strtolower($typeMatch[1]) : $defaultPageType
  ];
  $metadata['url'] = $metadata['url'] ?: str_replace(' ', '-', strtolower($metadata['title']));

  return array($pageheader, $pagecontent, $metadata);
}

// Display a cached version if possible
function displayCached($basepath, $type, $page)
{
  global $cachePages;
  if (!$cachePages) {
    return;
  }

  if (file_exists($basepath.'/'.$type.'-'.$page.'.html')) {
    $file = file_get_contents($basepath.'/'.$type.'-'.$page.'.html');
    $hash = explode("\n", $file, 3);
    $currentHash = md5_file($hash[0]);

    if ($currentHash == $hash[1]) {
      debugHeader('Page was in cache');
      echo $hash[2];
      exit();
    }
  }
}

function renderContent($type, $content)
{
  switch ($type) {
    case 'md': // Markdown
      $md = (new Parsedown())->text($content);
      return parseTags($md);
      break;

    case 'txt': // Fall through
    case 'text':// Text document
      return nl2br(htmlentities($content));
      break;

    default: // Everything else
      return $content;
      break;
  }
}

function parseTags($content) {
  if (strpos($content, '{{article list}}')) {
    $articleList = '';

    if (isset($_GET['start'])) {
      $startPos = $_GET['start'];
    } else {
      $startPos = 0;
    }

    $pages = array_slice(array_reverse(glob("./article/*.*")), $startPos, 10);

    foreach($pages as $page)
    {
      list($pageheader, $pcontent, $pagemeta) = getpage($page);
      $newpage = substr($page, 2); // Remove the './' from the front
      $newpageParts = explode('.', $newpage); // Get all the parts, typicall $file . $ext
      array_pop($newpageParts); // Remove the last element being the extension
      $newpage = implode('.', $newpageParts); // Rebuild path
      $articleList .= '<div class="article"><a href="'. $newpage . '">';
      $articleList .= '<h2 class="articletitle">'.$pagemeta['title'].'</h2><div class="articleinfo">by '.$pagemeta['author'].', on '.$pagemeta['date'].'</div></a>';
      $articleList .= renderContent($pagemeta['type'], $pcontent);
      $articleList .= '</div>';
    }

    if ($startPos > 0) {
      $articleList .= '<a href="' . $blogpagename . (($startPos > 10) ? '?start=' . ($startPos - 10) : '') . '">Newer articles</a>&nbsp;';
    }

    if (count(array_slice(array_reverse(glob("./article/*.*")), $startPos, 11)) > 10) {
      $articleList .= '<a href="' . $blogpagename . '?start=' . ($startPos + 10) . '">Older articles</a>';
    }

    $content = str_replace('{{article list}}', $articleList, $content);
  }

  return $content;
}

function renderMenu()
{
  $pages = glob("./page/*.*");
  $menu = '';
  foreach($pages as $page)
  {
    list($menupageheader, $menupagecontent, $menupagemeta) = getpage($page);
    if ($menupagemeta['menu'] && $menupagemeta['url']) {
      $menu .= '<li><a href="'.$menupagemeta['url'].'">'.$menupagemeta['title']."</a></li>\n";
    }
  }
  return $menu;
}

function debugHeader($value, $num = null)
{
  global $debug;
  if ($debug) {
    header('X-Void-Debug-' . rand(0, 100) . ': ' . $value);
  }
}

// Get page requested
$requestedpage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$urlChunks = parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);
$parentDir = dirname($urlChunks);
$pageName = trim($parentDir, '/');
if ($pageName === $requestedpage) {
  $requestedpage = 'index';     // check if page is home, there should be a better way to do it!
}
$type = strpos($_SERVER['REQUEST_URI'], 'article') ? 'article' : 'page';

displayCached($parsedHtmlPath, $type, $requestedpage); // Will exit if cached version is available

$pages = glob("./".$type."/*$requestedpage.*");

if ($pages) {
  $pagefilename = $pages[0];
} else {
  $pagefilename = $http404page;
  $type = 'page';
}

list($pageheader, $pagecontent, $pagemeta) = getpage($pagefilename);

// Generate page/article
debugHeader('Page was generated');
ob_start();
include 'template.php';
$parsedHtml = ob_get_clean();

// Save a cached version of the page
if ($cachePages) {
  $file = $pagefilename ."\n". md5_file($pagefilename) ."\n". $parsedHtml;
  file_put_contents($parsedHtmlPath.'/'.$type.'-'.$requestedpage.'.html', $file);
}
// Display
echo $parsedHtml;
