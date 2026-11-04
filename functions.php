<?php
/**
 * Theme functions.
 *
 * @package my-wp-project
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the `is-logged-in` class to the body tag for logged-in users.
 *
 * Appends only: every class WordPress or another filter already supplied is
 * passed through untouched.
 *
 * @param string[] $classes Body classes.
 * @return string[] Body classes, with `is-logged-in` appended when a user is logged in.
 */
function my_wp_project_logged_in_body_class( $classes ) {
	if ( is_user_logged_in() ) {
		$classes[] = 'is-logged-in';
	}

	return $classes;
}
add_filter( 'body_class', 'my_wp_project_logged_in_body_class' );
