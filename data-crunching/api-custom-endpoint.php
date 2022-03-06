<?php
/**
 * The Custom Endpoint
 *
 * Create the custom endpoint and add the data for Boomerang API.
 *
 * @package    boomerang_api
 * @subpackage boomerang_api/includes
 * @link       https://www.sage.com/en-us/blog/api/v1
 * @since      0.1
 * @author     Yorick Brown
 */

/**
 * Define the custom endpoint content.
 *
 * Add the route for the Boomerang API Custom Endpoint and generate
 * the necessary data for the frontend.
 *
 * @package    boomerang_api
 * @subpackage boomerang_api/includes
 * @since      0.1
 * @author     Yorick Brown
 */
class Boomerang_API_Custom_Endpoint {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options name prefix for Boomerang API
	 *
	 * @since  0.1
	 * @access private
	 * @var    string  $option_name Option name prefix for Boomerang API
	 */
	private $option_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.1
	 * @param string $plugin_name        The name of this plugin.
	 * @param string $version            The version of this plugin.
	 * @param string $option_name        The option prefix for this plugin.
	 */
	public function __construct( $plugin_name, $version, $option_name ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->option_name = $option_name;

	}

	/**
	 * Admin nag message is WP API not enabled.
	 *
	 * @since    0.1
	 */
	public function boomerang_api_nag_message() {

		global $wp_version;

		// WP v4.7 was the first WP version with the API fully baked in :).
		if ( $wp_version >= 4.7 ) {

			return;

		} elseif ( is_plugin_active( 'WP-API-develop/plugin.php' ) || is_plugin_active( 'rest-api/plugin.php' ) || is_plugin_active( 'WP-API/plugin.php' ) ) {

				return;

		} else { ?>

			<div class="update-nag notice">

				<p>
					<?php __( 'To use <strong>Boomerang API</strong>, you need to update to the latest version of WordPress (version 4.7 or above). To use an older version of WordPress, you can install the <a href="https://wordpress.org/plugins/rest-api/">WP API Plugin</a> plugin. However, we&apos;d strongly advise youto update WordPress.', 'boomerang-api' ); ?>
				</p>

			</div>

			<?php
		}

	}

