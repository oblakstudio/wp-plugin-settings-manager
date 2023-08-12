<?php
/**
 * Plugin_Settings_Page class file.
 *
 * @package Plugin_Settings
 */

namespace Oblak\WP;

/**
 * Handles the output of the plugin settings
 */
abstract class Plugin_Settings_Page {

    /**
     * Plugin slug
     *
     * @var string
     */
    protected $slug;

    /**
     * Setting page id.
     *
     * @var string
     */
    protected $id = '';

    /**
     * Setting page label.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( $this->slug . '_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_filter( $this->slug . '_get_settings_' . $this->id, array( $this, 'format_section_settings' ), PHP_INT_MAX - 1, 2 );
        add_action( $this->slug . '_sections_' . $this->id, array( $this, 'output_sections' ) );
        add_action( $this->slug . '_settings_' . $this->id, array( $this, 'output' ) );
        add_action( $this->slug . '_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get settings page slug.
     *
     * @return string
     */
    public function get_slug() {
        return $this->slug;
    }

    /**
     * Get settings page ID.
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get settings page label.
     *
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * Add this page to settings.
     *
     * @param array $pages The settings array where we'll add ourselves.
     *
     * @return mixed
     */
    public function add_settings_page( $pages ) {
        $pages[ $this->id ] = $this->label;

        return $pages;
    }

	/**
		 * Get settings array.
		 *
		 * The strategy for getting the settings is as follows:
		 *
		 * - If a method named 'get_settings_for_{section_id}_section' exists in the class
		 *   it will be invoked (for the default '' section, the method name is 'get_settings_for_default_section').
		 *   Derived classes can implement these methods as required.
		 *
		 * - Otherwise, 'get_settings_for_section_core' will be invoked. Derived classes can override it
		 *   as an alternative to implementing 'get_settings_for_{section_id}_section' methods.
		 *
		 * @param string $section_id The id of the section to return settings for, an empty string for the default section.
		 *
		 * @return array Settings array, each item being an associative array representing a setting.
		 */
    final public function get_settings_for_section( $section_id ) {
        if ( '' === $section_id ) {
            $method_name = 'get_settings_for_default_section';
        } else {
            $method_name = "get_settings_for_{$section_id}_section";
        }

        if ( method_exists( $this, $method_name ) ) {
            $settings = $this->$method_name();
        } else {
            $settings = $this->get_settings_for_section_core( $section_id );
        }

        return apply_filters( $this->slug . '_get_settings_' . $this->id, $settings, $section_id );
    }

    /**
     * Applies prefixes to the field IDs.
     *
     * @param  array[] $settings   The settings array.
     * @param  string  $section_id The section ID.
     * @return array[]             The formatted settings array.
     */
    final public function format_section_settings( $settings, $section_id ) {
        $formatted      = array();
        $section_suffix = count( $this->get_own_sections() ) > 1
            ? ( '' === $section_id ? 'core' : "{$section_id}" )
            : '';

        foreach ( $settings as $field ) {
            if ( in_array( $field['type'], array( 'title', 'sectionend' ), true ) ) {
                $formatted[] = $field;
                continue;
            }

            $field_key           = isset( $field['name'] ) ? 'name' : 'id';
            $field[ $field_key ] = $this->format_field_id( $section_suffix, $field[ $field_key ] );

            $formatted[] = $field;
        }

        return $formatted;
    }

    /**
     * Formats the field ID.
     *
     * @param  string $suffix The section suffix.
     * @param  string $id     The field ID.
     * @return string         The formatted field ID.
     */
    private function format_field_id( $suffix, $id ) {
        return sprintf(
            '%s_%s%s[%s]',
            $this->slug,
            $this->id,
            '' !== $suffix ? "_{$suffix}" : '',
            $id
        );
    }

    /**
     * Get the settings for a given section.
     * This method is invoked from 'get_settings_for_section' when no 'get_settings_for_{current_section}_section'
     * method exists in the class.
     *
     * When overriding, note that the $this->slug . '_get_settings_' filter must NOT be triggered,
     * as this is already done by 'get_settings_for_section'.
     *
     * @param string $section_id The section name to get the settings for.
     *
     * @return array Settings array, each item being an associative array representing a setting.
     */
	protected function get_settings_for_section_core( $section_id ) { //phpcs:ignore
		return array();
	}

    /**
     * Get all sections for this page, both the own ones and the ones defined via filters.
     *
     * @return array
     */
	public function get_sections() {
		$sections = $this->get_own_sections();
		return apply_filters( $this->slug . '_get_sections_' . $this->id, $sections );
	}

    /**
     * Get own sections for this page.
     * Derived classes should override this method if they define sections.
     * There should always be one default section with an empty string as identifier.
     *
     * Example:
     * return array(
     *   ''        => __( 'General', 'woocommerce' ),
     *   'foobars' => __( 'Foos & Bars', 'woocommerce' ),
     * );
     *
     * @return array An associative array where keys are section identifiers and the values are translated section names.
     */
    protected function get_own_sections() {
		return array( '' => __( 'General', 'default' ) );
	}

    /**
     * Output sections.
     */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
            $url       = add_query_arg(
                array(
                    'page'    => $this->slug . '-settings',
                    'tab'     => $this->id,
                    'section' => $id,
                ),
                admin_url( 'admin.php' )
            );
			$class     = ( $current_section === $id ? 'current' : '' );
			$separator = ( end( $array_keys ) === $id ? '' : '|' );
			$text      = esc_html( $label );
			echo "<li><a href='$url' class='$class'>$text</a> $separator </li>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</ul><br class="clear" />';
	}

    /**
     * Output the HTML for the settings.
     */
	public function output() {
		global $current_section;

		$settings = $this->get_settings_for_section( $current_section );

		Plugin_Admin_Settings::output_fields( $settings, $this->slug );
	}

    /**
     * Save settings and trigger the 'woocommerce_update_options_'.id action.
     */
	public function save() {
		$this->save_settings_for_current_section();
		$this->do_update_options_action();
	}

    /**
     * Save settings for current section.
     */
	protected function save_settings_for_current_section() {
		global $current_section;

		$settings = $this->get_settings_for_section( $current_section );

		Plugin_Admin_Settings::save_fields( $settings, null, $this->slug );
	}

    /**
     * Trigger the 'woocommerce_update_options_'.id action.
     *
     * @param string $section_id Section to trigger the action for, or null for current section.
     */
	protected function do_update_options_action( $section_id = null ) {
		global $current_section;

		if ( is_null( $section_id ) ) {
			$section_id = $current_section;
		}

		if ( $section_id ) {
			do_action( $this->slug . '_update_options_' . $this->id . '_' . $section_id );
		}
	}
}
