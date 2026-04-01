=== NExT Related Posts Query Block ===

Contributors:      NExT-Season
Tags:              block, related posts, query, taxonomy, custom post type
Tested up to:      6.8
Stable tag:        0.1.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Displays related posts based on shared taxonomy terms. Supports custom post types and custom taxonomies with a Query Loop-style inner block template.

== Description ==

The NExT Related Posts Query Block works like the core Query Loop — it queries posts that share taxonomy terms with the current post, and lets you design the layout of each result using inner blocks.

**Features:**

* Query Loop pattern — uses a Post Template inner block to define each post's layout
* Automatically detects categories and custom taxonomies of the current post
* Cross-post-type matching — select a target post type to find related posts of a different type
* When a post type is selected, that type's own taxonomies are shown for selection
* Taxonomy selection resets automatically when post type changes
* Grid and list layout toggle with 1–4 column grid support
* Block gap (spacing) support — reflected directly on the frontend
* Responsive: single column below 600px
* Configurable number of posts (1–12); maximum enforced server-side
* Excludes the current post from results
* Each inner block receives postId and postType context

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/next-related-posts-query/`.
2. Activate the plugin through the Plugins screen in WordPress.
3. Add the "NExT Related Posts Query" block to any post template or individual post.
4. Customize the inner Post Template block to design the layout.

== Frequently Asked Questions ==

= How do I customize the layout? =

Select the NExT Post Template block inside the query block. Use the toolbar to switch between grid and list view, and the inspector to set columns and block gap. Add or replace inner blocks (Post Title, Post Featured Image, Post Date, Post Excerpt, etc.) to build your template.

= Does this work with custom post types? =

Yes. Set Post Type to "Auto" to match the current post's type automatically, or select a specific custom post type to query across types.

= How do I use custom taxonomies for cross-type matching? =

Select the target post type. The taxonomy selector will show that post type's taxonomies. Check the taxonomy you want to use for matching. The block will find posts of the target type that share the same terms as the current post in that taxonomy.

= What happens if there are no related posts? =

The block outputs nothing on the frontend. In the editor, a placeholder message is shown.

= Why isn't block gap reflected on the frontend? =

Select the NExT Post Template block and set "Block Spacing" in the inspector. The value is written directly as an inline style, so it applies regardless of theme or WordPress version.

== Changelog ==

= 0.1.0 =
* Initial release
* Query Loop pattern with next/post-template inner block
* Cross-post-type and custom taxonomy support
* Grid/list layout toggle with blockGap support
* REST API endpoint for editor preview
