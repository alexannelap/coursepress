<?php
/**
 * Class CoursePress_Data_Core
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Core extends CoursePress_Utility {
	var $courses = array();
	var $users = array();
	var $students = array();
	var $instructors = array();

	protected $course_post_type = 'course';
	protected $unit_post_type = 'unit';
	protected $step_post_type = 'module';
	protected $discussions_post_type = 'discussions';
	protected $category_type = 'course_category';
	protected $notification_post_type = 'cp_notification';

	public function __construct() {
		// Register CP post types
		add_action( 'init', array( $this, 'register_post_types' ) );
		// Set capabilities.
		CoursePress_Data_Capabilities::init();
		// If front end set schema.
		if ( ! is_admin() ) {
			CoursePress_Data_Schema::init();
			CoursePress_Data_Instructor::init();
			CoursePress_Data_Facilitator::init();
		}
		// Initialize unsubscribe
		add_action( 'init', array( $this, 'init_unsubscribe' ) );
		// Initialize email alerts.
		add_action( 'init', array( $this, 'init_email_alerts' ) );
		// Register CP query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		// Add CP rewrite rules
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
		// Listen to comment submission
		add_filter( 'comments_open', array( $this, 'comments_open' ), 10, 2 );
		/**
		 * try to regenerate missing pdf
		 */
		add_action( 'template_redirect', array( $this, 'regenerate_pdf' ) );
		add_action( 'parse_request', array( $this, 'course_signup' ), 1 );
		/**
		 * add image size
		 */
		add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );
	}

	/**
	 * theme setup
	 *
	 * @since 3.0.0
	 */
	public function theme_setup() {
		$width = coursepress_get_setting( 'course/image_width', 220 );
		$height = coursepress_get_setting( 'course/image_height', 220 );
		add_image_size( 'course-thumbnail', $width, $height, true );
	}

	/**
	 * Try to regenerate Certificate file.
	 *
	 * Try to regenerate Certificate file in case of missing.
	 *
	 * @since 3.0.0
	 */
	public function regenerate_pdf() {
		if ( ! is_404() ) {
			return;
		}
		$current_url = lib3()->net->current_url();
		if ( ! preg_match( '@(pdf-cache/)([a-z0-9\/]+\.pdf)$@', $current_url, $matches ) ) {
			return;
		}
		if ( isset( $_REQUEST['try'] ) ) {
			return;
		}
		$certificate = new CoursePress_Certificate();
		$result = $certificate->try_to_regenerate( $matches[2] );
		if ( $result ) {
			$url = add_query_arg( 'try', 'yes' );
			wp_redirect( $url );
		}
	}

	public function register_post_types() {
		// Course
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		register_post_type(
			$this->course_post_type,
			array(
				'public' => true,
				'label' => __( 'All Courses', 'cp' ),
				'show_ui' => false,
				'show_in_nav_menu' => false,
				'has_archive' => true,
				'can_export' => false, // CP have it's own export mechanism
				'delete_with_user' => false,
				'rewrite' => array(
					'slug' => $course_slug,
					'with_front' => false,
				),
				'support' => array( 'comments' ),
				'labels' => array(
					'add_new_item' => __( 'New Course', 'cp' ),
					'edit_item' => __( 'Edit Course', 'cp' ),
					'new_item' => __( 'New Course', 'cp' ),
					'view_item' => __( 'View Course', 'cp' ),
					'view_items' => __( 'View Courses', 'cp' ),
					'search_items' => __( 'Search Courses', 'cp' ),
					'not_found' => __( 'No courses found.', 'cp' ),
					'not_found_in_trash' => __( 'No courses found in Trash.', 'cp' ),
					'attributes' => __( 'Course Attributes', 'cp' ),
					'insert_into_item' => __( 'Insert into course', 'cp' ),
					'uploaded_to_this_item' => __( 'Uploaded to this course', 'cp' ),
					'filter_items_list' => __( 'Filter courses list', 'cp' ),
					'items_list_navigation' => __( 'Courses list navigation', 'cp' ),
					'items_list' => __( 'Courses list', 'cp' ),
				),
			)
		);
		$category_slug = coursepress_get_setting( 'slugs/category', 'course_category' );
		register_taxonomy( $this->category_type,
			array( $this->course_post_type ),
			array(
				'public' => true,
				'rewrite' => array(
					'slug' => $category_slug,
					'with_front' => false,
				),
				'labels' => array(
					'name' => __( 'Categories', 'cp' ),
					'singular_name' => __( 'Category', 'cp' ),
					'search_items' => __( 'Search Course Categories', 'cp' ),
					'all_items' => __( 'All Course Categories', 'cp' ),
					'edit_item' => __( 'Edit Course Categories', 'cp' ),
					'update_item' => __( 'Update Course Category', 'cp' ),
					'add_new_item' => __( 'Add New Course Category', 'cp' ),
					'new_item_name' => __( 'New Course Category Name', 'cp' ),
				),
				'capabilities' => array(
					'manage_terms' => 'coursepress_course_categories_manage_terms_cap',
					'edit_terms' => 'coursepress_course_categories_edit_terms_cap',
					'delete_terms' => 'coursepress_course_categories_delete_terms_cap',
					'assign_terms' => 'coursepress_courses_cap',
				),
			)
		);
		// Unit
		register_post_type( $this->unit_post_type, array(
			'public' => false,
			'can_export' => false,
			'label' => 'Units', // debugging only,
			'query_var' => false,
			'publicly_queryable' => false,
			'supports' => array( 'thumbnail', 'comments' ),
			//'show_ui' => true,
			'hierarchical' => true,
		) );
		// Module
		register_post_type( $this->step_post_type, array(
			'public' => false,
			'show_ui' => false,
			'can_export' => false,
			'label' => 'Modules', // dbugging only
			'query_var' => false,
			'publicly_queryable' => false,
			'support' => array( 'comments' ),
			'hierarchical' => true,
		) );
		// Certificate
		register_post_type( 'cp_certificate', array(
			'public' => false,
			'show_ui' => false,
			'can_export' => false,
		) );
		// Notifications.
		register_post_type( $this->notification_post_type, array(
			'public' => true,
			'show_ui' => false,
			'can_export' => false,
			'show_in_nav_menu' => false,
			'has_archive' => true,
			'query_var' => false,
			'publicly_queryable' => false,
		) );
		// Discussions
		register_post_type( $this->discussions_post_type, array(
			'public' => false,
			'show_ui' => false,
			'can_export' => false,
			'label' => 'Discussions', // dbugging only
			'query_var' => false,
			'publicly_queryable' => false,
			'support' => array( 'comments' ),
			'hierarchical' => true,
		) );
	}

	/**
	 * Initialize unsubscribe action.
	 *
	 * @return void
	 */
	public function init_unsubscribe() {
		$unsubscribe_helper = new CoursePress_Data_Unsubscribe();
		$unsubscribe_helper->init();
	}

	/**
	 * Initialize email alerts.
	 *
	 * @return void
	 */
	public function init_email_alerts() {
		global $cp_coursepress;
		// Initialize Email alerts
		$emailAlerts = $cp_coursepress->get_class( 'CoursePress_Cron_EmailAlert' );
		// Initialize email alert crons.
		$emailAlerts->init();
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'coursepress';
		$vars[] = 'unit';
		$vars[] = 'coursename';
		$vars[] = 'module';
		$vars[] = 'step';
		$vars[] = 'instructor';
		$vars[] = 'topic';
		$vars[] = 'notification';
		return $vars;
	}

	public function add_rewrite_rules( $rules ) {
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		$unit_slug = coursepress_get_setting( 'slugs/units', 'units' );
		$step_slug = coursepress_get_setting( 'slugs/step', 'step' );
		$workbook_slug = coursepress_get_setting( 'slugs/workbook', 'workbook' );
		$notification_slug = coursepress_get_setting( 'slugs/notifications', 'notifications' );
		$discussion_slug = coursepress_get_setting( 'slugs/discussions', 'discussions' );
		$new_discussion_slug = coursepress_get_setting( 'slugs/discussions_new', 'new-discussion' );
		$grade_slug = coursepress_get_setting( 'slugs/grades', 'grades' );
		$instructor_slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );
		$student_dashboard = coursepress_get_setting( 'slugs/student_dashboard', 'courses-dashboard' );
		$student_settings = coursepress_get_setting( 'slugs/student_settings', 'student-settings' );
		$student_login = coursepress_get_setting( 'slugs/login', 'student-login' );
		$base = '^' . $course_slug . '/([^/]*)/';
		$new_rules = array(
			// Course completion
			$base . 'completion/almost-there/?' => 'index.php?coursename=$matches[1]&coursepress=completion-status',
			$base . 'completion/success/?' => 'index.php?coursename=$matches[1]&coursepress=completion-status',
			$base . 'completion/failed/?' => 'index.php?coursename=$matches[1]&coursepress=completion-status',
			$base . 'completion/validate/?' => 'index.php?coursename=$matches[1]&coursepress=completion',
			// Unit
			$base . $unit_slug . '/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&coursepress=unit',
			$base . $unit_slug . '/([^/]*)/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&module=$matches[3]&coursepress=module',
			$base . $unit_slug . '/([^/]*)/([^/]*)/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&module=$matches[3]&step=$matches[4]&coursepress=step',
			$base . $unit_slug . '/([^/]*)/([^/]*)/?$' => 'index.php?coursename=$matches[1]&unit=$matches[2]&module=$matches[3]&coursepress=module',
			// nits archive
			$base . $unit_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=unit-archive',
			/**
			 * no module course
			 */
			$base . $step_slug .'/([^/]+)/?$' => 'index.php?coursename=$matches[1]&module=$matches[2]&coursepress=step',
			// Workbook
			$base . $workbook_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=workbook',
			// Notifications
			$base . $notification_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=notifications',
			/**
			 * Forum | Discussions
			 */
			$base . $discussion_slug . '/?$' => 'index.php?coursename=$matches[1]&coursepress=forum',
			$base . $discussion_slug . '/' . $new_discussion_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=forum&topic=new',
			$base . $discussion_slug . '/([^/]*)/?' => 'index.php?coursename=$matches[1]&coursepress=forum&topic=$matches[2]',
			// Grades
			$base . $grade_slug . '/?' => 'index.php?coursename=$matches[1]&coursepress=grades',
			// Course Instructor Profile
			'^' . $instructor_slug . '/([^/]*)/?' => 'index.php?coursepress=instructor&instructor=$matches[1]',
			// Student Dashboard
			'^' . $student_dashboard . '/?' => 'index.php?coursepress=student-dashboard',
			// Student Settings
			'^' . $student_settings . '/?' => 'index.php?coursepress=student-settings',
			// Student Login
			'^' . $student_login . '/?' => 'index.php?coursepress=student-login',
		);
		return array_merge( $new_rules, $rules );
	}

	public function comments_open( $is_open, $object_id ) {
		$post_type = get_post_type( $object_id );
		$post_types = array( 'course', 'unit', 'module', 'discussions' );
		if ( in_array( $post_type, $post_types ) ) {
			return true;
		}
		return $is_open;
	}

	/**
	 * Login user - we need do it in parse_request action, because when
	 * shortcode is parsed, then it is too late to set auth cookie.
	 */
	public static function course_signup() {
		if ( ! isset( $_POST['log'] ) || ! isset( $_POST['pwd'] ) ) {
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}
		// Attempt a login if submitted.
		$user = $_POST['log'];
		if ( preg_match( '/@/', $user ) ) {
			$userdata = get_user_by( 'email', $user );
			$user = $userdata->user_login;
		}
		$credentials = array(
			'user_login' => $user,
			'user_password' => $_POST['pwd'],
		);
		$auth = wp_signon( $credentials );
		if ( ! is_wp_error( $auth ) ) {
			/**
			 * redirect contributors+ to dashboard
			 */
			$userdata = get_user_by( 'login', $user );
			if ( user_can( $userdata, 'edit_posts' ) ) {
				wp_safe_redirect( admin_url() );
				exit;
			}
			if ( isset( $_POST['redirect_url'] ) ) {
				wp_safe_redirect( urldecode( esc_url_raw( $_POST['redirect_url'] ) ) );
			} elseif ( isset( $_POST['redirect_to'] ) ) {
				wp_safe_redirect( urldecode( esc_url_raw( $_POST['redirect_to'] ) ) );
			} else {
				wp_redirect( esc_url_raw( coursepress_get_dashboard_url() ) );
			}
			exit;
		}
		add_filter( 'cp_course_signup_form_show_messages', '__return_true' );
	}
}
