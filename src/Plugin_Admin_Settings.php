<?php
/**
 * Plugin_Admin_Settings class file.
 *
 * @package Plugin_Settings
 */

namespace Oblak\WP;

/**
 * Outputs the plugin settings page
 */
class Plugin_Admin_Settings {

    /**
     * Plugin namespace
     *
     * @var string
     */
    private $slug;

    /**
     * Settings array
     *
     * @var Plugin_Settings_page[]
     */
    private $settings = array();

    /**
     * Messages array
     *
     * @var string[]
     */
    private static $messages = array();

    /**
     * Errors array
     *
     * @var string[]
     */
    private static $errors = array();

    /**
     * Class constructor
     *
     * @param  string $slug Plugin namespace.
     */
    public function __construct( $slug ) {
        $this->slug = $slug;
    }

    /**
     * Adds an error to the errors array
     *
     * @param string $text Error text / HTML.
     */
    public static function add_error( $text ) {
        self::$errors[] = $text;
    }

    /**
     * Adds a message to the messages array
     *
     * @param string $text Message text / HTML.
     */
    public static function add_message( $text ) {
        self::$messages[] = $text;
    }

    /**
     * Output messages + errors.
     */
    public function show_messages() {
        if ( count( self::$errors ) > 0 ) {
            foreach ( self::$errors as $error ) {
                printf(
                    '<div id="message" class="error inline"><p><strong>%s</strong></p></div>',
                    esc_html( $error )
                );
            }
        } elseif ( count( self::$errors ) > 0 ) {
            foreach ( self::$errors as $message ) {
                printf(
                    '<div id="message" class="updated inline"><p><strong>%s</strong></p></div>',
                    esc_html( $message )
                );
            }
        }
    }

    /**
     * Get the settings pages
     */
    public function get_settings_pages() {
		if ( empty( $this->settings ) ) {
            $this->settings = apply_filters( $this->slug . '_get_settings_pages', array() );
		}

        return $this->settings;
    }

    /**
     * Output the settings pages
     */
    public function output() {
        global $current_tab, $current_section;

        do_action( $this->slug . '_settings_start' );

        $tabs = apply_filters( $this->slug . '_settings_tabs_array', array() );

        include 'views/html-admin-settings.php';
    }

    /**
     * Output admin fields.
     *
     * Loops through the woocommerce options array and outputs each field.
     *
     * @param array[] $options Opens array to output.
     * @param string  $slug    Plugin slug.
     */
	public static function output_fields( $options, $slug ) {
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}

