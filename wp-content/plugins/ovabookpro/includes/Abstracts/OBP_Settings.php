<?php

namespace BookPro\Abstracts;

use BookPro\Admin\OBP_Admin_Settings;

defined( 'ABSPATH' ) || exit;

abstract class OBP_Settings extends OBP_Admin_Settings {

	/**
	 * option name.
	 *
	 * @var string
	 */
	public $option_name = null;

	/**
	 * setting title.
	 *
	 * @var string
	 */
	protected $title = null;

	/**
	 * setting fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * is tab.
	 *
	 * @var boolean
	 */
	public $is_tab = false;

	/**
	 * setting position.
	 *
	 * @var int
	 */
	protected $position = 1;

	/**
	 * Data options.
	 *
	 * @var array
	 */
	public $options = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'obp_admin_settings', array( $this, 'add_tab' ), $this->position );
			add_action( 'obp_admin_setting_' . $this->option_name . '_content', array( $this, 'layout' ), $this->position );
		}

		// Load options
		$this->get_options();

		// Settings fields
		add_filter( 'obp_settings_fields', array( $this, 'settings_fields' ) );
	}

	/**
	 * Add tab
	 * @return array
	 */
	public function add_tab( $tabs = array() ) {
		if ( $this->option_name && $this->title ) {
			$tabs[$this->option_name] = $this->title;
		}

		return $tabs;
	}

	/**
     * Generate layout
     * @return html layout
     */
	public function layout() { 
        // Before content tab
		do_action( 'obp_before_admin_setting_content_tab', $this->option_name );
		
		$this->fields = apply_filters( 'obp_admin_setting_fields', $this->render_fields(), $this->option_name );


		if ( $this->fields ) {
			$html = array();

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $tab_group = isset( $_GET['group'] ) ? sanitize_text_field( wp_unslash( $_GET['group'] ) ) : '';


            if ( ! $tab_group ) {
            	$tab_group = current( array_keys( $this->fields ) );
            }

			if ( $this->is_tab ) {
				?>
				<h3 class="obp-tab-subgroup">
				<?php
				foreach ( $this->fields as $id => $groups ) {
			
					$class = 'obp-tab-group';
				
					if ( $tab_group === $id ) $class = 'obp-tab-group active';
					?>
					<a href="#<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $groups['title'] ); ?></a>
				<?php } ?>
				</h3>
				<?php
			}

			if ( $this->is_tab ) {
				foreach ( $this->fields as $id => $groups ) {
					$class = 'obp-tab-group-content';

					if ( $tab_group === $id ) $class = 'obp-tab-group-content active';
					?>
					<div data-tab-id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
						<?php echo apply_filters( 'obp_before_tab_group_content', '', $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

						<?php if ( isset( $groups['desc'] ) ): ?>
							<div class="desc-tab"><?php echo esc_html( $groups['desc'] ); ?></div>
						<?php endif; ?>

						<?php $this->generate_fields( $groups );
						echo apply_filters( 'obp_after_tab_group_content', '', $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<?php
				}
			} else {
				$this->generate_fields( $this->fields );
			}
		}
        // After tab content
		do_action( 'obp_after_admin_setting_content_tab', $this->option_name );
	}

	/**
     * Render fields
     * @return html
     */
	protected function render_fields() {
		return array();
	}

	/**
     * Genterate fields settings
     * @param  array  $groups
     * @return html
     */
	function generate_fields( $groups = array() ) {
		$html = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_group = isset( $_GET['group'] ) ? sanitize_text_field( wp_unslash( $_GET['group'] ) ) : '';

		foreach ( $groups as $key => $group ) {
			// Accordion
			$accordion 	= isset( $group['accordion'] ) ? $group['accordion'] : array();
			$tabs 	= isset( $group['tabs'] ) ? $group['tabs'] : array();

			if ( $accordion ) {
				?>
				<div class="obp-accordion">
				<?php
				foreach ( $accordion as $section_key => $section_name ) { ?>
					<h3 class="obp-accordion-title">
					<span class="text"><?php echo esc_html( $section_name ); ?></span>
					<span class="icon"><i class="flaticon bookproicon-right-arrow"></i></span>
					</h3>
					<div class="obp-accordion-content <?php echo esc_attr( $section_key ); ?>">
						<?php $this->generate_tables_accordion( $group, $section_key ); ?>
					</div>
				<?php } ?>
				</div>
			<?php } elseif ( $tabs ) { ?>
				<div class="obp-tabs-table">
					<ul>
						<?php $i = 0;
						foreach ( $tabs as $tab_key => $tab_name ) { ?>
							<li>
								<?php if ( empty( $_group ) && $i == 0 ) {
									$class_active = 'active';
								} else {
									$class_active = $_group === $tab_key ? 'active' : '';
								}
								?>
								<a href="#" data-id="<?php echo esc_attr( $tab_key ); ?>" class="obp-tab-table <?php echo esc_attr( $class_active ); ?>">
									<?php echo esc_html( $tab_name ); ?>
								</a>
							</li>
							<?php $i++;
						} ?>
					</ul>
					<?php $j = 0;
					foreach ($tabs as $tab_key => $tab_name) {
		
						if ( empty( $_group ) && $j == 0 ) {
							$class_active = 'active';
						} else {
							$class_active = $_group === $tab_key ? 'active' : '';
						}
						
						$this->generate_tables_accordion( $group, $tab_key, $class_active );
						$j++;
					} ?>
				</div>
			<?php } else {
				$this->generate_tables( $group );
			}
		}
	}


	/**
     * Genterate tables accordion
     * @param  array $groups, string $section_key
     * @return html
     */
	public function generate_tables_accordion( $group = array(), $section_key = null, $class_active = null ){
		$html = array();

		if ( isset( $group['title'], $group['desc'] ) ) {
			?>
			<h3><?php echo esc_html( $group['title'] ); ?></h3>
			<p><?php echo wp_kses_post( $group['desc'] ); ?></p>
			<?php
		}

		if ( isset( $group['fields'] ) ) {
			if ( is_null( $class_active ) ) { ?>
				<table id="<?php echo esc_attr( $section_key ); ?>">
				<?php
			} else { ?>
				<table id="<?php echo esc_attr( $section_key ); ?>" class="<?php echo esc_attr( $class_active ); ?>">
				<?php
			}
			

			foreach ( $group['fields'] as $type => $field ) {
				$default = array(
					'belong_to' => '',
					'type' 		=> '',
					'label' 	=> '',
					'desc' 		=> '',
					'atts' 		=> array(),
					'name' 		=> '',
					'group' 	=> $this->option_name ? $this->option_name : null,
					'options' 	=> array(),
					'default' 	=> ''
				);

				if ( $section_key && $field['belong_to'] === $section_key ) {
					if ( isset( $field['filter'] ) && $field['filter'] ) {
						
						echo call_user_func_array( $field['filter'], array( $field ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						
					} elseif ( isset( $field['name'], $field['type'] ) ) { ?>
						<tr>
							<th>
								<label for="<?php echo esc_attr( $this->get_field_id( $field['name'] ) ); ?>">
									<?php echo esc_html( $field['label'] ); ?>
								</label>

								<?php if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) { ?>
									<p><small><?php echo wp_kses_post( $field['desc'] ); ?></small></p>
								<?php } ?>
							</th>

							<td>
								<?php
								$field = wp_parse_args( $field, $default );
								include OBP_PLUGIN_INC . 'Admin/Settings/views/fields/' . $field['type'] . '.php';
							?>
							</td>
						</tr>
					<?php }
				}
			} ?>

			</table>
			<?php
		}
		
	}

	/**
     * Genterate tables
     * @param  array $groups, string $section_key
     * @return html
     */
	public function generate_tables( $group = array() ){
		$html = array();

		// Title
		if ( isset( $group['title'] ) ) { ?>
			<h3 class="obp-title"><?php echo esc_html( $group['title'] ); ?></h3>
			<?php
		}

		// Description
		if ( isset( $group['desc'] ) ) {
			?>
			<p><?php echo wp_kses_post( $group['desc'] ); ?></p>
			<?php
		}

		if ( isset( $group['fields'] ) ) {
			?>
			<table>
			<?php
			foreach ( $group['fields'] as $type => $field ) {
				$default = array(
					'belong_to' => '',
					'type' 		=> '',
					'label' 	=> '',
					'desc' 		=> '',
					'atts' 		=> array(),
					'name' 		=> '',
					'group' 	=> $this->option_name ? $this->option_name : null,
					'options' 	=> array(),
					'default' 	=> ''
				);

				if ( isset( $field['filter'] ) && $field['filter'] ) {
					
					echo call_user_func_array( $field['filter'], array( $field ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					
				} elseif ( isset( $field['name'], $field['type'] ) ) {
					if ( $field['type'] == 'hidden' || ( isset( $field['is_hidden'] ) && $field['is_hidden'] ) ) {
						?>
						<tr style="display: none;">
						<?php
					} else { ?>
						<tr>
						<?php
					}
						?>
						<th>
							<?php if ( isset( $field['checkbox'] ) && $field['checkbox'] ) { ?>
								<label for="<?php echo esc_attr( $this->get_field_id( $field['checkbox'] ) ); ?>" class="obp-label-checkbox"><input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $field['checkbox'] ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field['checkbox'] ) ); ?>" value="1" <?php checked( $this->get( $field['checkbox'] ), 1 ); ?> /><span class="obp-checkmark"></span><?php echo esc_html( $field['label'] ); ?></label>
							<?php
							} else {
								if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
									?>
									<label for="<?php echo esc_attr( $this->get_field_id( $field['name'] ) ); ?>">
										<?php echo esc_html( $field['label'] ); ?>
									</label>
									<?php
								}
								
							}

							if ( isset( $field['desc'] ) && ! empty( $field['desc'] ) ) { ?>
								<p><small><?php echo wp_kses_post( $field['desc'] ); ?></small></p>
								<?php
							} ?>
						</th>
						<td>
							<?php
							$field = wp_parse_args( $field, $default );
							include OBP_PLUGIN_INC . 'Admin/Settings/views/fields/' .  $field['type'] . '.php';
						?>
						</td>
					</tr>
				<?php } elseif ( $field['type'] === 'warning' ) {
					if ( isset( $field['is_hidden'] ) && $field['is_hidden'] ) { ?>
						<tr id="<?php echo esc_attr( $field['id'] ); ?>" style="display: none;">
					<?php } else { ?>
						<tr id="<?php echo esc_attr( $field['id'] ); ?>">
					<?php } ?>
						<td>
							<?php
							$field = wp_parse_args( $field, $default );
							include OBP_PLUGIN_INC . 'Admin/Settings/views/fields/' . $field['type'] . '.php';
							?>
						</td>
					</tr>
				<?php }
			}
			?>
			</table>
		<?php }
	}

	/**
     * Get option value
     * @param  $name
     * @return option value. array, string, boolean
     */
	public function get( $name = null, $default = null ) {
		if ( ! $this->options ){
			$this->options = $this->get_options();
		}

		if ( $name && isset( $this->options[$name] ) && ! is_array( $this->options[$name] ) ) {
			return trim( $this->options[$name] );
		}

		if ( $name && isset( $this->options[$name] ) && is_array( $this->options[$name] ) ) {
			return $this->options[$name];
		}

		return $default;
	}

	/**
     * Get field id
     * @param  $name of field option
     * @return string name field
     */
	public function get_field_id( $name = null, $group = null ) {
		if ( ! $this->option_group || ! $name ) return;
		if ( ! $group ) $group = $this->option_name;
		if ( $group ) return $this->option_group . '_' . $group . '_' . $name;

		return $this->option_group . '_' . $name;
	}

	/**
     * Get field name
     * @param  $name of field option
     * @return string name field
     */
	public function get_field_name( $name = null, $group = null ) {
		if ( ! $this->option_group || ! $name ) return;
		if ( ! $group ) $group = $this->option_name;
		if ( $group ) return $this->option_group . '[' . $group . '][' . $name . ']';

		return $this->option_group . '[' . $name . ']';
	}

	/**
     * Settings fields
     * @return array
     */
	public function settings_fields( $settings ) {
		$settings[$this->option_name] = $this;
		return $settings;
	}

	/**
     * Rendor attributes
     * @param  $atts
     * @return string
     */
	public function render_atts( $atts = array() ) {
		if ( ! is_array( $atts ) ) return;

		$html = array();

		foreach ( $atts as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$html[] = $key . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $html );
	}

	/**
	 * Get options
	 * @return array || null
	 */
	protected function get_options() {
		if ( $this->options ) return $this->options;

		$options = parent::get_options();

		if ( ! $options ) $options = get_option( $this->option_group, null );

		if ( isset( $options[$this->option_name] ) ) return $this->options = $options[$this->option_name];

		return null;
	}
}