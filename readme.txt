=== Related Posts Query Block ===

Contributors:      WordPress Telex
Tags:              block, related posts, query, taxonomy, categories
Tested up to:      6.8
Stable tag:        0.1.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Displays related posts that share the same categories and taxonomies as the current post.

== Description ==

The Related Posts Query Block works like a Query Loop — it automatically detects the categories and custom taxonomies of the current post and queries other posts that share those same terms.

You can add any blocks inside it to design the layout of each related post, just like the core Query Loop block. Use core blocks like Post Title, Post Featured Image, Post Date, and Post Excerpt to build your template.

**Features:**

* Works like the core Query Loop — add inner blocks to design each post's layout
* Automatically detects all categories and custom taxonomies of the current post
* Queries posts sharing the same terms
* Excludes the current post from results
* Configurable number of posts to display (1-12)
* Responsive grid layout with 1-4 column options
* Each inner block receives the related post's context (postId, postType)
* Clean, flexible design that adapts to your theme

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/telex-related-posts-query` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Add the "Related Posts Query" block to any post template or individual post.
4. Add inner blocks (Post Title, Post Featured Image, etc.) to design the layout.

== Frequently Asked Questions ==

= How do I customize the layout? =

Add any blocks inside the Related Posts Query block. Core blocks like Post Title, Post Featured Image, Post Date, and Post Excerpt will automatically display data from each related post.

= Does this work with custom post types? =

Yes, the block queries posts of the same post type as the current post.

= What happens if there are no related posts? =

The block will display nothing on the frontend if no related posts are found.

= Can I control which taxonomies are used? =

The block automatically uses all public taxonomies assigned to the current post, including categories, tags, and any custom taxonomies.

== Changelog ==

= 0.1.0 =
* Initial release
