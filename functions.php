<?php

add_action('wp_enqueue_scripts', 'porto_child_css', 1001);

// Load CSS
function porto_child_css()
{
	// porto child theme styles
	wp_deregister_style('styles-child');
	wp_register_style('styles-child', esc_url(get_stylesheet_directory_uri()) . '/style.css');
	wp_enqueue_style('styles-child');

	if (is_rtl()) {
		wp_deregister_style('styles-child-rtl');
		wp_register_style('styles-child-rtl', esc_url(get_stylesheet_directory_uri()) . '/style_rtl.css');
		wp_enqueue_style('styles-child-rtl');
	}
}

define('LABEL_CHECK_1', 'Optin to receive text message order alerts');
define('LABEL_CHECK_2', 'Sign Up for Newsletter');


add_action( 'woocommerce_edit_account_form', 'add_contact_phone_to_edit_account_form' ); // After existing fields
add_action( 'woocommerce_register_form', 'add_contact_phone_to_edit_account_form' ); // For registration page
function add_contact_phone_to_edit_account_form() {
    $user = wp_get_current_user();
    ?>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label class="font-weight-medium mb-1" for="contact_phone"><?php _e( 'Phone Number', 'woocommerce' ); ?> <span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text line-height-xl input-text" name="contact_phone" id="contact_phone" value="<?php echo esc_attr( $user->contact_phone ); ?>" />
    </p>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<input type="checkbox" name="contact_receive_order_alerts" id="contact_receive_order_alerts" <?php echo $user->contact_receive_order_alerts ? 'checked' : ''; ?> />
		<label class="font-weight-medium mb-1" for="contact_receive_order_alerts" style="display: inline-block;"><?php _e( LABEL_CHECK_1, 'porto' ); ?></label>
    </p>
	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<input type="checkbox" name="contact_receive_newsletter" id="contact_receive_newsletter" <?php echo $user->contact_receive_newsletter ? 'checked' : ''; ?> />
		<label class="font-weight-medium mb-1" for="contact_receive_newsletter" style="display: inline-block;"><?php _e( LABEL_CHECK_2, 'porto' ); ?></label>
    </p>
    <?php
}

// Check and validate the Phone Number
add_action( 'woocommerce_save_account_details_errors','contact_phone_field_validation', 20, 1 );
function contact_phone_field_validation( $args ){
    if ( isset($_POST['contact_phone']) && empty($_POST['contact_phone']) )
        $args->add( 'error', __( 'Phone number is a required field.', 'porto' ),'');
}

add_filter( 'woocommerce_registration_errors', 'contact_phone_registration_errors_validation', 10, 3 );
function contact_phone_registration_errors_validation( $reg_errors, $sanitized_user_login, $user_email ) {
    if ( isset($_POST['contact_phone']) && empty($_POST['contact_phone']) ) {
		return new WP_Error( 'registration-error', __( 'Phone number is a required field.', 'porto' ) );
	}
	return $reg_errors;
}


// Save the Phone Number value to user data
add_action( 'woocommerce_save_account_details', 'my_account_saving_contact_phone', 20, 1 );
function my_account_saving_contact_phone( $user_id ) {
    if( isset($_POST['contact_phone']) && ! empty($_POST['contact_phone']) ) {
		update_user_meta( $user_id, 'contact_phone', sanitize_text_field($_POST['contact_phone']) );
	}
	update_user_meta( $user_id, 'contact_receive_order_alerts', isset($_POST['contact_receive_order_alerts']) ? 1 : 0 );
	update_user_meta( $user_id, 'contact_receive_newsletter', isset($_POST['contact_receive_newsletter']) ? 1 : 0 );
}

add_action( 'woocommerce_created_customer', 'contact_phone_registration_save_data', 20, 3 );
function contact_phone_registration_save_data( $user_id, $new_customer_data, $password_generated ) {
    if ( isset($_POST['contact_phone']) && !empty($_POST['contact_phone']) ) {
        update_user_meta( $user_id, 'contact_phone', sanitize_text_field($_POST['contact_phone']) );
	}
	update_user_meta( $user_id, 'contact_receive_order_alerts', isset($_POST['contact_receive_order_alerts']) ? 1 : 0 );
	update_user_meta( $user_id, 'contact_receive_newsletter', isset($_POST['contact_receive_newsletter']) ? 1 : 0 );
}



add_action( 'show_user_profile', 'be_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'be_show_extra_profile_fields' );
function be_show_extra_profile_fields( $user ) {
?>
	<div id="extra_contact_info" style="padding: 10px 0;">
		<h3><?php _e('Extra Contact Information', 'porto') ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="contact_phone"><?php _e('Phone Number', 'woocommerce')?></label></th>
				<td>
					<input type="text" name="contact_phone" id="contact_phone" value="<?php echo esc_attr( get_the_author_meta( 'contact_phone', $user->ID ) ); ?>" class="regular-text" /><br />
					<span class="description">Please enter your phone number.</span>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="checkbox" name="contact_receive_order_alerts" id="contact_receive_order_alerts" <?php echo get_the_author_meta( 'contact_receive_order_alerts', $user->ID ) ? 'checked' : ''; ?>/>
					<label for="contact_receive_order_alerts"><?php _e( LABEL_CHECK_1, 'porto' ) ?></label>
				</td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td>
					<input type="checkbox" name="contact_receive_newsletter" id="contact_receive_newsletter" <?php echo get_the_author_meta( 'contact_receive_newsletter', $user->ID ) ? 'checked' : ''; ?>/>
					<label for="contact_receive_newsletter"><?php _e( LABEL_CHECK_2, 'porto' ) ?></label>
				</td>
			</tr>
		</table>
	</div>
<?php
}

add_action( 'personal_options_update', 'be_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'be_save_extra_profile_fields' );
function be_save_extra_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
	update_user_meta( $user_id, 'contact_phone', esc_attr( $_POST['contact_phone'] ) );
	update_user_meta( $user_id, 'contact_receive_order_alerts', isset($_POST['contact_receive_order_alerts']) ? 1 : 0 );
	update_user_meta( $user_id, 'contact_receive_newsletter', isset($_POST['contact_receive_newsletter']) ? 1 : 0 );
}
