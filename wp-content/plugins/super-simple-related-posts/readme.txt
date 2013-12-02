=== Super Simple Related Posts ===
Contributors:      McGuive7, MIGHTYminnow
Donate link:       http://mightyminnow.com
Tags:              related, posts, content, category, tag, taxonomy, widget
Requires at least: 3.0
Tested up to:      3.7
Stable tag:        1.2
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

A super simple widget to output related posts based on categories, tags, or custom taxonomies.

== Description ==
Super Simple Related Posts gives you both a widget and a shortcode to output lists of posts related to the current post. You get to decide how the posts are related (categories, tags, custom taxonomies), what to show (posts, pages, custom post types), and a whole lot more. The widget is configurable via **Appearance > Settings** and more info on shortcode usage is provided below.

A [**MIGHTYminnow**](http://mightyminnow.com) plugin.

= Features =
There are tons of related posts plugins out there - here's why Super Simple Related Posts is special:

* **Speed**  
  Unlike other related posts plugins, Super Simple Related Posts is super fast. It doesn't run resource-intensive algorithms on your database and it doesn't bog down your server. If you're looking for a plugin that'll run through all your content and match related posts word-for-word, there are plenty of good ones out there. If you're looking for a simple, lightning-fast related posts plugin, you're in the right place.

* **Simplicity**  
  As it's name implies, Super Simple Related Posts keeps things as straightforward as possible. This means that you get a simple widget with easily configurable settings to let you do what you gotta' do.


= Settings = 
Whether you use widgets or shortcakes, you can easily configure Super Simple Related Posts with the following settings. These settings are directly configurable in each widget, and can also be controlled with attributes when using shortcodes (for more on shortcode settings, see the "Shortcode" section below).

* **Title**  
  The title of your widget.

* **Hide Title**
  Give you the ability to hide the widget title, particularly useful when using the shortcode.

* **Post Types**  
  The post types you would like to output, and the order in which you want to display them. This works great with all custom post types.Note: you must specify at least one post type, otherwise no content will be output.

* **Show Posts Related By**  
  Choose the method by which you want to show related posts. You can choose categories, tags, or any custom taxonomy. This field automatically populates with all available possibilities.

* **Order By**  
  The criteria by which to order the related posts (e.g., title, date created, author, date modified, etc).

* **Order**  
  Ascending or descending - take your pick.

* **Number of Posts**  
  The total number of posts you would like to show in each section.

* **What to do if there are no related posts**  
  Choose how you would like to handle the situations in which no posts are found. Either hide the contents or display a custom message.

* **Headings**  
  Options to link and/or hide post type and category/tag/taxonomy headings.

* **Before/After HTML**  
  A handy feature to let you add custom HTML before and after the widget, in case you want any intro or concluding text.


= Shortcode = 
In addition to using SSRP widgets, you can also use the `[ssrp]` shortcode. Here's an example:

`[ssrp title="My Related Posts" post_types="post, page" number_of_posts="3"]`

Here is a list of the available shortcode attributes, which correlate to the settings listed above. Each attribute is listed along with its potential values in quotations, and any default values are listed in parenthesis.

* `title="Any Text"` (Related Posts)
* `hide_title="1"`
* `post_types="Comma-separated list of post type slugs, in order. E.g. post, page"`
* `taxonomy="Taxonomy slug by which to detemine relatedness. E.g. tag"` (category)
* `orderby="title|name|date|modified|menu_order|author|ID|parent|rand|comment_count"` (date)
* `order="ASC|DESC"` (DESC)
* `number_of_posts="Any number"` (-1, unlimited)
* `no_posts_action="hide|message"` (hide)
* `no_posts_message="Any text"` (No posts found)
* `post_heading_links="1"`
* `hide_post_type_headings="1"`
* `term_heading_links="1"`
* `hide_term_headings="1"`
* `before_HTML="Any HTML/text"`
* `after_HTML="Any HTML/text"`


= Filters = 
Super Simple Related Posts also provides you with a handy set of filters you can use for your own development and customization:

* **`ssrp_post_title( $post_title, $post_id )`**  
  Applied to the title of each related post, within the surrounding `<a></a>` tags.

* **`ssrp_post_link( $post_link, $post_id )`**  
  Applied to the linked title of each related post, outside the surrounding `<a></a>` tags. This is useful for adding a prefix/font-icon before the linked title.

* **`ssrp_posts_list( $post_ul, $post_type_object, $taxonomy_term_object )`**  
  Applied to the `<ul>` lists of related posts for a given custom post type and category/tag/taxonomy.

* **`ssrp_taxonomy_term_heading( $taxonomy_term_name, $taxonomy_term_object )`**  
  Applied to the category/tag/taxonomy term headings, within the surrounding `<a></a>` tags if the headings are set to be linked.

* **`ssrp_post_type_heading( $post_type_name, $post_type_object )`**  
  Applied to the post type headings, within the surrounding `<a></a>` tags if the headings are set to be linked.


Banner photo by [Susannah Kay](http://susannahkay.com).


== Installation ==

= Manual Installation =

1. Upload the entire `/super-simple-related-posts-widget` directory to the `/wp-content/plugins/` directory.
2. Activate Super Simple Related Posts Widget through the 'Plugins' menu in WordPress.


== Screenshots ==

1. The Super Simple Related Posts widget.
2. Sample output showing posts, pages, and custom post type 'Case Studies' related by Categories.

== Changelog ==

= 1.2 =
* Added shortcode functionality!
* Add ability to hide the title (hide_title).

= 1.1 =
* Added missing closting bracket } to main PHP file that was causing PHP end of file error and duplicated post type output.

= 1.0 =
* First release

== Upgrade Notice ==

= 1.2 =
* Added shortcode functionality!
* Add ability to hide the title (hide_title).

= 1.1 =
* Added missing closting bracket } to main PHP file that was causing PHP end of file error and duplicated post type output.

= 1.0 =
First Release