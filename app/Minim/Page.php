<?php
namespace Minim;

class Page
{
    private $path = '';
    private $type = '';
    private $metadata = [];
    private $content = '';
    private $parsed = '';
    private static $defaultType = 'txt';

    public function __construct($path, $type = '')
    {
        $this->path = $path;
        $this->type = $type;
        $this->load();
    }

    protected function load()
    {
        $this->metadata = [
            'title' => '',
            'author' => '',
            'date' => '',
            'menu' => 'no',
            'shortcodes' => 'yes',
            'url' => '',
            'type' => self::$defaultType,
        ];

        $pagestr = file_get_contents($this->path);
        if (substr($pagestr, 0, 3) !== '---') {
            return;
        }

        list(, $pageheader, $pagecontent) = explode('---', $pagestr, 3);   // split into 3 parts : above the first --- (blank), metadata, content
        preg_match_all("~^((?!_)[\w-]+):\s*(.*)$~m", $pageheader, $matches, PREG_SET_ORDER);
        foreach($matches as $match) {
            $this->metadata[mb_strtolower($match[1])] = trim($match[2]);
        }
        $this->metadata['url'] = $this->metadata['url'] ?: str_replace(' ', '-', strtolower($this->metadata['title']));
        $this->content = $pagecontent;
        return;
    }

    public static function setDefaultType($type)
    {
        self::$defaultType = $type;
    }

    public function render()
    {
        if ($this->metadata['shortcodes'] !== 'no') {
            $this->parsed = Shortcode::process($this->getContent(), Application::getInstance()->getConfig(), $this);
        }

        switch (mb_strtolower($this->metadata['type'])) {
            case 'md': // Fallthrough
            case 'markdown': // Markdown
                $this->parsed = (new \Parsedown())->text($this->getContent());
                return $this->parsed;

            case 'txt': // Fallthrough
            case 'text':// Text document
                $this->parsed = trim($this->getContent());
                $this->parsed = nl2br(htmlentities($this->getContent()));
                return $this->parsed;

            default: // Everything else
                return $this->content;
        }
    }

    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }
        if (!array_key_exists($key, $this->metadata)) {
            return null;
        }
        return $this->metadata[$key];
    }

    public function getRaw()
    {
        return $this->content;
    }

    public function getContent()
    {
        if ($this->parsed === '') {
            return $this->getRaw();
        }
        return $this->parsed;
    }

    public function getType()
    {
        return $this->type;
    }
}