	/**
	 * API Route Constructor.
	 *
	 * @since    0.1
	 */
	public function boomerang_custom_api_route_constructor() {

		register_rest_route(
			'/v1',
			'/totals',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'boomerang_custom_api_endpoint_totals' ),
				'permission_callback' => array( $this, 'check_api_secret_key' ),
			)
		);
	}

    /**
	 * @api {get} /totals Retrieve post totals.
	 * @apiName GetTotalsPosts
	 * @apiGroup Posts
	 *
	 * @apiDescription The totals endpoint returns the totals of pages generated from posts, amp posts, pages, taxonomy terms, and authors with posts.
	 * The response includes counts for blog posts, amp posts, pages, glossary posts, categories, tags, custom taxonomies, and authors, and further granular counts and details for posts, taxonomies and authors.
	 *
	 * @apiExample {http} Overview:
	 * https://www.sage.com/en-us/blog/api/v1/totals?key=35328fcd1b8cf9e101fc0e398de0be08
	 *
	 * @apiSuccess {Object} totals an array of all public sites in network and an array of counts for blog posts, pages, glossary posts, categories, tags, custom taxonomies, and authors, plus the total of all pages generated from these.
	 *
	 * @apiSuccessExample {json} Overview - Success-Response (example):
	 *     HTTP/1.1 200 OK
	 * {
	 *   "sites": [
	 *     "/en-gb/blog/",
	 *     "/en-us/blog/",
	 *     "/fr-fr/blog/",...
	 *   ],
	 *   "published blog posts": "789",
	 *   "published amp posts": "789",
	 *   "published glossary posts": "111",
	 *   "published pages + home page + glossary index page": 3,
	 *   "number of categories": 8,
	 *   "number of tags": 69,
	 *   "number of business types": 3,
	 *   "number of industries": 9,
	 *   "number of authors": 151,
	 *   "total all pages": 1142
	 * }
	 *
	 * @apiExample {http} Example usage - query taxonomy-data -  granular data:
	 * https://www.sage.com/en-us/blog/api/v1/totals?key=35328fcd1b8cf9e101fc0e398de0be08&query=taxonomy-data
	 *
	 * @apiSuccess {Object} taxonomy granular counts and details for taxonomies and authors.
	 *
	 * @apiSuccessExample {json} Taxonomy Data - Success-Response (example):
	 *     HTTP/1.1 200 OK
	 * {
	 *   "categories": [
	 *       {
	 *           "ID": 33,
	 *           "category": "Business planning",
	 *           "count": 11
	 *       },
	 *       {
	 *           "ID": 34,
	 *           "category": "Business process",
	 *           "count": 7
	 *       },...
	 *   ],
	 *  "tags": [
	 *      {
	 *          "ID": 50,
	 *          "tag": "AI",
	 *          "count": 28
	 *      },
	 *      {
	 *          "ID": 18,
	 *          "tag": "Alternative finance",
	 *          "count": 31
	 *      },...
	 *  ],
	 *  "business types": [
	 *      {
	 *          "name": "Small businesses",
	 *          "count": 563
	 *      },
	 *      {
	 *          "name": "Medium businesses",
	 *          "count": 402
	 *      },
	 *      {
	 *          "name": "Accountants",
	 *          "count": 93
	 *      }
	 *  ],
	 *  "industries":[
	 *      {
	 *          "name": "Chemical",
	 *          "count": 2
	 *      },
	 *      {
	 *          "name": "Construction",
	 *          "count": 8
	 *      },...
	 *  ],
	 * "authors":[
	 *      {
	 *          "name": "Adam Prince",
	 *      "count": "4"
	 *      },
	 *      {
	 *          "name": "Alan Laing",
	 *          "count": "3"
	 *      },...
	 *  ]
	 * }
	 *
	 * @apiExample {http} Example usage - query post details -  granular data:
	 * https://www.sage.com/en-us/blog/api/v1/totals?key=35328fcd1b8cf9e101fc0e398de0be08&query=post-details
	 *
	 * @apiSuccess {Object} posts, post details - granular counts and details for blog posts and glossary posts
	 *
	 * @apiSuccessExample {json} Post details - Success-Response (example):
	 *     HTTP/1.1 200 OK
	 * {
	 *   "post details": [
	 *    {
	 *      "ID": 7676,
	 *      "title": "AO.com: ‘Supply chain tech is essential to cope with coronavirus challenges'",
	 *      "post type": "post",
	 *      "url": "https://www.sage.test/en-gb/blog/ao-com-supply-chain-tech-coronavirus-challenges/",
	 *      "amp status": ""
	 *    },
	 *    {
	 *      "ID": 7671,
	 *      "title": "Workforce planning: How to effectively manage employee shift patterns",
	 *      "post type": "post",
	 *      "url": "https://www.sage.test/en-gb/blog/workforce-planning-manage-shift-patterns/",
	 *      "amp status": "disabled"
	 *    },
	 *    {
	 *      "ID": 6621,
	 *      "title": "What is a payment gateway?",
	 *      "post type": "sage_glossary",
	 *      "url": "https://www.sage.test/en-gb/blog/glossary/what-is-a-payment-gateway/",
	 *      "amp status": "disabled"
	 *    },
	 *    {
	 *      "ID": 4124,
	 *      "title": "What is a balance sheet?",
	 *      "post type": "sage_glossary",
	 *      "url": "https://www.sage.test/en-gb/blog/glossary/what-is-a-balance-sheet/",
	 *      "amp status": ""
	 *    },...
	 * @apiUse RestNoRouteError
	 * @apiUse AccessDeniedError
	 */
	public function boomerang_custom_api_endpoint_totals( WP_REST_Request $params ) {

		$params = explode( ',', $params['query'] );

		$published_blog_posts     = wp_count_posts()->publish;
		$published_glossary_posts = wp_count_posts( 'sage_glossary' )->publish;
		$published_pages          = wp_count_posts( 'page' )->publish + 2; // add home page and glossary home page.

		$cats           = get_categories();
		$tags           = get_tags();
		$business_types = get_terms( 'business_type' );
		$industries     = get_terms( 'industry' );

		// only count authors with posts.
		$user_args = array(
			'role'                => 'author',
			'has_published_posts' => true,
		);

		$authors = get_users( $user_args );

		$sites = get_sites( array( 'public' => 1 ) );

		/**
		 *  Build the results object.
		 *  Total posts = blog posts published + glossary posts published + published pages + number of categories + tags + authors with posts + home page + glossary index page + amp pages
		 */

		$result_object['sites'] = array();

		$result_object['published blog posts'] = (int) $published_blog_posts;

		// query posts and custom posts.
		$args = array(
			'post_type'              => array( 'post', 'sage_glossary' ),
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		);

		// endpoint for post details.
		if ( in_array( 'post-details', $params, true ) ) {
			$result_object_post['post details'] = array();
		}

		$query           = new WP_Query( $args );
		$count_amp_posts = 0; // counter for posts with AMP not disabled. nb status is enabled if empty as default.

		foreach ( $query->posts as $post_id ) {

			$check_amp_status = get_post_meta( $post_id, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true );

			if ( 'disabled' !== $check_amp_status ) {
				++$count_amp_posts;
			}
			if ( in_array( 'post-details', $params, true ) ) {
				$result_amp = array(
					'ID'         => $post_id,
					'title'      => get_the_title( $post_id ),
					'post type'  => get_post_type( $post_id ),
					'url'        => get_permalink( $post_id ),
					'amp status' => $check_amp_status,
				);
			}
			array_push( $result_object_post['post details'], $result_amp );
		}

		if ( in_array( 'post-details', $params, true ) ) {
			return $result_object_post;
		}

		$result_object['published glossary posts']                          = (int) $published_glossary_posts;
		$result_object['published amp posts - blog and glossary']           = (int) $count_amp_posts;
		$result_object['published pages + home page + glossary index page'] = (int) $published_pages;

		$result_object['number of categories']     = count( $cats );
		$result_object['number of tags']           = count( $tags );
		$result_object['number of business types'] = count( $business_types );
		$result_object['number of industries']     = count( $industries );
		$result_object['number of authors']        = count( $authors );

		$result_object['total all pages'] = 0;

		if ( in_array( 'taxonomy-data', $params, true ) ) {

			$result_object_tax['categories']     = array();
			$result_object_tax['tags']           = array();
			$result_object_tax['business types'] = array();
			$result_object_tax['industries']     = array();
			$result_object_tax['authors']        = array();

			// categories.
			foreach ( $cats as $cat ) {
				$result_category = array(
					'ID'       => $cat->term_id,
					'category' => $cat->name,
					'count'    => $cat->count,
				);
				array_push( $result_object_tax['categories'], $result_category );
			}

			// tags.
			foreach ( $tags as $tag ) {
				$result_tag = array(
					'ID'    => $tag->term_id,
					'tag'   => $tag->name,
					'count' => $tag->count,
				);
				array_push( $result_object_tax['tags'], $result_tag );
			}

			// business types.
			foreach ( $business_types as $business_type ) {
				$result_term = array(
					'name'  => $business_type->name,
					'count' => $business_type->count,
				);
				array_push( $result_object_tax['business types'], $result_term );
			}

			// industries.
			foreach ( $industries as $industry ) {
				$result_term = array(
					'name'  => $industry->name,
					'count' => $industry->count,
				);
				array_push( $result_object_tax['industries'], $result_term );
			}

			// authors.
			foreach ( $authors as $author ) {
				$result_term = array(
					'name'  => $author->display_name,
					'count' => count_user_posts( $author->id, $post_type = 'post', $public_only = false ),
				);
				array_push( $result_object_tax['authors'], $result_term );
			}

			return $result_object_tax;
		}

		// sites.
		foreach ( $sites as $site ) {
			array_push( $result_object['sites'], $site->path );
		}

		/**
		* Output total of blog posts, glossary posts, pages, categories and tags.
		*/

		$result_object['total all pages'] =
		$published_blog_posts +
		$published_glossary_posts +
		$count_amp_posts +
		$published_pages +
		count( $cats ) +
		count( $tags ) +
		count( $business_types ) +
		count( $industries ) +
		count( $authors );

		return $result_object;

	}


	/**
	 * Check if the API secret key is valid.
	 */
	public function check_api_secret_key( WP_REST_Request $params ) {

		$request_token = $params['key'];
		$boomerang_key = get_option( 'boomerang_key' );

		if ( empty( $request_token ) ) {
			return false;
		}

		if ( $boomerang_key === $request_token ) {
			return true;
		}

		return false;

	}

}
