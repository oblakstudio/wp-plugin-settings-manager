<?php
/**
 * Admin View: Settings
 *
 * @package Plugin Settings Manager
 */

defined( 'ABSPATH' ) || exit;

$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';
$tab_exists        = isset( $tabs[ $current_tab ] ) ||
    has_action( $this->slug . '_sections_' . $current_tab ) ||
    has_action( $this->slug . '_settings_' . $current_tab );

if ( ! $tab_exists ) {
    // Prevent infinite redirect loop on no settings pages defined.
    if ( ! empty( $tabs ) ) {
	    wp_safe_redirect( admin_url( 'admin.php?page=' . $this->slug . '-settings' ) );
	    exit;
    }

    echo '<h1>NO TABS DEFINED</h1>';
    return;
}


/**
 * Defines the form method for the current tab for the current plugin
 */
$form_method = apply_filters( $this->slug . '_settings_form_method_tab_' . $current_tab, 'post' );
?>

<div class="wrap plugin-settings <?php echo esc_attr( $this->slug ); ?>">
    <?php
    /**
     * Fires before the settings page header.
     *
     * @since 1.0.0
     */
    do_action( $this->slug . 'before_settings_' . $current_tab );
    ?>
    <form method="<?php echo esc_attr( $form_method ); ?>" id="mainform" action="" enctype="multipart/form-data">
        <nav class="nav-tab-wrapper <?php echo esc_attr( $this->slug ); ?>-nav-tab-wrapper plugin-settings-nav-tab-wrapper">
            <?php
			foreach ( $tabs as $slug => $label ) {
                $url = add_query_arg(
                    array(
                        'page' => $this->slug . '-settings',
                        'tab'  => $slug,
                    ),
                    admin_url( 'admin.php' )
                );
                printf(
                    '<a href="%s" class="nav-tab %s">%s</a>',
                    esc_url( $url ),
                    $current_tab === $slug ? 'nav-tab-active' : '',
                    esc_html( $label )
                );
			}
            ?>
        </nav>

        <h1 class="screen-reader-text"> <?php echo esc_html( $current_tab_label ); ?> </h1>

        <?php
        do_action( $this->slug . '_sections_' . $current_tab );

        $this->show_messages();

        do_action( $this->slug . '_settings_' . $current_tab );
        ?>

        <p class="submit">
            <?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
                <button
                    name="save"
                    class="button-primary plugin-settings-save-button <?php echo esc_attr( $this->slug ); ?>-save-button"
                    type="submit"
                    value="<?php esc_attr_e( 'Save Changes', 'default' ); ?>"
                >
                    <?php esc_html_e( 'Save Changes', 'default' ); ?>
                </button>
                <?php wp_nonce_field( $this->slug . '-settings' ); ?>
            <?php endif; ?>
        </p>
    </form>

    <?php
    /**
     * Fires after the settings form
     *
     * @since 1.0.0
     */
    do_action( $this->slug . 'after_settings_' . $current_tab );
    ?>
</div>
