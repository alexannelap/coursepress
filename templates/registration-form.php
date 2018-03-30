<?php
/**
 * The template use for CoursePress custom registration form.
 *
 * @since 2.0.5
 **/

$signup_tag         = get_query_var( 'signup_tag' );
$signup_title       = get_query_var( 'signup_title' );
$form_message       = get_query_var( 'form_message' );
$form_message_class = get_query_var( 'form_message_class' );
$redirect_url       = get_query_var( 'redirect_url' );
$login_url          = get_query_var( 'login_url' );
$course_id          = ! empty( $_GET['course_id'] ) ? (int) $_GET['course_id'] : 0;
$username           = ! empty( $_POST['username'] ) ? $_POST['username'] : '';
$first_name         = ! empty( $_POST['first_name'] ) ? $_POST['first_name'] : '';
$last_name          = ! empty( $_POST['last_name'] ) ? $_POST['last_name'] : '';
$email              = ! empty( $_POST['email'] ) ? $_POST['email'] : '';

$submit_button = __( 'Create an Account', 'cp' );
$action_url    = admin_url( 'admin-ajax.php?action=coursepress_register' );

if ( coursepress_get_cookie( 'cp_mismatch_password' ) ) {
	$form_message = __( 'Mismatch password!', 'cp' );
} elseif ( coursepress_get_cookie( 'cp_profile_updated' ) ) {
	$form_message = __( 'Profile successfully updated!', 'cp' );
}

if ( is_user_logged_in() ) {
	$user          = coursepress_get_user();
	$first_name    = $user->__get( 'first_name' );
	$last_name     = $user->__get( 'last_name' );
	$email         = $user->__get( 'user_email' );
	$submit_button = __( 'Update Changes', 'cp' );
	$action_url    = admin_url( 'admin-ajax.php?action=coursepress_update_profile' );
}
?>
<div class="coursepress-form coursepress-form-signup">
	<?php if ( ! is_user_logged_in() && ! empty( $signup_title ) ) : ?>
		<?php printf( '<%1$s>%2$s</%1$s>', $signup_tag, $signup_title ); ?>
	<?php endif; ?>
	<p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>

	<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form" action="<?php //echo $action_url; ?>">
		<?php
		/**
		 * Trigger before the signup form.
		 **/
		do_action( 'coursepress_before_signup_form' );

		/**
		 * Trigger before signup fields are printed.
		 **/
		do_action( 'coursepress_before_all_signup_fields' );
		?>
		<input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
		<input type="hidden" name="redirect_url" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<label class="firstname">
			<span><?php _e( 'First Name', 'cp' ); ?>:</span>
			<input type="text" name="first_name" value="<?php echo esc_attr( $first_name ); ?>"/>
		</label>
		<?php
		/**
		 * Trigger after first_name field.
		 **/
		do_action( 'coursepress_after_signup_first_name' );
		?>

		<label class="lastname">
			<span><?php _e( 'Last Name', 'cp' ); ?>:</span>
			<input type="text" name="last_name" value="<?php echo esc_attr( $last_name ); ?>"/>
		</label>
		<?php
		/**
		 * Trigger after last_name field.
		 **/
		do_action( 'coursepress_after_signup_last_name' );

		if ( ! is_user_logged_in() ) :
		?>

		<label class="username">
			<span><?php _e( 'Username', 'cp' ); ?>:</span>
			<input type="text" name="username" value="<?php echo esc_attr( $username ); ?>" />
		</label>
		<?php

		/**
		 * Trigger after printing username.
		 **/
		do_action( 'coursepress_after_signup_username' );
		endif;
		?>

		<label class="email">
			<span><?php _e( 'E-mail', 'cp' ); ?>:</span>
			<input type="text" name="email" value="<?php echo esc_attr( $email ); ?>" />
		</label>
		<?php
		/**
		 * Trigger after email field.
		 **/
		do_action( 'coursepress_after_signup_email' );
		?>

		<label class="password">
			<span><?php _e( 'Password', 'cp' ); ?>:</span>
			<input type="password" name="password" value=""/>
		</label>
		<?php
		/**
		 * Trigger after password field.
		 **/
		do_action( 'coursepress_after_signup_password' );
		?>

        <p>
		<label class="password-confirm right">
			<span><?php _e( 'Confirm Password', 'cp' ); ?>:</span>
			<input type="password" name="password_confirmation" value=""/>
		</label>
		<label class="weak-password-confirm">
			<input type="checkbox" name="confirm_weak_password" value="1" />
			<span><?php _e( 'Confirm use of weak password', 'cp' ); ?></span>
        </label>
        </p>

		<?php if ( shortcode_exists( 'signup-tos' ) && '1' == get_option( 'show_tos', 0 ) ) : ?>
			<label class="tos full">
				<?php echo do_shortcode( '[signup-tos]' ); ?>
			</label>
		<?php endif; ?>

		<?php
		/**
		 * Trigger after all signup fields are rendered.
		 **/
		do_action( 'coursepress_after_all_signup_fields' );

		if ( ! is_user_logged_in() ) :
		?>
<p>
		<label class="existing-link full">
			<?php printf( __( 'Already have an account? %s%s%s!', 'cp' ), '<a href="' . esc_url( $login_url ) . '">', __( 'Login to your account', 'cp' ), '</a>' ); ?>
        </label>
</p>
        <?php endif; ?>
<p>
		<label class="submit-link full-right">
			<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php echo $submit_button; ?>" />
		</label>
</p>
		<?php

		/**
		 * Trigger when registration form submitted.
		 **/
		do_action( 'coursepress_after_submit' );

		wp_nonce_field( 'coursepress_nonce', '_wpnonce', true );

		/**
		 * Trigger after all signform fields are printed.
		 **/
		do_action( 'coursepress_after_signup_form' );
		?>
	</form>
</div>
