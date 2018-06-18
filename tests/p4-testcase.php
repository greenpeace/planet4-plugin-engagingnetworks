<?php

/**
 * Class P4_TestCase.
 */
class P4_TestCase extends WP_UnitTestCase {

	function setUp() {

		parent::setUp();
		self::_setupPlugin();
		require_once( get_template_directory() . '/functions.php' );
	}


	static function _setupPlugin() {
		$dest = WP_CONTENT_DIR . '/plugins/planet4-plugin-engagingnetworks/';
		$src  = __DIR__ . '/../../planet4-plugin-engagingnetworks/';
		if ( is_dir( $src ) ) {
			self::_copyDirectory( $src, $dest );
		}
	}

	static function _copyDirectory( $src, $dst ) {
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::_copyDirectory( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}

}
