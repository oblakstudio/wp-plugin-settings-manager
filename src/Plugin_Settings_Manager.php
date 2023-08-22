<?php
/**
 * Plugin_Settings_Manager class file.
 *
 * @package Plugin_Settings
 */

namespace Oblak\WP;

/**
 * Manages plugin settings
 */
class Plugin_Settings_Manager {

    /**
     * Plugin namespace
     *
     * @var string
     */
    protected $slug;

    /**
     * Configuration array
     *
     * @var array
     */
    protected $config;


    /**
     * Admin Settigns loader
     *
     * @var Plugin_Admin_Settings
     */
    protected $settings;

    /**
     * Class constructor
     *
     * @param  string $slug Plugin namespace.
     * @param  array  $config    Configuration array.
     */
    public function __construct( $slug, $config = array() ) {
        $this->slug   = $slug;
        $this->config = $config;

        add_action( 'admin_init', array( $this, 'buffer' ) );
        add_action( 'plugin_action_links', array( $this, 'output_settings_link' ), 20, 2 );
        add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta' ), 20, 2 );
        add_action( 'admin_menu', array( $this, 'add_menu_pages' ), 10 );

        add_action( 'wp_loaded', array( $this, 'save_settings' ) );
    }

    /**
     * Output buffering allows us to redirect without having to worry about headers already being sent
     */
    public function buffer() {
        ob_start();
    }

    /**
     * Output settings link
     *
     * @param  array  $links       Array of Plugin links.
     * @param  string $plugin_file Plugin basename.
     * @return array               Modified array of Plugin links
     */
    public function output_settings_link( $links, $plugin_file ) {
        if ( $plugin_file !== $this->config['basename'] ) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s">%s</a>',
            add_query_arg(
                array(
                    'page' => $this->slug . '-settings',
                ),
                admin_url( 'admin.php' )
            ),
            esc_html__( 'Settings', 'default' ),
        );

        return $links;
    }

    /**
     * Add plugin meta links
     *
     * @param  string[] $plugin_meta Array of plugin meta links.
     * @param  string   $basename    Plugin basename.
     * @return string[]              Modified array of plugin meta links.
     */
    public function add_plugin_meta( $plugin_meta, $basename ) {
        if ( $basename !== $this->config['basename'] || empty( $this->config['meta'] ?? array() ) ) {
            return $plugin_meta;
        }

        foreach ( $this->config['meta'] as $meta ) {
            $plugin_meta[] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url( $meta['link'] ),
                esc_html( $meta['text'] ),
            );
        }

        return $plugin_meta;
    }

    /**
     * Adds the plugin menu pages
     */
    public function add_menu_pages() {
        global $admin_page_hooks;

        $config = $this->config['page'] ?? false;

        if ( false === $config ) {
            return;
        }

        if ( ! $this->config['page']['root'] ) {
            $settings_page = add_submenu_page(
                $config['parent'],
                $config['menu_title'] ?? $config['title'],
                $config['title'],
                $config['cap'] ?? 'manage_options',
                $this->slug . '-settings',
                array( $this, 'settings_page' ),
                $config['pos'] ?? null
            );

            add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
            return;
        }

        add_menu_page(
            $config['title'],
            $config['title'],
            $config['cap'] ?? 'manage_options',
            $this->slug,
            array( $this, 'settings_page' ),
            ! empty( $config['image'] )
                ? 'data:image/svg+xml;base64,' . base64_encode( $config['image'] ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
                : '',
            $config['prio'] ?? 51.34
        );

        // Work around https://github.com/woocommerce/woocommerce/issues/35677 (and related https://core.trac.wordpress.org/ticket/18857).
		// Translating the menu item breaks screen IDs and page hooks, so we force the hookname to be untranslated.
        $admin_page_hooks[ $this->slug ] = $this->slug; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        $settings_page = add_submenu_page(
            $this->slug,
            $config['title'],
            $config['menu_title'] ?? $config['title'],
            $config['cap'] ?? 'manage_options',
            $this->slug . '-settings',
            array( $this, 'settings_page' ),
            $config['pos'] ?? null
        );

        remove_submenu_page( $this->slug, $this->slug );

        add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
    }

    /**
     * Hooks into the settings page load
     */
    public function settings_page_init() {
        $this->settings->get_settings_pages();

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( ! empty( $_GET[ $this->slug . '_error' ] ) ) {
            $this->settings->add_error( wp_kses_post( wp_unslash( $_GET[ $this->slug . '_error' ] ) ) );
        }

        if ( ! empty( $_GET[ $this->slug . '_message' ] ) ) {
            $this->settings->add_message( wp_kses_post( wp_unslash( $_GET[ $this->slug . '_message' ] ) ) );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        /**
         * Fires when the settings page is initialized
         */
        do_action( $this->slug . '_settings_page_init' );
    }

    /**
     * Outputs the settings page HTML
     */
    public function settings_page() {
        $this->settings->output();
    }

    /**
     * Saves the settings
     */
    public function save_settings() {
        global $current_tab, $current_section;

        $this->settings = new Plugin_Admin_Settings( $this->slug, $this->config );

        //phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
        if ( ! is_admin() || ! isset( $_GET['page'] ) || $this->slug . '-settings' !== $_GET['page'] ) {
            return;
        }

        $this->settings->get_settings_pages();

        // Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) );

        // Save settings if data has been posted.
		if ( '' !== $current_section && apply_filters( "{$this->slug}_save_settings_{$current_tab}_{$current_section}", ! empty( $_POST['save'] ) ) ) {
			Plugin_Admin_Settings::save( $this->slug );
		} elseif ( '' === $current_section && apply_filters( "{$this->slug}_save_settings_{$current_tab}", ! empty( $_POST['save'] ) ) ) {
			Plugin_Admin_Settings::save( $this->slug );
		}
        //phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
    }
}
