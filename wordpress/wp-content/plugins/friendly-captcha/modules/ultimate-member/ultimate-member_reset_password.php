<?php

add_action( 'um_after_password_reset_fields', 'frcaptcha_um_reset_password_show_widget', 500 );

function frcaptcha_um_reset_password_show_widget() {
    $plugin = FriendlyCaptcha_Plugin::$instance;
    if (!$plugin->is_configured() or !$plugin->get_um_reset_password_active()) {
        return;
    }

    echo frcaptcha_generate_widget_tag_from_plugin($plugin);

    // it just slightly overflows..
    echo '<style>.frc-captcha {max-width:100%; margin-bottom: 1em}</style>';

    frcaptcha_enqueue_widget_scripts();
}

add_action( 'um_reset_password_errors_hook', 'frcaptcha_um_reset_password_validate', 20 );	

function frcaptcha_um_reset_password_validate($post) {
    if ( isset( $post['mode'] ) && $post['mode'] == 'password') {
        $plugin = FriendlyCaptcha_Plugin::$instance;
        if (!$plugin->is_configured() or !$plugin->get_um_reset_password_active()) {
            return;
        }

        $errorPrefix = '<strong>' . __( 'Error', 'frcaptcha' ) . '</strong> : ';
        $solution = frcaptcha_get_sanitized_frcaptcha_solution_from_post();
        
        if ( empty( $solution ) ) {
            $error_message = $errorPrefix . FriendlyCaptcha_Plugin::default_error_user_message() . __(' (captcha missing)', 'frcaptcha');
            UM()->form()->add_error( 'frcaptcha', $error_message );
            return;
        }

        $verification = frcaptcha_verify_captcha_solution($solution, $plugin->get_sitekey(), $plugin->get_api_key());

        if (!$verification['success']) {
            $error_message = $errorPrefix . FriendlyCaptcha_Plugin::default_error_user_message();
            UM()->form()->add_error( 'frcaptcha', $error_message );
            return;
        }
    }
}
