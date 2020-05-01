<?php

/**
 * The file that defines cache tag behavior.
 *
 * @link    https://developer.akamai.com
 * @since   0.7.0
 *
 * @package Akamai
 */

/**
 * The core plugin class for managing cache tag behavior.
 *
 * Singleton for defining cache tag behavior. This means: basic rules for
 * generating cache tags (ie surrogate keys), and determining which tags are
 * relevant to (emitted as headers or sent in a purge request) for a given post.
 *
 * API TO IMPLEMENT:
 *
 * get_tags_for_cache_header( ??? ) : [string]
 * get_tags_for_purge_term( ??? )   : [string]
 * get_tags_for_purge_author( ??? ) : [string]
 *
 * @since   0.7.0
 * @package Akamai
 */
class Akamai_Cache_Tags {
    /**
     * The one instance of Akamai_Cache_Tags.
     *
     * @since 0.7.0
     * @var   Akamai_Cache_Tags
     */
    private static $instance;

    /**
     * The template types to always (usually) include when purging.
     *
     * @since 0.7.0
     * @var   array
     */
    public static $always_purged_templates = [
        'post',
        'home',
        'feed',
        '404',
    ];

    /**
     * Standard tag codes for types of tags.
     *
     * @since 0.7.0
     * @var   array
     */
    public static $default_codes = [
        'post'      => 'p',
        'term'      => 't',
        'author'    => 'a',
        'template'  => 'tm',
        'multisite' => 's',
    ];

