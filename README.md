Minim
=====

**Minim** is a static website creation tool. Want static pages or blog articles? Both are possible with Minim. It was built with simplicity and speed in mind. Minim has on-demand caching that will render and cache a page only when it's used saving precious processing power. When the source for the page or article is changed, Minim will regenerate and recache the HTML for future use.

If you download the source directly from Github, make sure to run `composer install` to get the Markdown and shortcode processor.

Using Minim
-----------

All pages are placed in the `pages` directory. Likewise, all the blog articles are placed in the `articles` directory. Articles are rendered in the blog list by reversed file name. Thus, article file names should start with a number with newer articles having the highest numbers. The file extension does not matter.

All pages/articles start with a metadata section that's located at the very top of the file and between a set of three hyphens "---".

Example:

```
---
TITLE: My Awesome Site
AUTHOR: My Awesome Self
---
```

Everything between the three hyphens is only used by Minim. Everything after the closing three hyphens is considered the page content. The possible tags that can be used in the metadata sections are TITLE, AUTHOR, DATE, MENU, URL, and TYPE. These tags are case insensitive. You may also use your own tags so long as they are made up of letters, numbers, hyphens, or underscores. They can't start with an underscore. Invalid tags are ignored.

- TITLE - The title of the articles displayed in the main content area or the title of page displayed in the browser tab.
- AUTHOR - Author of the article
- DATE - Date the article was created
- MENU - Set this to 1 to have the page placed in the main menu
- URL - Url used for a menu linked page, if omitted, the title is used with hyphens in place of spaces and all lowercase eg: `My Awesome Site` (assuming .md extension) will be `my-awesome-site.md`.
- TYPE - The content type of the file, Markdown (md), HTML, or text (txt), if omitted, the configured default file type is assumed.

See the default pages and articles for examples.

Shortcodes
----------

Minim uses shortcodes similar to Wordpress to create dynamic, pluggable content. Right now the only builtin shortcodes are `[article-list]` which will show a paged article list and `[datetime]` which shows the current date and time.

Configuring Minim
-----------------

The application configuration can be edited in app/config.php.

Plugins
-------

Minim supports simple plugins that implement shortcodes. You can look at the default shortcodes located in the app/plugins directory. A plugin can be a single file located in app/plugins or can be in a subdirectory. If it's in a subdirectory, only a file named `main.php` will be executed. Any other files will need to be included by the plugin itself. Plugins have access to the main Application object by calling `Minim\Application::getInstance()`. Plugins can also register shortcodes:

```php
Shortcode::register('datetime', function(array $options, Config $config, Page $page) {
    return date('Y-m-d H:i:s');
});
```

$options is an array of options parsed from the shortcode. Along with defined arguments are builtins:

- `_raw` - Raw shortcode including content and ending tag
- `_content` - Text inside a paired shortcode, blank if shortcode was self-closing
- `_offset` - Offset in the text where the shortcode was found
- `_code` - The shortcode name itself
- `_length` - The length of the raw shortcode string

$config is the application configuration. You can query for data by calling `$config->get($data)`. If the configuration option isn't available, get() will return null. Custom metadata tags are available as well as the predefined tags.

$page is the page being processed. You can access metadata using `$page->getMetadata($data)`, the type `$page->getType()`, or the content `$page->getContent()` or the unprocessed text `$page->getRaw()`.

Again take a look at the example plugins. The article-list plugin uses all these arguments.

Credit
------

**Minim** uses the [Parsedown](http://github.com/erusev/parsedown) library, licensed under MIT license.

**Minim** is based on [Void](http://www.thisisvoid.org/) written by Joseph Ernest ([@JosephErnest](http:/twitter.com/JosephErnest)).

License
-------

MIT license
