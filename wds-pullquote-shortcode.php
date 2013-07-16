<?php
/*
Plugin Name: WDS Pull Quote Shortcode
Plugin URI: http://webdevstudios.com/
Description: Shortcode for displaying a stylized pull quote with options for attribution, style, characters, etc. Adds a convenient pull quote button to the WordPress editor.
Author: WebDevStudios.com
Version: 1.0.0
Author URI: http://webdevstudios.com/
*/

class WDS_Pull_Quote_Shortcode {

	public $btn = 'wdspq';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' )  );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_button_script' )  );
		add_action( 'admin_footer', array( $this, 'enqueue_button_script' )  );
		add_shortcode( 'pullquote', array( $this, 'pullquote' )  );
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
			'check_number' => __( 'value must be an integer and greater than 0.', 'wds' ),
			'check_empty_quote' => __( 'Quote must not be empty.', 'wds' ),
			'required_pre' => __( 'required for', 'wds' ),
			'button_name' => __( 'Pull Quote', 'wds' ),
			'button_title' => __( 'Pull Quote Shortcode', 'wds' ),
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
			#wdspq-form {
				padding: 0 20px;
			}
			#wdspq-form .wdspq-errors p {
				font-size: 105%;
			}
			#wdspq-form table {
				width: 100%;
			}
			#wdspq-form label {
				display: block;
				text-align: right;
				padding-right: 9px;
			}
			#wdspq-form input, #wdspq-form select {
				max-width: 92%;
			}
			#wdspq-form .ui-state-highlight {
				color: #c00;
			}
			#wdspq-form .error {
				border-color: #c00;
			}
		</style>
		<div style="display: none;" id="wdspq-form" title="<?php esc_attr_e( 'Pull Quote Shortcode', 'wds' ); ?>">
			<div class="wdspq-errors"><p></p></div>
			<form>
			<fieldset>
				<table>
					<tr id="wdspq-quote-row">
						<td><label for="wdspq-quote"><?php _e( 'Quote', 'wds' ); ?></label></td>
						<td><input type="text" name="wdspq-quote" id="wdspq-quote" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="wdspq-attrib"><?php _e( 'Quote Attribution', 'wds' ); ?></label></td>
						<td><input type="text" name="wdspq-attrib" id="wdspq-attrib" value="" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="wdspq-attrib-link"><?php _e( 'Quote Attribution URL', 'wds' ); ?></label></td>
						<td><input type="text" name="wdspq-attrib-link" id="wdspq-attrib-link" value="" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
					<tr>
						<td><label for="wdspq-align"><?php _e( 'Align', 'wds' ); ?></label></td>
						<td>
							<select name="wdspq-align" id="wdspq-align" value="left" class="text ui-widget-content ui-corner-all">
								<option value="alignleft" selected="selected"><?php _e( 'Left', 'wds' ); ?></option>
								<option value="aligncenter"><?php _e( 'Centered', 'wds' ); ?></option>
								<option value="alignright"><?php _e( 'Right', 'wds' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="wdspq-width"><?php _e( 'Pull Quote Width', 'wds' ); ?></label></td>
						<td><input type="number" name="wdspq-width" id="wdspq-width" value="52" class="text ui-widget-content ui-corner-all" />%</td>
					</tr>
					<tr>
						<td><label for="wdspq-doquote"><?php _e( 'Display Quote Marks', 'wds' ); ?></label></td>
						<td><input type="checkbox" name="wdspq-doquote" id="wdspq-doquote" value="1" checked="checked" class="text ui-widget-content ui-corner-all" /></td>
					</tr>
				</table>
			</fieldset>
			</form>
		</div>
		<?php
	}

	/**
	 * Displays a pull quote
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

		return '<div style="width:'. absint( $width ) .'%" class="pullquote '. $align .'">'. $trimmed . $attribution .'</div>';
	}
}
new WDS_Pull_Quote_Shortcode();