    /**
     * Instantiate or return the one Akamai_Cache_Tags instance.
     *
     * @since  0.7.0
     * @param  string $plugin The Akamai Plugin class instance.
     * @return Akamai_Cache_Tags
     */
    public static function instance( $plugin ) {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self( $plugin );
        }
        return self::$instance;
    }

    /**
     * A reference to the Akamai Plugin class instance.
     *
     * @since 0.7.0
     * @var   string $plugin The Akamai Plugin class instance.
     */
    public $plugin;

    /**
     * ...
     *
     * @since 0.7.0
     * @param string $plugin The Akamai class instance.
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * A helper for standardizing / customizing tag generation.
     *
     * @since  0.7.0
     * @param  string $name  The code to identify the type of tag.
     * @param  string $value The value (usually an int ID) for the tag.
     * @return string A formatted tag part (code-value).
     */
    public function tag_part( $name, $value ) {
        $code = apply_filters( "akamai_{$name}_code", static::$default_codes[$name] );
        return sprintf( '%s-%s', $code, $value );
    }

    /**
     * A helper for standardizing / customizing tag generation.
     *
     * @since  0.7.0
     * @return string The current unique site code.
     */
    public function get_site_code() {
        $site_code = $this->plugin->setting( 'unique-sitecode' );
        if ( $site_code === '' ) {
            return urlencode( $this->plugin->get_hostname() );
        }
        return $site_code;
    }

    /**
     * A helper for standardizing / customizing tag generation.
     *
     * @since  0.7.0
     * @return string The unique site tag(-part) for the current site.
     *                Same as the site code, unless multisite, then it's
     *                unique to the current site/blog.
     */
    public function get_site_tag() {
        if ( is_multisite() ) {
            $tag_part = $this->tag_part( 'multisite', get_current_blog_id() );
            return $this->get_site_code() . '-' . $tag_part;
        } else {
            return $this->get_site_code();
        }
    }

    /**
     * Post tag helper to generate standardized, unique tags.
     *
     * @since  0.7.0
     * @param  \WP_Post|int $value The post or post id to generate a tag for.
     * @return string The tag.
     */
    public function get_post_tag( $value ) {
        if ( $value instanceof \WP_Post ) {
            $value = $value->ID;
        }
        $tag_part = $this->tag_part( 'post', (string) $value );
        return $this->get_site_tag() . '-' . $tag_part;
    }

    /**
     * Term tag helper to generate standardized, unique tags.
     *
     * @since  0.7.0
     * @param  \WP_Term|int $value The term or term id to generate a tag for.
     * @return string The tag.
     */
    public function get_term_tag( $value ) {
        if ( $value instanceof \WP_Term ) {
            $value = $value->term_id;
        }
        $tag_part = $this->tag_part( 'term', (string) $value );
        return $this->get_site_tag() . '-' . $tag_part;
    }

    /**
     * User (author) tag helper to generate standardized, unique tags.
     *
     * @since  0.7.0
     * @param  \WP_User|int $value The user or user id to generate a tag for.
     * @return string The tag.
     */
    public function get_author_tag( $value ) {
        if ( $value instanceof \WP_User ) {
            $value = $value->id;
        }
        $tag_part = $this->tag_part( 'author', (string) $value );
        return $this->get_site_tag() . '-' . $tag_part;
    }

    /**
     * Template tag helper to generate standardized, unique tags.
     *
     * @since  0.7.0
     * @param  string $value The template type to generate a tag for.
     * @return string The tag.
     */
    public function get_template_tag( $template_type ) {
        $tag_part = $this->tag_part( 'template', $template_type );
        return $this->get_site_tag() . '-' . $tag_part;
    }

    /**
     * Formats the always purged template types as tags, and then filters
     * to allow more.
     *
     * @since  0.7.0
     * @return array The list of built tags for always purged types.
     */
    public function always_purged_tags() {
        foreach ( static::$always_purged_templates as $template_type ) {
            $tags[] = $this->get_template_tag( $template_type );
        }
        return apply_filters( 'akamai_always_purged_tags', $tags, static::$instance );
    }

    /**
     * Builds the always cached tags (associated with the site).
     *
     * @since  0.7.0
     * @return array The list of always cached tags.
     */
    public function always_cached_tags() {
        $tags = [ $this->get_site_code() ];
        if ( is_multisite() ) {
            $tags[] = $this->get_site_tag();
        }
        return apply_filters( 'akamai_always_cached_tags', $tags, static::$instance );
    }

    /**
     * Get the given post's author tag, wrapped in an array for simplicity's
     * sake.
     *
     * @since  0.7.0
     * @param  \WP_Post $post The post to search for related author information.
     * @return array The author tag(s).
     */
    public function related_author_tags( $post ) {
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }
        if ( ! empty( $post ) ) {
            $author_id = absint( $post->post_author );
            if ( $author_id > 0 ) {
                return [ $this->get_author_tag( $author_id ) ];
            }
        }
        return [];
    }

    /**
     * Get the term link pages for all terms associated with a post
     * every taxonomy. Filter taxonomies for fun and profit.
     *
     * @since  0.7.0
     * @param  \WP_Post $post The post to search for related term information.
     */
    public function related_term_tags( $post ) {
        $tags = [];

        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }
        if ( ! empty( $post ) ) {
            $taxonomies = apply_filters( 'akamai_related_taxonomies', (array) get_taxonomies() );

            foreach ( $taxonomies as $taxonomy ) {
                $terms = wp_get_post_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );

                if ( is_array( $terms ) ) {
                    foreach ( $terms as $term ) {
                        if ( $term ) {
                            $tags[] = $this->get_term_tag( $term );
                        }
                    }
                }
            }
        }
        return $tags;
    }

    /**
     * Get a list of tags to send to Akamai for purging when a post needs
     * to be purged.
     *
     * @since 0.7.0
     * @param \WP_Post $post    The post to generate purge tags for.
     * @param bool     $related Optional. Also purge related posts, terms and
     *                          authors. Defaults to true.
     * @param bool     $always  Optional. Also purge the always-purged tags.
     *                          Defaults to true.
     */
    public function get_tags_for_purge_post( $post, $related = true, $always = true ) {
        $tags = [];

        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }
        if ( empty( $post ) ) {
            return apply_filters( 'akamai_purge_post_tags', $tags, $post, static::$instance );
        }
        $tags[] = $this->get_post_tag( $post );

        if ( $related ) {
            $r_posts = apply_filters(
                'akamai_purge_post_related_posts', [], $post, static::$instance );
            $r_terms = apply_filters(
                'akamai_purge_post_related_terms', $this->related_term_tags( $post ), $post, static::$instance );
            $r_authors = apply_filters(
                'akamai_purge_post_related_authors', $this->related_author_tags( $post ), $post, static::$instance );
            $tags = array_merge( $tags, $r_posts, $r_terms, $r_authors );
        }

        if ( $always ) {
            $tags = array_merge( $this->always_purged_tags(), $tags );
        }

        return apply_filters( 'akamai_purge_post_tags', $tags, $post, static::$instance );
    }

    /**
     * Get the purge this specific site tag, in a multisite setup this
     * is a bit more targeted than purging all.
     *
     * @since  0.7.0
     * @return array List of tags necessary to purge the specifically
     *               current multisite site.
     */
    public function get_tags_for_purge_multisite_site() {
        return [ $this->get_site_tag() ];
    }

    /**
     * Get the purge ALL tag: ie the unique site code.
     *
     * @since  0.7.0
     * @return array List of tags necessary to purge the entire site
     *               (or all sites in multisite).
     */
    public function get_tags_for_purge_all() {
        return [ $this->get_site_code() ];
    }

}
