<?php
/*
Plugin Name: Admin Columns - Icons Add-on
Version: 1.1
Description: Use icons instead of text labels in column headers on post, user, media and other admin pages. Extension for Admin Columns.
Author: Jesper van Engelen
Author URI: http://jespervanengelen.com
License: GPLv2

Copyright 2014	Jesper van Engelen	contact@jepps.nl

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit of accessed directly

/**
 * Main plugin class
 *
 * @since 1.0
 */
class CPACIC {

	/**
	 * Admin Columns main plugin class instance
	 *
	 * @since 1.0
	 * @var CPAC
	 */
	public $cpac;

	/**
	 * List of dashicons
	 *
	 * @since 1.1
	 * @var array
	 */
	public $dashicons;

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Admin Columns-dependent setup
		add_action( 'cac/loaded', array( $this, 'init' ) );

		// Hooks
		add_filter( 'cac/column/default_options', array( $this, 'column_default_options' ) );
		add_action( 'cac/column/settings_after', array( $this, 'column_settings_fields' ), 20 );
		add_action( 'cac/settings/after_columns', array( $this, 'after_columns' ) );
		add_filter( 'cac/headings/label', array( $this, 'column_heading_label' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Setup
		add_action( 'plugins_loaded', array( $this, 'after_setup' ) );
		$this->load_dashicons();
	}

	/**
	 * @since 1.0
	 */
	function init( $cpac ) {
		$this->cpac = $cpac;
	}

	/**
	 * Enqueue admin styles and scripts
	 *
	 * @since 1.0
	 */
	public function admin_scripts() {
		wp_register_style( 'cpacic-admin-cpac-settings', plugins_url( 'assets/css/admin/cpac-settings.css', __FILE__ ) );
		wp_register_script( 'cpacic-admin-cpac-settings', plugins_url( 'assets/js/admin/cpac-settings.js', __FILE__ ), array( 'jquery' ) );

		if ( $this->cpac && $this->cpac->is_settings_screen() ) {
			wp_enqueue_style( 'cpacic-admin-cpac-settings' );
			wp_enqueue_script( 'cpacic-admin-cpac-settings' );
			wp_enqueue_media();
		}
	}

	/**
	 * @since 1.0
	 */
	public function column_default_options( $options ) {
		$options['label_icon_dashicon_name'] = '';
		$options['label_icon_custom_url'] = '';
		$options['label_icon_attachment_id'] = '';
		$options['label_icon_type'] = '';

		return $options;
	}

	/**
	 * @since 1.1
	 */
	public function load_dashicons() {
		if ( empty( $this->dashicons ) ) {
			$fname = plugin_dir_path( __FILE__ ) . 'data/icons.json';
			$fh = fopen( $fname, 'r' );
			$contents = '';

			while ( ! feof( $fh ) ) {
				$line = fgets( $fh );
				$contents .= $line;
			}

			$this->dashicons = json_decode( $contents );
		}
	}

	/**
	 * @since 1.0
	 */
	public function column_settings_fields( $column_instance ) {
		$type = $column_instance->options->label_icon_type;
		$icon_dashicon_name = $column_instance->options->label_icon_dashicon_name;
		$icon_custom_url = $column_instance->options->label_icon_custom_url;
		$icon_attachment_id = $column_instance->options->label_icon_attachment_id;

		// Backwards compatibility with 1.0
		// In 1.0, a single column setting was used to store the custom URL and the attachment ID, dependent on
		// the column type. In 1.1, this was replaced by storing all separately
		if ( ! empty( $column_instance->options->label_icon ) ) {
			if ( $type == 'attachment' && ! $icon_attachment_id ) {
				$icon_attachment_id = $column_instance->options->label_icon;
			}

			if ( $type == 'custom' && ! $icon_custom_url ) {
				$icon_custom_url = $column_instance->options->label_icon;
			}
		}
		?>
		<tr class="column-header-icon">
			<?php $column_instance->label_view( __( 'Header Icon', 'cpac' ), __( 'This icon will replace the label text in the column header.', 'cpacic' ), 'label_icon_type-attachment' ); ?>
			<td class="input">
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-none">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-none" value="" <?php checked( $type, '' ); ?> />
					<?php _e( 'None', 'cpacic' ); ?>
				</label>
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-dashicon">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-dashicon" value="dashicon" <?php checked( $type, 'dashicon' ); ?> />
					<?php _e( 'Icon', 'cpacic' ); ?>
				</label>
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-custom">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-custom" value="custom" <?php checked( $type, 'custom' ); ?> />
					<?php _e( 'Custom URL', 'cpacic' ); ?>
				</label>
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-attachment">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-attachment" value="attachment" <?php checked( $type, 'attachment' ); ?> />
					<?php _e( 'Media Library/Upload', 'cpacic' ); ?>
				</label>
				<div class="section cpacic-label-icon-dashicon" <?php if ( $type != 'dashicon' ) echo 'style="display: none;"'; ?>>
					<div class="cpapic-current-icon">
						<div class="dashicons"><?php if ( $icon_dashicon_name ) echo "&#x{$icon_dashicon_name};" ?></div>
					</div>
					<a href="#TB_inline?width=600&amp;height=400&amp;inlineId=cpacic-select-icon" class="button thickbox" data-column="<?php echo esc_attr( $column_instance->id ); ?>" data-dashicon="<?php echo esc_attr( $icon_dashicon_name ); ?>"><?php _e( 'Select Icon', 'cpacic' ); ?></a>
					<input type="hidden" name="<?php $column_instance->attr_name( 'label_icon_dashicon_name' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_dashicon_name' ); ?>" value="<?php echo esc_attr( $icon_dashicon_name ); ?>" >
				</div>
				<div class="section cpacic-label-icon-custom" <?php if ( $type != 'custom' ) echo 'style="display: none;"'; ?>>
					<input type="text" name="<?php $column_instance->attr_name( 'label_icon_custom_url' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_custom_url' ); ?>" value="<?php echo esc_attr( $icon_custom_url ); ?>" class="text" placeholder="<?php esc_attr_e( 'Image URL', 'cpacic' ); ?>" />
				</div>
				<div class="section cpacic-label-icon-attachment" <?php if ( $type != 'attachment' ) echo 'style="display: none;"'; ?>>
					<a href="#">
						<div class="icon-preview">
							<?php if ( $icon_attachment_id ) : ?>
								<?php if ( $image = wp_get_attachment_image_src( $icon_attachment_id ) ) : ?>
									<img src="<?php echo esc_attr( $image[0] ); ?>" />
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="button"><?php _e( 'Upload/Select', 'cpacic' ); ?></div>
					</a>
					<?php if ( ! $icon_attachment_id ) : ?>
						<em class="no-icon"><?php _e( 'No image selected', 'cpacic' ); ?></em>
					<?php endif; ?>
					<input type="hidden" name="<?php $column_instance->attr_name( 'label_icon_attachment_id' ); ?>" value="<?php echo esc_attr( $icon_attachment_id ); ?>" />
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Output the HTML for the popup box for selecting a dashicon
	 *
	 * @since 1.1
	 */
	public function after_columns() {
		$this->load_dashicons();
		add_thickbox();
		?>
		<div id="cpacic-select-icon">
			<div class="cpacic-popup-content">
				<div class="cpacic-popup-toolbar">
					<a href="#" class="button button-primary"><?php _e( 'Done' ); ?></a>
				</div>
				<?php foreach ( $this->dashicons as $dashicon => $dashicon_label ) : ?>
					<a href="#" class="cpacic-dashicon" data-dashicon="<?php echo esc_attr( $dashicon ); ?>" title="<?php echo ucwords( str_replace( '-', ' ', str_replace( ',', ', ', $dashicon_label ) ) ); ?>">
						<span class="dashicons">&#x<?php echo esc_html( $dashicon ); ?>;</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function column_heading_label( $label, $column_name, $column_options, $storage_model_instance ) {
		$args = wp_parse_args( $column_options, array(
			'label_icon_type' => '',
			'label_icon_dashicon_name' => '',
			'label_icon_custom_url' => '',
			'label_icon_attachment_id' => '',

			// Backwards compatibility with 1.0
			// In 1.0, a single column setting was used to store the custom URL and the attachment ID, dependent on
			// the column type. In 1.1, this was replaced by storing all separately
			'label_icon' => ''
		) );

		// Backwards compatibility with 1.0
		if ( ! $args['label_icon_custom_url'] ) {
			$args['label_icon_custom_url'] = $args['label_icon'];
		}

		if ( ! $args['label_icon_attachment_id'] ) {
			$args['label_icon_attachment_id'] = $args['label_icon'];
		}

		// Display icon dependent on icon type
		if ( ! empty( $args['label_icon_type'] ) ) {
			if ( $args['label_icon_type'] == 'attachment' ) {
				if ( $args['label_icon_attachment_id'] ) {
					if ( $image = wp_get_attachment_image_src( $args['label_icon_attachment_id'], 'full' ) ) {
						$label = '<img src="' . esc_attr( $image[0] ) . '" alt="' . esc_attr( $label ) . '" />';
					}
				}
			}
			else if ( $args['label_icon_type'] == 'custom' ) {
				if ( $args['label_icon_custom_url'] ) {
					$label = '<img src="' . esc_attr( $args['label_icon_custom_url'] ) . '" alt="' . esc_attr( $label ) . '" />';
				}
			}
			else if ( $args['label_icon_type'] == 'dashicon' ) {
				if ( $args['label_icon_dashicon_name'] ) {
					$label = '<div class="dashicons">&#x' . esc_html( $args['label_icon_dashicon_name'] ) . '</div>';
				}
			}
		}

		return $label;
	}

	/**
	 * Allow other plugins to hook into this plugin
	 * Should be called on the plugins_loaded action
	 *
	 * @see action:plugins_loaded
	 * @since 1.0
	 */
	public function after_setup() {
		/**
		 * Fires after this plugin is setup, which should be on plugins_loaded
		 * Should be used to access the main plugin class instance, possibly store a reference to it for later use
		 * and remove any plugin action and filter hooks
		 *
		 * @since 1.0
		 *
		 * @param CPACIC Main plugin class instance
		 */
		do_action( 'cpacic/after_setup', $this );
	}

}

new CPACIC();
