<?php
/*
Plugin Name: Admin Columns - Icons Add-on
Version: 1.0
Description: Use icons instead of text labels in column headers on post, user, media and other admin pages. Extension for Codepress Admin Columns.
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
		add_filter( 'cac/headings/label', array( $this, 'column_heading_label' ), 10, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Setup
		add_action( 'plugins_loaded', array( $this, 'after_setup' ) );
	}

	/**
	 * @since 1.0
	 */
	function init( $cpac ) {
		$this->cpac = $cpac;
	}

	public function admin_scripts() {
		wp_register_script( 'cpacic-admin-cpac-settings', plugins_url( 'assets/js/admin/cpac-settings.js', __FILE__ ), array( 'jquery' ) );

		if ( $this->cpac && $this->cpac->is_settings_screen() ) {
			wp_enqueue_script( 'cpacic-admin-cpac-settings' );
			wp_enqueue_media();
		}
	}

	/**
	 * @since 1.0
	 */
	public function column_default_options( $options ) {
		$options['label_icon'] = '';
		$options['label_icon_type'] = '';

		return $options;
	}

	/**
	 * @since 1.0
	 */
	public function column_settings_fields( $column_instance ) {
		$type = $column_instance->options->label_icon_type;
		$icon = $column_instance->options->label_icon;
		?>
		<tr class="column-header-icon">
			<?php $column_instance->label_view( __( 'Header Icon', 'cpac' ), __( 'This icon will replace the label text in the column header.', 'cpacic' ), 'label_icon_type-attachment' ); ?>
			<td class="input">
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-none">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-none" value="" <?php checked( $type, '' ); ?> />
					<?php _e( 'None', 'cpacic' ); ?>
				</label>
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-custom">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-custom" value="custom" <?php checked( $type, 'custom' ); ?> />
					<?php _e( 'Custom URL', 'cpacic' ); ?>
				</label>
				<label for="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-attachment">
					<input type="radio" name="<?php $column_instance->attr_name( 'label_icon_type' ); ?>" id="<?php $column_instance->attr_id( 'label_icon_type' ); ?>-attachment" value="attachment" <?php checked( $type, 'attachment' ); ?> />
					<?php _e( 'Media Library/Upload', 'cpacic' ); ?>
				</label>
				<div class="section cpacic-label-icon-custom" <?php if ( $type != 'custom' ) echo 'style="display: none;"'; ?>>
					<input type="text" name="<?php $column_instance->attr_name( 'label_icon' ); ?>" id="<?php $column_instance->attr_id( 'label_icon' ); ?>" value="<?php echo esc_attr( $icon ); ?>" class="text" placeholder="<?php esc_attr_e( 'Image URL', 'cpacic' ); ?>" />
				</div>
				<div class="section cpacic-label-icon-attachment" <?php if ( $type != 'attachment' ) echo 'style="display: none;"'; ?>>
					<a href="#">
						<div class="icon-preview">
							<?php if ( $icon ) : ?>
								<?php if ( $image = wp_get_attachment_image_src( $icon ) ) : ?>
									<img src="<?php echo esc_attr( $image[0] ); ?>" />
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="button"><?php _e( 'Upload/Select', 'cpacic' ); ?></div>
					</a>
					<?php if ( ! $icon ) : ?>
						<em class="no-icon"><?php _e( 'No image selected', 'cpacic' ); ?></em>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @since 1.0
	 */
	public function column_heading_label( $label, $column_name, $column_options, $storage_model_instance ) {
		if ( ! empty( $column_options['label_icon_type'] ) && ! empty( $column_options['label_icon'] ) ) {
			if ( $column_options['label_icon_type'] == 'attachment' ) {
				$image = wp_get_attachment_image_src( $column_options['label_icon'], 'full' );

				if ( ! empty( $image ) ) {
					$label = '<img src="' . esc_attr( $image[0] ) . '" alt="' . esc_attr( $label ) . '" />';
				}
			}
			else {
				$label = '<img src="' . esc_attr( $column_options['label_icon'] ) . '" alt="' . esc_attr( $label ) . '" />';
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
