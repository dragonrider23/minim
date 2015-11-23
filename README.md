Minim
=====

**Minim** is a website creation tool that allows for easy creation of blog articles and static pages. It was built with simplicity and speed in mind. Minim offers on-demand caching that will render and cache a page only when it's used saving precious processing power. When the source for the page or article is changed, Minim will regenerate and recache the HTML for future use.

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

Everything between the three hyphens is only used by Minim. Everything after the closing three hyphens is considered the page content. These tags are case insensitive. You may also use your own tags so long as they are made up of letters, numbers, hyphens, or underscores. They can't start with an underscore. Invalid tags are ignored.

Default tags:

- Title - The title of the articles displayed in the main content area or the title of page displayed in the browser tab.
- Author - Author of the article
- Date - Date the article was created
- Menu - Set this to yes to have the page placed in the main menu
- Url - URL used for a menu linked page, if omitted, the title is used with hyphens in place of spaces and all lowercase eg: `My Awesome Site` (assuming .md extension) will be `my-awesome-site.md`.
- Type - The content type of the file, Markdown (md), HTML, or text (txt), if omitted, the configured default file type is assumed.
- Shortcodes - Disable shortcodes when set to "no".
- Cache - Disables caching for the page when set to "no".

See the default pages and articles for examples.

Caching
-------

Limitations on caching:

- The cache is not invalidated when a new page is added that would change the site menu. You will need to clear the entire cache for the new page to show in the menu. This issue should be resolved soon.

- Results of shortcodes will be saved in the cache. Meaning if a shortcode dynamically generates content on each page view, the first generation will be shown on subsequent requests. If you have shortcodes like this, make sure to add `cache: no` in that pages metadata. NOTE: This includes the `{{article-list}}` shortcode.

Shortcodes
----------

Minim uses shortcodes similar to Wordpress to create dynamic, pluggable content. The following shortcodes are available by default:

- `{{article-list}}` - Show a paged list of articles from newest, to oldest
- `{{datetime format="Y-m-d H:i:s"}}` - Show the current time using format. Format is optional.
- `{{site config=siteurl page=title}}` - Show data from the configuration or page. Only one of the parameters can be used. If config is set, page will be ignored. E.g. `{{site config=siteurl}}` and `{{site page=title config=siteurl}}` will both be replaced with the site URL. If you wanted the page title it must be `{{site page=title}}`.

NOTE: Shortcodes are processed before anything else. Meaning shortcodes are available for any page type and plugins may return Markdown which will be processed afterwards if the page type is markdown.

Configuring Minim
-----------------

The application configuration can be edited in app/config.php.

Plugins
-------

Minim supports simple plugins that implement shortcodes. You can look at the default shortcodes located in the app/plugins directory. A plugin can be a single file located in `app/plugins` or it can be in a subdirectory. If it's in a subdirectory, only a file named `main.php` will be executed. Any other files will need to be included by the plugin itself. Plugins have access to the main Application object by calling `Minim\Application::getInstance()`. Plugins can also register shortcodes:

```php
Shortcode::register('datetime', function(array $options, Config $config, Page $page) {
    $format = array_key_exists('format', $options) ? $options['format'] : 'Y-m-d H:i:s';
    return date($format);
});
```

$options is an array of options parsed from the shortcode. Along with defined arguments are builtins:

- `_raw` - Raw shortcode including content and ending tag
- `_content` - Text inside a paired shortcode, blank if shortcode is self-closing
- `_offset` - Offset in the text where the shortcode was found
- `_code` - The shortcode name itself
- `_length` - The length of the raw shortcode string

$config is the application configuration. You can query for data by calling `$config->get($data)`. If the configuration option isn't available, get() will return null. Custom metadata tags are available as well as the predefined tags. Tags are case insensitive.

$page is the page being processed. You can access metadata using `$page->getMetadata($data)`, the type `$page->getType()`, or the content `$page->getContent()` or the unprocessed text `$page->getRaw()`.

Again take a look at the example plugins. The article-list plugin uses all these arguments.

Credit
------

**Minim** uses the [Parsedown](http://github.com/erusev/parsedown) library, licensed under MIT license.

**Minim** was originally based on [Void](http://www.thisisvoid.org/) written by Joseph Ernest ([@JosephErnest](http:/twitter.com/JosephErnest)).

License
-------

MIT license
