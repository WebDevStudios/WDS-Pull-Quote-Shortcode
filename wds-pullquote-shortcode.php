<?php
/*
Plugin Name: NBC Pullquote Shortcode
Plugin URI: http://webdevstudios.com/
Description: Shortcode for displaying a stylized pullquote with options for attribution, style, characters, etc. Adds a convenient pullquote button to the WordPress editor.
Author: WebDevStudios.com
Version: 1.0.0
Author URI: http://webdevstudios.com/
*/

class NBC_Pullquote_Shortcode {

	public $btn = 'nbcpq';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' )  );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_button_script' )  );
		add_action( 'admin_footer', array( $this, 'enqueue_button_script' )  );
		add_shortcode( 'nbc-pq', array( $this, 'pullquote' )  );
	}

	public function init() {
		add_filter( 'mce_external_plugins', array( $this, 'add_buttons' )  );
		add_filter( 'mce_buttons', array( $this, 'register_buttons' )  );
	}

	public function add_buttons( $plugin_array ) {
		$plugin_array[$this->btn] = plugins_url( '/button-mce.js', __FILE__ );
		return $plugin_array;
	}

	public function register_buttons( $buttons ) {
		array_push( $buttons, $this->btn );
		return $buttons;
	}

	public function register_button_script() {
		wp_register_script( $this->btn, plugins_url( '/button.js', __FILE__ ) , array( 'jquery', 'jquery-ui-dialog', 'quicktags' ), '0.1.0', true );
		wp_localize_script( $this->btn, $this->btn.'text', array(
			'check_number' => __( 'value must be an integer and greater than 0.', 'tv-msnbc' ),
			'check_empty_quote' => __( 'Quote must not be empty.', 'tv-msnbc' ),
			'required_pre' => __( 'required for', 'tv-msnbc' ),
			'button_name' => __( 'NBC Pullquote', 'tv-msnbc' ),
			'button_title' => __( 'NBC Pullquote Shortcode', 'tv-msnbc' ),
		) );
	}

	public function enqueue_button_script() {
		$current = get_current_screen();

		if ( !isset( $current->parent_base ) || $current->parent_base != 'edit' )
			return;
		wp_enqueue_script( $this->btn );

		// Shortcode button popup form
		?>
		<style type="text/css">
			#nbcpq-form {
				padding: 0 20px;
			}
			#nbcpq-form .nbcpq-errors p {
				font-size: 105%;
			}
			#nbcpq-form table {
				width: 100%;
			}
			#nbcpq-form label {
				display: block;
				text-align: right;
				padding-right: 9px;
			}
			#nbcpq-form input, #nbcpq-form select {
				max-width: 92%;
			}
			#nbcpq-form .ui-state-highlight {
				color: #c00;
			}
			#nbcpq-form .error {
				border-color: #c00;
			}
		</style>
		<div style="display: none;" id="nbcpq-form" title="<?php esc_attr_e( 'Pull Quote Shortcode', 'tv-msnbc' ); ?>">
			<div class="nbcpq-errors"><p></p></div>
			<form>
			<fieldset>
				<table>
					<tr id="nbcpq-quote-row">
						<td><label for="nbcpq-quote"><?php _e( 'Quote', 'tv-msnbc' ); ?></label></td>
						<td><input type="text" name="nbcpq-quote" id="nbcpq-quote" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="nbcpq-attrib"><?php _e( 'Quote Attribution', 'tv-msnbc' ); ?></label></td>
						<td><input type="text" name="nbcpq-attrib" id="nbcpq-attrib" value="" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="nbcpq-attrib-link"><?php _e( 'Quote Attribution URL', 'tv-msnbc' ); ?></label></td>
						<td><input type="text" name="nbcpq-attrib-link" id="nbcpq-attrib-link" value="" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="nbcpq-align"><?php _e( 'Align', 'tv-msnbc' ); ?></label></td>
						<td>
							<select name="nbcpq-align" id="nbcpq-align" value="left" class="text ui-widget-content ui-corner-all">
								<option value="alignleft" selected="selected"><?php _e( 'Left', 'tv-msnbc' ); ?></option>
								<option value="aligncenter"><?php _e( 'Centered', 'tv-msnbc' ); ?></option>
								<option value="alignright"><?php _e( 'Right', 'tv-msnbc' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="nbcpq-width"><?php _e( 'Pull Quote Width', 'tv-msnbc' ); ?></label></td>
						<td><input type="number" name="nbcpq-width" id="nbcpq-width" value="52" class="text ui-widget-content ui-corner-all" />%</td>
					</tr>
					<tr>
						<td><label for="nbcpq-doquote"><?php _e( 'Display Quote Marks', 'tv-msnbc' ); ?></label></td>
						<td><input type="checkbox" name="nbcpq-doquote" id="nbcpq-doquote" value="1" checked="checked" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
				</table>
			</fieldset>
			</form>
		</div>
		<?php
	}

	/**
	 * Displays a pullquote
	 * @since 1.0.0
	 * @param  array $args arguments to pass to the shortcode
	 * @param  array $args arguments to pass to the shortcode
	 * @return string      concatenated widget output
	 */
	function pullquote( $atts, $content = '' ) {
		// defaults
		extract( $newatts = shortcode_atts( array(
			'align'  => 'alignleft',
			'attribution' => '',
			'attribution_link' => '',
			'chars' => 130,
			'width' => 52,
			'quote' => true,
		), $atts ) );

		if ( !empty( $attr ) )
			$attribution = $attr;

		if ( $align ) {
			if ( in_array( $align, array( 'center', 'left', 'right' ) ) )
				$align = ' align'. $align;

			elseif ( ! in_array( $align, array( 'aligncenter', 'alignleft', 'alignright' ) ) )
				$align = 'alignleft';
		}

		$trimmed = function_exists( 'genesis_truncate_phrase' ) ? genesis_truncate_phrase( $content, $chars ) : $content;

		if ( $content != $trimmed )
			$trimmed .= '&hellip;';

		if ( $quote && $quote !== 'false' )
			$trimmed = '<q>'. $trimmed .'</q>';

		$attribution = $attribution && $attribution_link ? '<a href="'. esc_url( $attribution_link ) .'" target="_blank">'. $attribution .'</a>' : $attribution;
		$attribution = $attribution ? '<div class="attribution">&#8212;'. $attribution .'</div>' : '';

		$width = is_numeric( $width ) && $width < 101 ? $width : 52;

		return '<div style="width:'. absint( $width ) .'%" class="nbc-pq '. $align .'">'. $trimmed . $attribution .'</div>';
	}
}
new NBC_Pullquote_Shortcode();