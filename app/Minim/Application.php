<?php
namespace Minim;

class Application
{
    private $config;
    private static $instance;

    private function __construct(array $config)
    {
        $config['siteurl'] = $this->buildUrl();
        $this->config = new Config($config);
    }

    public static function getInstance(array $config = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function run()
    {
        if ($this->config->get('debug')) {
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
        } else {
            error_reporting(0);
        }

        $this->loadPlugins();
        list($type, $requestedpage, $pagefilename) = $this->getRequestedPage();

        if ($this->config->get('cachePages')) {
            $cache = new Cacher($this->config->get('cacheDir'));
            if ($cache->displayCachedContent($type, $requestedpage) === true) {
                exit(0);
            }
        }

        Page::setDefaultType($this->config->get('defaultPageType'));
        $page = new Page($pagefilename, $type);
        $template = new Template($this->config);

        $parsedHtml = $template->render($page, '../app/template.php');

        // Save a cached version of the page
        if ($this->config->get('cachePages') && $pagefilename !== $this->config->get('http404page')) {
            $cache->cacheContent($pagefilename, $parsedHtml, $type, $requestedpage);
        }

        // Display
        echo $parsedHtml;
    }

    protected function loadPlugins()
    {
        Shortcode::init();
        foreach(glob('../app/plugins/*.php') as $plugin) {
            include $plugin;
        }
        foreach(glob('../app/plugins/*/main.php') as $plugin) {
            include $plugin;
        }
    }

    public function renderMenu()
    {
        $pages = glob("{$this->config->get('pageDir')}/*.*");
        $menu = '';

        foreach($pages as $page) {
            $pageObj = new Page($page);
            if ($pageObj->getMetadata('menu') === 'yes' && $pageObj->getMetadata('url') && $pageObj->getMetadata('title')) {
                $menu .= '<li><a href="'.$pageObj->getMetadata('url').'">'.$pageObj->getMetadata('title')."</a></li>\n";
            }
        }

        return $menu;
    }

    protected function getRequestedPage()
    {
        $requestURI = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $requestedpage = mb_substr($requestURI, mb_strlen(dirname($_SERVER['PHP_SELF'])));
        $requestedpage = ($requestedpage === '/') ? 'index' : ltrim($requestedpage, '/');
        $type = mb_strpos($_SERVER['REQUEST_URI'], 'article') ? 'article' : 'page';

        if ($type === 'article') {
            $requestedpage = mb_substr($requestedpage, strlen('/article'));
            $pages = glob("{$this->config->get('articleDir')}/$requestedpage.*");
        } else {
            $pages = glob("{$this->config->get('pageDir')}/$requestedpage.*");
        }

        if ($pages) {
            $pagefilename = $pages[0];
        } else {
            $pagefilename = $this->config->get('http404page');
            $type = 'page';
        }
        return [$type, $requestedpage, $pagefilename];
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function buildUrl()
    {
        // Protocol
        $url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        // Basename
        $url .= '://'.$_SERVER['SERVER_NAME'];
        // Port if needed
        $url .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? '' : ':'.$_SERVER['SERVER_PORT'];
        // Directory
        // Windows' directory separate is \ instead of HTTP/UNIX's /, replace to make sure
        $url .= rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/').'/';
        return $url;
    }
}
