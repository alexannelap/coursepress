<?php
/**
 * @var int $user_id
 * @var int $course
 */
$data = coursepress_get_disscusions( $course );
if ( empty( $data ) ) {
	printf(
		'<p class="message">%s</p>',
		__( 'This course does not have any discussions.', 'cp' )
	);
} else {
	echo '<ul class="cp-discussion-archive-list">';
	foreach ( $data as $discussion ) {
		echo '<li>';
		$comments_count = wp_count_comments( $discussion->ID );
		$author = coursepress_get_user_name( $discussion->post_author, false, false );
		if ( 'course' === $discussion->unit_id ) {
			$applies_to = get_post_field( 'post_title', $discussion->course_id );
		} else {
			$applies_to = get_post_field( 'post_title', $discussion->unit_id );
		}
		$date = get_the_date( get_option( 'date_format' ), $discussion );
		echo '
                    <div class="cp-discussion-archive-single">
                        <h3 class="cp-discussion-title"><a href="' . esc_url( $discussion->url ) . '">' . esc_html( $discussion->post_title ) . '</a></h3>
                        <div class="cp-discussion-content">
                            ' . $discussion->post_content . '
                        </div>
                        <div class="meta"><span>' .__( 'Started by: ', 'cp' ). '</span>' .esc_html( $author ) . ' | ' . esc_html( $date ) . ' | <span>' . esc_html__( 'Applies to:', 'cp' ) . '</span> ' . $applies_to . '</div>
                    </div>
            ';
		echo '<div class="cp-discussion-responces">';
		echo '<span class="cp-screen">';
		if ( 0 == $comments_count->approved ) {
			_e( '0 Responses', 'cp' );
		} else {
			$c = sprintf( '<span>%d</span>', $comments_count->approved );
			printf(
				_n( '%s Response', '%s Responses', $comments_count->approved, 'cp' ),
				$c
			);
		}
		echo '</span>';
		echo '<span class="cp-mobile">';
		printf(
			__( 'Responses (%d)', 'cp' ),
			$comments_count->approved
		);
		echo '</span>';
		echo '</div>';
		echo ' </li>';
	}
	echo '</ul>';

}
