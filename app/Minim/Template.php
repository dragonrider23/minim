<?php
namespace Minim;

class Template
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function render(Page $page, $template)
    {
        $vars = [
            'sitename' => $this->config->get('sitename'),
            'title' => $page->getMetadata('title') ? $this->config->get('sitename').' - '.$page->getMetadata('title') : $this->config->get('sitename'),
            'basepath' => htmlspecialchars($this->config->get('siteurl'), ENT_QUOTES, 'UTF-8'),
            'menu' => Application::getInstance()->renderMenu(),
        ];
        extract($vars);
        ob_start();
        include $template;
        return ob_get_clean();
    }

    public function display(Page $page)
    {
        echo $this->render($page);
    }
}
