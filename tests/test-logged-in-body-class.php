<?php
/**
 * Tests for the `is-logged-in` body class added in functions.php.
 *
 * The repository has no WordPress install or Composer autoloader, so the two
 * WordPress functions that functions.php touches are stubbed here and the file
 * is loaded directly. Run with:
 *
 *     php tests/test-logged-in-body-class.php
 *
 * @package my-wp-project
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$GLOBALS['stub_filters']        = array();
$GLOBALS['stub_user_logged_in'] = false;

/**
 * Stub of WordPress `add_filter()`: records the callback so tests can run it.
 *
 * @param string   $hook     Filter name.
 * @param callable $callback Filter callback.
 * @return true
 */
function add_filter( $hook, $callback ) {
	$GLOBALS['stub_filters'][ $hook ][] = $callback;

	return true;
}

/**
 * Stub of WordPress `is_user_logged_in()`, toggled by the tests.
 *
 * @return bool
 */
function is_user_logged_in() {
	return $GLOBALS['stub_user_logged_in'];
}

require_once ABSPATH . 'functions.php';

/**
 * Run every callback registered on `body_class` over a set of classes.
 *
 * @param string[] $classes Incoming body classes.
 * @return string[] Filtered body classes.
 */
function run_body_class_filters( array $classes ) {
	$callbacks = isset( $GLOBALS['stub_filters']['body_class'] ) ? $GLOBALS['stub_filters']['body_class'] : array();

	foreach ( $callbacks as $callback ) {
		$classes = call_user_func( $callback, $classes );
	}

	return $classes;
}

$failures = 0;

/**
 * Assert a condition, printing the result and recording failures.
 *
 * @param bool   $condition Condition under test.
 * @param string $message   Description of the expected behaviour.
 * @return void
 */
function check( $condition, $message ) {
	global $failures;

	if ( $condition ) {
		echo "ok   - {$message}\n";
		return;
	}

	++$failures;
	echo "FAIL - {$message}\n";
}

check(
	isset( $GLOBALS['stub_filters']['body_class'] ) && count( $GLOBALS['stub_filters']['body_class'] ) === 1,
	'functions.php registers a single callback on the body_class filter'
);

$existing = array( 'home', 'logged-in-theme-class', 'page-id-7' );

$GLOBALS['stub_user_logged_in'] = true;
$logged_in                      = run_body_class_filters( $existing );

check(
	in_array( 'is-logged-in', $logged_in, true ),
	'a logged-in user gets the is-logged-in class'
);
check(
	array_slice( $logged_in, 0, count( $existing ) ) === $existing,
	'existing classes are preserved, in order, for a logged-in user'
);
check(
	count( $logged_in ) === count( $existing ) + 1,
	'exactly one class is added for a logged-in user'
);

$GLOBALS['stub_user_logged_in'] = false;
$logged_out                     = run_body_class_filters( $existing );

check(
	! in_array( 'is-logged-in', $logged_out, true ),
	'a logged-out user does not get the is-logged-in class'
);
check(
	$logged_out === $existing,
	'body classes are untouched for a logged-out user'
);

$GLOBALS['stub_user_logged_in'] = true;
check(
	run_body_class_filters( array() ) === array( 'is-logged-in' ),
	'an empty class list gains only is-logged-in for a logged-in user'
);

echo "\n";

if ( $failures > 0 ) {
	echo "{$failures} test(s) failed\n";
	exit( 1 );
}

echo "All tests passed\n";
