<?php
// User settings
$sitename = 'SomeWebsite';              // Name of site dispayed on every page
$http404page = './page/404.md';         // Page to display when a 404 error is encountered
$parsedHtmlPath = './parsed';           // Location of HTML cache
$useMarkdown = true;                    // Use the Markdown parser?
$defaultPageType = 'md';                // Type to assign page if one isn't given, md, html, txt
$cachePages = true;                     // Cache generated HTML
$debug = false;                          // Enabled debug mode, set to false in production

// Don't edit below here
//
if ($debug) {
  ini_set('display_errors', true);
  ini_set('display_startup_errors', true);
} else {
  error_reporting(0);
}

if ($useMarkdown) {
  require 'vendor/autoload.php';
}

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
    'title' => isset($titleMatch[1]) ? trim($titleMatch[1]) : '',
    'author' => isset($authorMatch[1]) ? trim($authorMatch[1]) : '',
    'date' => isset($dateMatch[1]) ? trim($dateMatch[1]) : '',
    'menu' => isset($menuMatch[1]) ? trim($menuMatch[1]) : '0',
    'url' => isset($urlMatch[1]) ? trim($urlMatch[1]) : '',
    'type' => isset($typeMatch[1]) ? strtolower(trim($typeMatch[1])) : $defaultPageType
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
    // Cache structure, Line 1: Source path, 2: Source hash, 3: Menu hashes, 4: HTML Content
    $file = file_get_contents($basepath.'/'.$type.'-'.$page.'.html');
    $hash = explode("\n", $file, 4);
    $currentHash = md5_file($hash[0]);

    // If the source doesn't match its current form, bad cache
    if ($currentHash != $hash[1]) {
      return false;
    }

    // Check valid menu
    $menu = explode(':', $hash[2]);
    $menuItems = 0;

    $pages = glob("./page/*.*");
    foreach($pages as $page)
    {
      list(, , $menupagemeta) = getpage($page);
      if ($menupagemeta['menu'] && $menupagemeta['url'] && $menupagemeta['title']) {
        $menuItems++;
        if (!in_array(md5_file($page), $menu)) {
          return false;
        }
      }
    }

    if ($menuItems != count($menu)) {
      return false;
    }

    debugHeader('Page was in cache');
    echo $hash[3];
    exit();
  }
}

function renderContent($type, $content)
{
  global $useMarkdown;
  switch ($type) {
    case 'md': // Markdown
      if ($useMarkdown) {
        $md = (new Parsedown())->text($content);
        return parseTags($md);
      } else {
        return $content;
      }
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
    if ($menupagemeta['menu'] && $menupagemeta['url'] && $menupagemeta['title']) {
      $menu .= '<li><a href="'.$menupagemeta['url'].'">'.$menupagemeta['title']."</a></li>\n";
    }
  }

  return $menu;
}

function menuHashes()
{
  $pages = glob("./page/*.*");
  $menuHashes = [];

  foreach($pages as $page)
  {
    list($menupageheader, $menupagecontent, $menupagemeta) = getpage($page);
    if ($menupagemeta['menu'] && $menupagemeta['url'] && $menupagemeta['title']) {
      array_push($menuHashes, md5_file($page));
    }
  }

  return implode(':', $menuHashes);
}

function debugHeader($value, $num = null)
{
  global $debug;
  if ($debug) {
    header('X-Void-Debug-' . rand(0, 100) . ': ' . $value);
  }
}

function getRequestedPage() {
  $requestedpage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  $urlChunks = parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);
  $parentDir = dirname($urlChunks);
  $pageName = trim($parentDir, '/');
  if ($pageName === $requestedpage) {
    $requestedpage = 'index';     // check if page is home, there should be a better way to do it!
  }
  $type = strpos($_SERVER['REQUEST_URI'], 'article') ? 'article' : 'page';
  $pages = glob("./".$type."/*$requestedpage.*");

  if ($pages) {
    $pagefilename = $pages[0];
  } else {
    $pagefilename = $http404page;
    $type = 'page';
  }

  return [$type, $requestedpage, $pagefilename];
}

function generatePage($vars) {
  extract($vars);
  ob_start();
  include 'template.php';
  debugHeader('Page was generated');
  return ob_get_clean();
}

function saveCache($pagefilename, $parsedHtml, $parsedHtmlPath, $type, $requestedpage) {
  $file = $pagefilename ."\n". md5_file($pagefilename) ."\n". menuHashes() ."\n". $parsedHtml;
  file_put_contents($parsedHtmlPath.'/'.$type.'-'.$requestedpage.'.html', $file);
}

list($type, $requestedpage, $pagefilename) = getRequestedPage();
displayCached($parsedHtmlPath, $type, $requestedpage); // Will exit if cached version is available

list($pageheader, $pagecontent, $pagemeta) = getpage($pagefilename);

$parsedHtml = generatePage([
  'sitename' => $sitename,
  'type' => $type,
  'title' => $pagemeta['title'] ? $sitename.' - '.$pagemeta['title'] : $sitename,
  'pagemeta' => $pagemeta,
  'content' => $pagecontent,
  'basepath' => rtrim(dirname(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH)), '/') . '/'
]);

// Save a cached version of the page
if ($cachePages) {
  saveCache($pagefilename, $parsedHtml, $parsedHtmlPath, $type, $requestedpage);
}

// Display
echo $parsedHtml;
