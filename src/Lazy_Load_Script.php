<?php
/**
 * Class Google\Native_Lazyload\Lazy_Load_Script
 *
 * @package   Google\Native_Lazyload
 * @copyright 2019 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://wordpress.org/plugins/native-lazyload/
 */

namespace Google\Native_Lazyload;

/**
 * Class responsible for the script (and stylesheet) needed for lazy-loading.
 *
 * @since 1.0.0
 */
class Lazy_Load_Script {

	// Relative path to the JavaScript fallback file.
	const FALLBACK_SCRIPT_PATH = 'assets/js/lazyload.js';

	/**
	 * Plugin context instance to pass around.
	 *
	 * @since 1.0.0
	 * @var Context
	 */
	protected $context;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Context $context The plugin context instance.
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Prints the lazy-loading script.
	 *
	 * If the 'loading' attribute is supported by the browser, all elements to lazy-load are prepared for the native
	 * functionality. Otherwise, the fallback script is loaded into the page.
	 *
	 * @since 1.0.0
	 */
	public function print_script() {
		?>
<script type="text/javascript">
if ( 'loading' in HTMLImageElement.prototype ) {
	( function() {
		var lazyElements = [].slice.call( document.querySelectorAll( '.lazy' ) );
		lazyElements.forEach( function( element ) {
			if ( ! element.dataset.src ) {
				return;
			}
			element.src = element.dataset.src;
			if ( element.dataset.srcset ) {
				element.srcset = element.dataset.srcset;
			}
			if ( element.dataset.sizes ) {
				element.sizes = element.dataset.sizes;
			}
		} );
	} )();
} else {
	( function() {
		var script = document.createElement( 'script' );
		script.type = 'text/javascript';
		script.src = '<?php echo esc_js( $this->get_fallback_script_url() ); ?>';
		script.defer = true;
		document.body.appendChild( script );
	} )();
}
</script>
		<?php
	}

	/**
	 * Prints a style rule to ensure that lazy-loaded elements do not appear if JavaScript is disabled.
	 *
	 * @since 1.0.0
	 */
	public function print_style() {
		?>
<style type="text/css">
.no-js .lazy[data-src] {
	display: none;
}
</style>
		<?php
	}

	/**
	 * Registers the lazy-loading fallback script in WordPress.
	 *
	 * This is only done so that it is known to the infrastructure, e.g. so that a service worker can precache it.
	 *
	 * @since 1.0.0
	 */
	public function register_fallback_script() {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters
		wp_register_script(
			'native-lazyload-fallback',
			$this->get_fallback_script_url(),
			[],
			null,
			true
		);

		wp_script_add_data( 'native-lazyload-fallback', 'defer', true );
		wp_script_add_data( 'native-lazyload-fallback', 'precache', true );
	}

	/**
	 * Gets the URL to the fallback JavaScript file to load.
	 *
	 * @since 1.0.0
	 *
	 * @return string File URL.
	 */
	protected function get_fallback_script_url() : string {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return $this->context->url( str_replace( '/js/', '/js/src/', static::FALLBACK_SCRIPT_PATH ) );
		}

		return $this->context->url( static::FALLBACK_SCRIPT_PATH );
	}
}
