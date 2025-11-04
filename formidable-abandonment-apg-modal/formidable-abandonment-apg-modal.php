<?php
/*
Plugin Name: Formidable Abandonment APG Modal
Description: Companion plugin that enhances the Formidable Abandonment modal to follow the WAI-ARIA APG modal dialog pattern without modifying core files.
Version: 0.1.7
Author: Adem Cifcioglu
License: GPL-2.0-or-later
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue the APG modal enhancer on the frontend.
 * It safely no-ops until a modal is present.
 */
function frm_abdn_apg_enqueue() {
    if ( is_admin() ) {
        return;
    }
    // Only load when the Abandonment addon is active.
    if ( ! class_exists( 'FrmAbandonmentAppController' ) && ! class_exists( 'FrmAbandonmentObserverController' ) ) {
        return;
    }
    wp_enqueue_script(
        'frm-abdn-apg-modal',
        plugins_url( 'assets/apg-modal.js', __FILE__ ),
        array(),
        '0.1.6',
        true
    );
    // Pass configurable strings to JS.
    $error_message = get_option( 'frm_abdn_apg_error_message', '' );
    $title         = get_option( 'frm_abdn_apg_title', '' );
    $description   = get_option( 'frm_abdn_apg_description', '' );
    $label         = get_option( 'frm_abdn_apg_label', '' );
    $button        = get_option( 'frm_abdn_apg_button', '' );
    $close         = get_option( 'frm_abdn_apg_close', '' );
    wp_localize_script(
        'frm-abdn-apg-modal',
        'FrmAbdnApg',
        array(
            'errorMessage' => $error_message,
            'title'        => $title,
            'description'  => $description,
            'label'        => $label,
            'button'       => $button,
            'close'        => $close,
        )
    );
}
// Run after the addon enqueues its script in wp_footer at priority 10, and before core prints at ~20.
add_action( 'wp_footer', 'frm_abdn_apg_enqueue', 12 );

/**
 * Admin: Settings page to customize the error message.
 */
function frm_abdn_apg_admin_menu() {
    add_options_page(
        'Formidable Abandonment APG Modal',
        'Abandonment APG Modal',
        'manage_options',
        'frm-abdn-apg',
        'frm_abdn_apg_render_settings_page'
    );
}
add_action( 'admin_menu', 'frm_abdn_apg_admin_menu' );

function frm_abdn_apg_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_POST['frm_abdn_apg_save'] ) ) {
        check_admin_referer( 'frm_abdn_apg_save_settings' );
        $msg   = isset( $_POST['frm_abdn_apg_error_message'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_error_message'] ) ) : '';
        $title = isset( $_POST['frm_abdn_apg_title'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_title'] ) ) : '';
        $desc  = isset( $_POST['frm_abdn_apg_description'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_description'] ) ) : '';
        $label = isset( $_POST['frm_abdn_apg_label'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_label'] ) ) : '';
        $button= isset( $_POST['frm_abdn_apg_button'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_button'] ) ) : '';
        $close = isset( $_POST['frm_abdn_apg_close'] ) ? sanitize_text_field( wp_unslash( $_POST['frm_abdn_apg_close'] ) ) : '';
        update_option( 'frm_abdn_apg_error_message', $msg );
        update_option( 'frm_abdn_apg_title', $title );
        update_option( 'frm_abdn_apg_description', $desc );
        update_option( 'frm_abdn_apg_label', $label );
        update_option( 'frm_abdn_apg_button', $button );
        update_option( 'frm_abdn_apg_close', $close );
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $current_error = get_option( 'frm_abdn_apg_error_message', '' );
    $current_title = get_option( 'frm_abdn_apg_title', '' );
    $current_desc  = get_option( 'frm_abdn_apg_description', '' );
    $current_label = get_option( 'frm_abdn_apg_label', '' );
    $current_button= get_option( 'frm_abdn_apg_button', '' );
    $current_close = get_option( 'frm_abdn_apg_close', '' );
    ?>
    <div class="wrap">
        <h1>Abandonment APG Modal Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'frm_abdn_apg_save_settings' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_title">Modal title</label></th>
                    <td>
                        <input type="text" class="regular-text" id="frm_abdn_apg_title" name="frm_abdn_apg_title" value="<?php echo esc_attr( $current_title ); ?>" />
                        <p class="description">Overrides the modal title text.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_description">Modal description</label></th>
                    <td>
                        <textarea class="large-text" rows="3" id="frm_abdn_apg_description" name="frm_abdn_apg_description"><?php echo esc_textarea( $current_desc ); ?></textarea>
                        <p class="description">Overrides the modal description text.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_label">Email label</label></th>
                    <td>
                        <input type="text" class="regular-text" id="frm_abdn_apg_label" name="frm_abdn_apg_label" value="<?php echo esc_attr( $current_label ); ?>" />
                        <p class="description">Overrides the email field label.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_button">Button text</label></th>
                    <td>
                        <input type="text" class="regular-text" id="frm_abdn_apg_button" name="frm_abdn_apg_button" value="<?php echo esc_attr( $current_button ); ?>" />
                        <p class="description">Overrides the primary button text.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_close">Close label</label></th>
                    <td>
                        <input type="text" class="regular-text" id="frm_abdn_apg_close" name="frm_abdn_apg_close" value="<?php echo esc_attr( $current_close ); ?>" />
                        <p class="description">Overrides the accessible label for the close control.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="frm_abdn_apg_error_message">Error message text</label></th>
                    <td>
                        <input type="text" class="regular-text" id="frm_abdn_apg_error_message" name="frm_abdn_apg_error_message" value="<?php echo esc_attr( $current_error ); ?>" />
                        <p class="description">Overrides the modal email validation error text.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="frm_abdn_apg_save" class="button button-primary">Save Changes</button>
            </p>
        </form>
    </div>
    <?php
}
