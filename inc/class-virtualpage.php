<?php
/**
 * Class CoursePress_VirtualPage
 *
 * @since 2.0
 * @package CoursePress
 */
final class CoursePress_VirtualPage extends CoursePress_Utility {
	/**
	 * @var array
	 */
	protected $breadcrumb = array();

	/**
	 * @var array
	 */
	protected $templates = array(
		'archive' => 'archive-course.php',
		'unit-archive' => 'archive-unit.php',
		'workbook' => 'page-course-workbook.php',
		'notifications' => 'page-course-notifications.php',
		'forum' => 'page-course-discussion.php',
		'grades' => 'page-course-grades.php',
		'instructor' => 'course-instructor.php',
		'single-course' => 'single-course.php',
		'archive-course' => 'archive-course.php',
		'student-dashboard' => 'page-student-dashboard.php',
		'student-settings' => 'page-student-settings.php',
		'unit' => 'single-unit.php',
		'module' => 'single-unit.php',
		'step' => 'single-unit.php',
		'completion-status' => 'page-course-completion.php',
	);

	/**
	 * CoursePress_VirtualPage constructor.
	 *
	 * @param $array
	 */
	public function __construct( $array ) {
		if ( is_array( $array ) )
			foreach ( $array as $key => $value )
				$this->__set( $key, $value );

		// Setup CP template
		add_filter( 'template_include', array( $this, 'load_coursepress_page' ) );

		// Set dummy post object on selected template
		add_filter( 'posts_results', array( $this, 'set_post_object' ), 10, 2 );
	}

	/**
	 * Helper method to check if the current theme have CoursePress template.
	 * @param $type
	 *
	 * @return bool|string
	 */
	private function has_template( $type ) {
		if ( ! empty( $this->templates[ $type ] ) ) {
			$template = locate_template( $this->templates[ $type ], false, false );

			if ( $template )
				return $template;
		}

		return false;
	}

	private function get_post_id_by_slug( $slug ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT ID FROM `{$wpdb->posts}` WHERE `post_name`=%s", $slug );

		$post_id = $wpdb->get_var( $sql );

		return $post_id;
	}

	private function add_breadcrumb( $title, $url ) {
		$breadcrumbs = $this->__get( 'breadcrumb' );
		$attr = array( 'href' => esc_url( $url ) );
		$breadcrumbs[] = $this->create_html( 'a', $attr, $title );

		$this->__set( 'breadcrumb', $breadcrumbs );
	}

	private function get_template( $type ) {
		global $CoursePress, $CoursePress_Instructor, $wp_query, $CoursePress_Course, $CoursePress_Unit,
			$_course_module_id, $_course_module, $_course_step;

		$course = false;

		if ( ! empty( $this->__get( 'course' )
		              || 'single-course' == $type ) ) {
			//$course_id = $this->get_post_id_by_slug( $this->__get( 'course' ) );
			$CoursePress_Course = $course = coursepress_get_course();


			//echo get_the_ID();
			//$CoursePress_Course = coursepress_get_course( get_the_ID() );

		}

		$template = $CoursePress->plugin_path . '/templates/';
		$template .= $this->templates[ $type ];

		if ( 'instructor' == $type ) {
			$instructor = $wp_query->get( 'instructor' );
			$user = get_user_by( 'login', $instructor );

			if ( $user ) {
				$CoursePress_Instructor = new CoursePress_Instructor( $user );
			}
		} elseif ( in_array( $type, array( 'unit', 'module', 'step' ) ) ) {
			$this->add_breadcrumb( $CoursePress_Course->get_the_title(), $CoursePress_Course->get_permalink() );

			$unit = $this->__get( 'unit' );
			$unit_id = $this->get_post_id_by_slug( $unit );

			if ( $unit_id > 0 ) {
				$CoursePress_Unit = new CoursePress_Unit( $unit_id );
				$this->add_breadcrumb( $CoursePress_Unit->get_the_title(), $CoursePress_Unit->get_unit_url() );
				$_course_module_id = 1; // always start module with 1

				$module = $this->__get( 'module' );

				if ( ! empty( $module ) ) {
					$module = $CoursePress_Unit->get_module_by_slug( $module );

					if ( ! empty( $module ) ) {
						$_course_module_id = $module['id'];
						$_course_module = $module;
						$this->add_breadcrumb( $module['title'], $module['url'] );
					}
				} else {
					$_course_module = $CoursePress_Unit->get_module_by_id( 1 );
				}

				$step = $this->__get( 'step' );

				if ( ! empty( $step ) ) {
					$step_id = $this->get_post_id_by_slug( $step );

					if ( $step_id > 0 ) {
						$_course_step = $stepClass = $CoursePress_Unit->get_step_by_id( $step_id );

						if ( ! is_wp_error( $stepClass ) ) {
							$this->add_breadcrumb( $stepClass->get_the_title(), $stepClass->get_permalink() );
						}
					}
				}
			}
		}

		return $template;
	}

	function load_coursepress_page() {
		$type = $this->__get( 'type' );
		$template = $this->has_template( $type );


		if ( ! $template ) {
			// If the theme did not override the template, load CP template
			$page_template = $this->get_template( $type );
		} else {
			$page_template = $template;
		}

		return $page_template;
	}

	private function the_post( $post, $args = array() ) {

		foreach ( $args as $key => $value )
			$post->{$key} = $value;

		$post->comment_status = 'closed';
		$post->post_status = 'publish';

		return $post;
	}

	function set_post_object( $posts, $wp ) {
		if ( ! $wp->is_main_query() )
			return $posts;

		$type = $this->__get( 'type' );
		$post = array_shift( $posts );

		if ( 'student-dashboard' == $type ) {
			$post = $this->the_post( $post, array(
				'post_title' => __( 'My Courses', 'cp' ),
				'post_type' => 'page',
			) );

		} elseif ( 'student-settings' == $type ) {
			$post = $this->the_post( $post, array(
				'post_title' => __( 'My Settings', 'cp' ),
				'post_type' => 'page',
			) );

		}

		array_unshift( $posts, $post );

		return $posts;
	}
}