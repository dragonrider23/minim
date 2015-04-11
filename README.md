Minim
=====

**Minim** is a static website creation tool. Want static pages or blog articles? Both are possible with Minim. It was built with simplicity and speed in mind. Minim has on-demand caching that will render and cache a page only when it's used saving precious processing power. When the source for the page or article is changed, Minim will regenerate and recache the HTML for future use.

Minim relies on the Parsedown library for Markdown processing. If you download the source directly from Github, make sure to run `composer install` to get Parsedown. Otherwise you will get an error. If you do not wish to use Markdown at all, you can comment out the require statement at the top of index.php and not worry about Composer.

Using Minim
-----------

All pages are placed in the `pages` directory. Likewise, all the blog articles are placed in the `articles` directory. Articles are rendered in the blog list by reversed file name. Thus, article file names should start with a number with newer articles having the highest numbers. The file extenstion does not matter.

All pages/articles start with a metadata section that's located at the very top of the file and between a set of three hyphens "---".

Example:

    ---
    TITLE: My Awesome Site
    AUTHOR: My Awesome Self
    ---

Everything between the three hyphens is only used by Minim. Everything after the closing three hyphens is considered the page content. The possible tags that can be used in the metadata sections are TITLE, AUTHOR, DATE, MENU, URL, and TYPE.

- TITLE - The title of the articles displayed in the main content area or the title of page displayed in the browser tab.
- AUTHOR - Author of the article
- DATE - Date the article was created
- MENU - Set this to 1 to have the page placed in the main menu
- URL - Url used for a menu linked page, if omitted, the title is used with hyphens in place of spaces and all lowercase eg: `My Awesome Site` (assuming .md extension) will be `my-awesome-site.md`.
- TYPE - The content type of the file, Markdown (md), HTML, or text (txt), if omitted, the configured default file type is assumed.

See the default pages and articles for examples.

Configuring Minim
-----------------

Minim has a few settings that should be changed before being used. Open the index.php file and at the top you'll find the settings you can change. Here you can set whether or not caching is enabled, the name of your site, the location of cached html files, the 404 file not found page, and the default file type.

Credit
------

**Minim** uses the [Parsedown](http://github.com/erusev/parsedown) library, licensed under MIT license.

**Minim** is based on [Void](http://www.thisisvoid.org/) written by Joseph Ernest ([@JosephErnest](http:/twitter.com/JosephErnest)).

License
-------

MIT license