			// The 'field_name' key can be used when it is useful to specify an input field name that is different
			// from the input field ID. We use the key 'field_name' because 'name' is already in use for a different
			// purpose.
			if ( ! isset( $value['field_name'] ) ) {
				$value['field_name'] = $value['id'];
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['default'] ) ) {
				$value['default'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}
			if ( ! isset( $value['suffix'] ) ) {
				$value['suffix'] = '';
			}
			if ( ! isset( $value['value'] ) ) {
				$value['value'] = self::get_option( $value['id'], $value['default'] );
			}

            $disabled = isset( $value['disabled'] ) && $value['disabled'] ? true : false;

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling.
			$field_description = self::get_field_description( $value );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];

			// Switch based on type.
			switch ( $value['type'] ) {

				// Section Titles.
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
					}
					if ( ! empty( $value['desc'] ) ) {
						echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
						echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
						echo '</div>';
					}
					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $value['id'] ) ) {
						do_action( $slug . '_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				case 'info':
                    if ( empty( $value['text'] ) ) {
                        break;
                    }
					echo '<tr><td colspan="2" style="padding: 0;' . esc_attr( $value['css'] ) . '">';
					echo wp_kses_post( wpautop( wptexturize( $value['text'] ) ) );
					echo '</td></tr>';
					break;

				// Section Ends.
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( $slug . '_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( $slug . '_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'.
				case 'text':
				case 'password':
				case 'datetime':
				case 'datetime-local':
				case 'date':
				case 'month':
				case 'time':
				case 'week':
				case 'number':
				case 'email':
				case 'url':
				case 'tel':
					$option_value = $value['value'];

					?><tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<input
									name="<?php echo esc_attr( $value['field_name'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="<?php echo esc_attr( $value['type'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                    <?php echo $disabled ? 'disabled' : ''; ?>
								<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									/><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
		            break;

				// Color picker.
				case 'color':
					$option_value = $value['value'];

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">&lrm;
								<span class="colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>">&nbsp;</span>
								<input
									name="<?php echo esc_attr( $value['field_name'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="text"
									dir="ltr"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>colorpick"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									/>&lrm; <?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
							</td>
						</tr>
						<?php
		            break;

				// Textarea.
				case 'textarea':
					$option_value = $value['value'];

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
							<?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

								<textarea
									name="<?php echo esc_attr( $value['field_name'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
									placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									><?php echo esc_textarea( $option_value ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
							</td>
						</tr>
						<?php
		            break;

				// Select boxes.
				case 'select':
				case 'multiselect':
					$option_value = $value['value'];

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<select
									name="<?php echo esc_attr( $value['field_name'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
                                    <?php echo $disabled ? 'disabled' : ''; ?>
								<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
									>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
										<option value="<?php echo esc_attr( $key ); ?>"
										<?php

										if ( is_array( $option_value ) ) {
											selected( in_array( (string) $key, $option_value, true ), true );
										} else {
											selected( $option_value, (string) $key );
										}

										?>
										><?php echo esc_html( $val ); ?></option>
										<?php
								}
								?>
								</select> <?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
		            break;

				// Radio inputs.
				case 'radio':
					$option_value    = $value['value'];
					$disabled_values = $value['disabled'] ?? array();

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<fieldset>
								<?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<ul>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
										<li>
											<label><input
												name="<?php echo esc_attr( $value['field_name'] ); ?>"
												value="<?php echo esc_attr( $key ); ?>"
												type="radio"
											<?php
                                            if ( in_array( $key, $disabled_values, true ) ) {
												echo 'disabled'; }
											?>
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
											<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<?php checked( $key, $option_value ); ?>
												/> <?php echo esc_html( $val ); ?></label>
										</li>
										<?php
								}
								?>
									</ul>
								</fieldset>
							</td>
						</tr>
						<?php
		            break;

				// Checkbox input.
				case 'checkbox':
					$option_value     = $value['value'];
					$visibility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
						$visibility_class[] = 'hidden_option';
					}
					if ( 'option' === $value['hide_if_checked'] ) {
						$visibility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' === $value['show_if_checked'] ) {
						$visibility_class[] = 'show_options_if_checked';
					}

					$must_disable = $value['disabled'] ?? false;

					if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
						$has_tooltip             = isset( $value['tooltip'] ) && '' !== $value['tooltip'];
						$tooltip_container_class = $has_tooltip ? 'with-tooltip' : '';
						?>
								<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
									<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
									<td class="forminp forminp-checkbox <?php echo esc_html( $tooltip_container_class ); ?>">
									<?php if ( $has_tooltip ) : ?>
                                        <span class="help-tooltip">
                                            <?php echo self::help_tip( esc_html( $value['tooltip'] ) ); //phpcs:ignore ?>
                                        </span>
									<?php endif; ?>
                                    <fieldset>
							<?php
					} else {
						?>
								<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
							<?php
					}

					if ( ! empty( $value['title'] ) ) {
						?>
								<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
							<?php
					}

					?>
							<label for="<?php echo esc_attr( $value['id'] ); ?>">
								<input
								<?php echo $must_disable ? 'disabled' : ''; ?>
									name="<?php echo esc_attr( $value['field_name'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									type="checkbox"
									class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
									value="1"
								<?php disabled( $value['disabled'] ?? false ); ?>
								<?php checked( $option_value, 'yes' ); ?>
								<?php echo implode( ' ', $custom_attributes ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								/> <?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</label> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php

						if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
							?>
										</fieldset>
									</td>
								</tr>
							<?php
						} else {
							?>
								</fieldset>
							<?php
						}
		            break;

				// Single page selects.
				case 'single_select_page':
					$args = array(
						'name'             => $value['field_name'],
						'id'               => $value['id'],
						'sort_column'      => 'menu_order',
						'sort_order'       => 'ASC',
						'show_option_none' => ' ',
						'class'            => $value['class'],
						'echo'             => false,
						'selected'         => absint( $value['value'] ),
						'post_status'      => 'publish,private,draft',
					);

					if ( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
					}

					?>
						<tr valign="top" class="single_select_page">
							<th scope="row" class="titledesc">
								<label><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp">
							<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'default' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $description; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
		            break;

				case 'single_select_page_with_search':
					$option_value = $value['value'];
					$page         = get_post( $option_value );

					if ( ! is_null( $page ) ) {
						$page                = get_post( $option_value );
						$option_display_name = sprintf(
							/* translators: 1: page name 2: page ID */
							__( '%1$s (ID: %2$s)', 'default' ),
							$page->post_title,
							$option_value
						);
					}
					?>
						<tr valign="top" class="single_select_page">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
							</th>
							<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
								<select
									name="<?php echo esc_attr( $value['field_name'] ); ?>"
									id="<?php echo esc_attr( $value['id'] ); ?>"
									style="<?php echo esc_attr( $value['css'] ); ?>"
									class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									data-placeholder="<?php esc_attr_e( 'Search for a page&hellip;', 'default' ); ?>"
									data-allow_clear="true"
									data-exclude="<?php echo self::esc_json( wp_json_encode( $value['args']['exclude'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
									>
									<option value=""></option>
								<?php if ( ! is_null( $page ) ) { ?>
										<option value="<?php echo esc_attr( $option_value ); ?>" selected="selected">
										<?php echo wp_strip_all_tags( $option_display_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</option>
									<?php } ?>
								</select> <?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
		            break;

				// Default: run an action.
				default:
					do_action( $slug . '_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

    /**
     * Get a setting from the settings API.
     *
     * @param  string $option_name Option name.
     * @param  mixed  $def_value   Default value.
     * @return mixed
     */
	public static function get_option( $option_name, $def_value = '' ) {
		if ( ! $option_name ) {
			return $def_value;
		}

		// Array value.
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key.
			$option_name = current( array_keys( $option_array ) );

			// Get value.
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			} else {
				$option_value = null;
			}
		} else {
			// Single value.
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = wp_unslash( $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $def_value : $option_value;
	}

    /**
     * Helper function to get the formatted description and tip HTML for a
     * given form field. Plugins can call this when implementing their own custom
     * settings types.
     *
     * @param  array $value The form field value array.
     * @return array The description and tip as a 2 element array.
     */
	public static function get_field_description( $value ) {
		$description  = '';
		$tooltip_html = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = self::help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

    /**
     * Save admin fields.
     *
     * Loops through the woocommerce options array and outputs each field.
     *
     * @param array  $options Options array to output.
     * @param array  $data    Optional. Data to use for saving. Defaults to $_POST.
     * @param string $slug    Slug.
     * @return bool
     */
	public static function save_fields( $options, $data = null, $slug ) {
		if ( is_null( $data ) ) {
			$data = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options   = array();
		$autoload_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ( isset( $option['is_option'] ) && false === $option['is_option'] ) ) {
				continue;
			}

			$option_name = $option['field_name'] ?? $option['id'];

			// Get posted value.
			if ( strstr( $option_name, '[' ) ) {
				parse_str( $option_name, $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$setting_name = '';
				$raw_value    = isset( $data[ $option_name ] ) ? wp_unslash( $data[ $option_name ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox':
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect':
				case 'multi_select_countries':
					$value = array_filter( array_map( 'wc_clean', (array) $raw_value ) );
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
					break;
				default:
					$value = self::clean( $raw_value );
					break;
			}

			/**
			 * Sanitize the value of an option.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( $slug . '_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( $slug . '_admin_settings_sanitize_option_' . $option_name, $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}

			$autoload_options[ $option_name ] = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
		}

		return true;
	}

    /**
     * Save the settings.
     *
     * @param string $slug Slug.
     */
	public static function save( $slug ) {
		global $current_tab;

		check_admin_referer( $slug . '-settings' );

		// Trigger actions.
		do_action( $slug . '_settings_save_' . $current_tab );
		do_action( $slug . '_update_options_' . $current_tab );
		do_action( $slug . '_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'default' ) );

		do_action( $slug . '_settings_saved' );
	}

	/**
	 * Display a WooCommerce help tip.
	 *
	 * @since  2.5.0
	 *
	 * @param  string $tip        Help tip text.
	 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
	 * @return string
	 */
	public static function help_tip( $tip, $allow_html = false ) {
		if ( $allow_html ) {
			$sanitized_tip = self::sanitize_tooltip( $tip );
		} else {
			$sanitized_tip = esc_attr( $tip );
		}

	    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		return sprintf(
            '<span class="plugin-manager-help-tip" tabindex="0" aria-label="%s" data-tip="%s"></span>',
            $sanitized_tip,
            $sanitized_tip,
            $tip,
            $allow_html
        );
	    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
     * Sanitize a string destined to be a tooltip.
     *
     * @param  string $tip Data to sanitize.
     * @return string
     */
	public static function sanitize_tooltip( $tip ) {
		return htmlspecialchars(
			wp_kses(
                html_entity_decode( $tip ?? '' ),
                array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'small'  => array(),
					'span'   => array(),
					'ul'     => array(),
					'li'     => array(),
					'ol'     => array(),
					'p'      => array(),
                )
            )
		);
	}

    /**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param  string|array $var_to_clean Data to sanitize.
     * @return string|array
     */
    public static function clean( $var_to_clean ) {
        if ( is_array( $var_to_clean ) ) {
            return array_map( 'wc_clean', $var_to_clean );
        } else {
            return is_scalar( $var_to_clean ) ? sanitize_text_field( $var_to_clean ) : $var_to_clean;
        }
    }

    /**
     * Escape JSON for use on HTML or attribute text nodes.
     *
     * @since 3.5.5
     * @param string $json JSON to escape.
     * @param bool   $html True if escaping for HTML text node, false for attributes. Determines how quotes are handled.
     * @return string Escaped JSON.
     */
	public static function esc_json( $json, $html = false ) {
		return _wp_specialchars(
			$json,
			$html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
			'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
			true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
		);
	}
}
