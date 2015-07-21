<?php

class CoursePress_Helper_Settings_Email {


	public static function get_defaults( $context = false ) {

		$defaults = apply_filters( 'coursepress_default_email_settings', array(
			'basic_certificate' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => CoursePress_View_Admin_Settings_BasicCertificate::default_email_subject(),
				'content' => CoursePress_View_Admin_Settings_BasicCertificate::default_email_content(),
				'auto_email' => true,
			),
			'registration' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Registration Status', CoursePress::TD ),
				'content' => self::_registration_email(),
			),
			'enrollment_confirm' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Enrollment Confirmation', CoursePress::TD ),
				'content' => self::_enrollment_confirmation_email(),
			),
			'course_invitation' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Invitation to a Course', CoursePress::TD ),
				'content' => self::_course_invitation_email(),
			),
			'course_invitation_password' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', CoursePress::TD ),
				'content' => self::_course_invitation_passcode_email(),
			),
			'instructor_invitation' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => sprintf( __( 'Invitation to be an instructor at %s', CoursePress::TD ), get_option( 'blogname' ) ),
				'content' => self::_instructor_invitation_email(),
			),
			'new_order' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Order Confirmation', CoursePress::TD ),
				'content' => self::_new_order_email(),
			),

		) );


		if( $context && isset( $defaults[ $context ] ) ) {
			return $defaults[ $context ];
		} else {
			return $defaults;
		}

	}

	public static function get_settings_sections() {
		$defaults = apply_filters( 'coursepress_default_email_settings_sections', array(
			'basic_certificate' => array(
				'title' => __( 'Basic Certificate E-mail', CoursePress::TD ),
				'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST', CoursePress::TD ),
				'order' => 7,
			),
			'registration' => array(
				'title' => __( 'User Registration E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon account registration.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, STUDENT_USERNAME, STUDENT_PASSWORD, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS', CoursePress::TD ),
				'order' => 1,
			),
			'enrollment_confirm' => array(
				'title' => __( 'Course Enrollment Confirmation E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon enrollment.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS', CoursePress::TD ),
				'order' => 2,
			),
			'course_invitation' => array(
				'title' => __( 'Student Invitation to a Course E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS', CoursePress::TD ),
				'order' => 3,
			),
			'course_invitation_password' => array(
				'title' => __( 'Student Invitation to a Course E-mail (with passcode)', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, PASSCODE', CoursePress::TD ),
				'order' => 4,
			),
			'instructor_invitation' => array(
				'title' => __( 'Instructor Invitation Email', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: INSTRUCTOR_FIRST_NAME, INSTRUCTOR_LAST_NAME, INSTRUCTOR_EMAIL, CONFIRMATION_LINK, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, WEBSITE_NAME', CoursePress::TD ),
				'order' => 5,
			),
			'new_order' => array(
				'title' => __( 'New Order E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon placing an order.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', CoursePress::TD ),
				'order' => 6,
			),

		) );

		return $defaults;
	}

	private static function _registration_email() {
		return CoursePress_Core::get_setting('email/registration/content',
			sprintf( __( 'Hi %1$s,

Congratulations! You have registered account with %2$s successfully! You may log into your account here: %3$s.

Get started by exploring our courses here: %4$s

Yours sincerely,
%5$s Team', CoursePress::TD ), 'STUDENT_FIRST_NAME', 'BLOG_NAME', '<a href="LOGIN_ADDRESS">LOGIN_ADDRESS</a>', '<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>' )
		);
	}

	private static function _enrollment_confirmation_email() {
		return CoursePress_Core::get_setting('email/enrollment_confirm/content',
			sprintf( __( 'Hi %1$s,

Congratulations! You have enrolled in course "%2$s" successfully!

You may check all courses you are enrolled in here: %3$s.

Or you can explore other courses in your %4$s

Yours sincerely,
%5$s Team', CoursePress::TD ), 'STUDENT_FIRST_NAME', '<a href="COURSE_ADDRESS">COURSE_TITLE</a>', '<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', CoursePress::TD ) . '</a>', '<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>', 'BLOG_NAME' )
		);
	}

	private static function _course_invitation_email() {
		return CoursePress_Core::get_setting('email/course_invitation/content',
			sprintf( __( 'Hi %1$s,

we would like to invite you to participate in the course: "%2$s"

What is all about:
%3$s

Check this page for more info on the course: %4$s

If you have any question feel free to contact us.

Yours sincerely,
%5$s Team', CoursePress::TD ), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', '<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>' )
		);
	}

	private static function _course_invitation_passcode_email() {
		return CoursePress_Core::get_setting('email/course_invitation_password/content',
			sprintf( __( 'Hi %1$s,

we would like to invite you to participate in the course: "%2$s"

Since the course is only for selected ones, it is passcode protected. Here is the passcode for you: %6$s

What is all about:
%3$s

Check this page for more info on the course: %4$s

If you have any question feel free to contact us.

Yours sincerely,
%5$s Team', CoursePress::TD ), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', '<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>', 'PASSCODE' )
		);
	}

	private static function _instructor_invitation_email() {
		return CoursePress_Core::get_setting('email/instructor_invitation/content',
			sprintf( __(
				'Hi %1$s,

Congratulations! You have been invited to become an instructor for the course: %2$s

Click on the link below to confirm:

%3$s

If you haven\'t yet got a username you will need to create one.

%4$s
	', CoursePress::TD ), 'INSTRUCTOR_FIRST_NAME', 'COURSE_NAME', '<a href="CONFIRMATION_LINK">CONFIRMATION_LINK</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>')
		);
	}

	private static function _new_order_email() {
		return CoursePress_Core::get_setting('email/new_order/content',
			sprintf( __( 'Thank you for your order %1$s,

Your order for course "%2$s" has been received!

Please refer to your Order ID (ORDER_ID) whenever contacting us.

You can track the latest status of your order here: ORDER_STATUS_URL

Yours sincerely,
%5$s Team', CoursePress::TD ), 'CUSTOMER_NAME', '<a href="COURSE_ADDRESS">COURSE_TITLE</a>', '<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', CoursePress::TD ) . '</a>', '<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>', 'BLOG_NAME' )
		);
	}



}