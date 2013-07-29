<?php
/**
 * Unit tests for DirectoryWalker
 *
 * File:		test.DirectoryWalker.php
 * @package		DirectoryWalker
 * @subpackage	UnitTests
 * @version		1.0
 * @link		https://github.com/jrfnl/DirectoryWalker
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<simple.directory.walker@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <simple.directory.walker@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		Unit tests available since release 1.0
 */


if ( !defined( 'TEST_FILES_PATH' ) ) {
	/**
	 * Determine the path where files needed for the tests are placed
	 */
	define(
		'TEST_FILES_PATH',
		dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
	);
}

/**
 * Include the class to be tested
 */
require_once dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'class.DirectoryWalker.php';


/**
 * Unit tests for the DirectoryWalker class.
 *
 * @package		DirectoryWalker
 * @subpackage	UnitTests
 * @version		1.0
 * @link		https://github.com/jrfnl/DirectoryWalker
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<simple.directory.walker@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <simple.directory.walker@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		Unit tests available since release 1.0
 */
class DirectoryWalkerTests extends PHPUnit_Framework_TestCase {

	/**
	 * @var		array	$cache	Hold the expected results for comparison to assertion results
	 */
	protected $cache;

	/**
	 * @var		array	$exts	Hold the expected validated extensions results for comparison to assertion results
	 */
	protected $exts;

//	  protected function setUp() {}

	/**
	 * @covers DirectoryWalker::get_file_list()
	 * @covers DirectoryWalker::traverse_directory()
	 * @covers DirectoryWalker::validate_exts()
	 */
	public function test_get_file_list() {

		$d					   = DIRECTORY_SEPARATOR;
		$this->exts['css_php'] = $exts = array( 'css', 'php' );
		$ext_string 		   = implode( '_', $exts );

		// Are both cache empty to start with ?
		$this->assertEmpty( PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'cache' ) );
		$this->assertEmpty( PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'exts' ) );


		// Wrong path to directory
		$path = dirname( __FILE__ ) . $d . '_invalid' . $d;
		$this->assertFalse( DirectoryWalker::get_file_list( $path ) );
		unset( $path );

		// Empty directory
		$path = dirname( __FILE__ ) . $d . '_empty' . $d;
		$this->assertEmpty( DirectoryWalker::get_file_list( $path ) );
		$this->cache[$path][false]['all'] = array();
		unset( $path );


		// All files - non recursive
		$result = array(
			'file.css',
			'file.html',
			'file.php',
			'file.txt',
		);
		$this->cache[TEST_FILES_PATH][false]['all'] = $result;
		$this->assertEquals( $result, DirectoryWalker::get_file_list( TEST_FILES_PATH ) );

		// All files - recursive
		$result = array(
			'file.css',
			'file.html',
			'file.php',
			'file.txt',
			'subdir1' . $d . 'file.css',
			'subdir1' . $d . 'file.html',
			'subdir1' . $d . 'file.php',
			'subdir1' . $d . 'file.txt',
			'subdir1' . $d . 'subdir' . $d . 'file.css',
			'subdir1' . $d . 'subdir' . $d . 'file.html',
			'subdir1' . $d . 'subdir' . $d . 'file.php',
			'subdir1' . $d . 'subdir' . $d . 'file.txt',
			'subdir2' . $d . 'file.css',
			'subdir2' . $d . 'file.html',
			'subdir2' . $d . 'file.php',
			'subdir2' . $d . 'file.txt',
		);
		$this->cache[TEST_FILES_PATH][true]['all'] = $result;
		$this->assertEquals( $result, DirectoryWalker::get_file_list( TEST_FILES_PATH, true ) );

		// Selected extensions - non recursive
		$result = array(
			'file.css',
			'file.php',
		);

		$this->cache[TEST_FILES_PATH][false][$ext_string] = $result;
		$this->assertEquals( $result, DirectoryWalker::get_file_list( TEST_FILES_PATH, null, $exts ) );
		
		// Selected extensions - recursive
		$result = array(
			'file.css',
			'file.php',
			'subdir1' . $d . 'file.css',
			'subdir1' . $d . 'file.php',
			'subdir1' . $d . 'subdir' . $d . 'file.css',
			'subdir1' . $d . 'subdir' . $d . 'file.php',
			'subdir2' . $d . 'file.css',
			'subdir2' . $d . 'file.php',
		);
		$this->cache[TEST_FILES_PATH][true][$ext_string] = $result;
		$this->assertEquals( $result, DirectoryWalker::get_file_list( TEST_FILES_PATH, true, $exts ) );
		
		
		// Once more, but now with different extensions, provided in non-alphabetic order
		$exts = array( 'html', 'css' );
		sort( $exts );
		$ext_string 			 = implode( '_', $exts );
		$this->exts[$ext_string] = $exts;

		// Selected extensions - recursive
		$result = array(
			'file.css',
			'file.html',
			'subdir1' . $d . 'file.css',
			'subdir1' . $d . 'file.html',
			'subdir1' . $d . 'subdir' . $d . 'file.css',
			'subdir1' . $d . 'subdir' . $d . 'file.html',
			'subdir2' . $d . 'file.css',
			'subdir2' . $d . 'file.html',
		);
		$this->cache[TEST_FILES_PATH][true][$ext_string] = $result;
		$this->assertEquals( $result, DirectoryWalker::get_file_list( TEST_FILES_PATH, true, $exts ) );

		
		// Have all the results been cached ?
		$this->assertEquals( $this->cache, PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'cache' ) );

		// Have all the extension sets been cached ?
		$this->assertEquals( $this->exts, PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'exts' ) );


		/* These tests need to be run here as otherwise we have no cache to test against
		 - actually why don't we have that cache ? is that PHPUnit specific or is something else going wrong ?
		 => Looks like every test is being run against a new instance of this class, so we don't have *this* cache, cache in class being tested is there and correct.
		 May be use the @depends tag to fix this ? */

		// Has one specific cache been cleared ?
		unset( $this->cache[TEST_FILES_PATH][true]['all'] );
		DirectoryWalker::clear_file_list( TEST_FILES_PATH, true );
		$this->assertEquals( $this->cache, PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'cache' ) );
		
		// Has one specific cache been cleared ?
		unset( $this->exts['css_html'] );
		DirectoryWalker::clear_exts( array( 'html', 'css' ) );
		$this->assertEquals( $this->exts, PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'exts' ) );
	}
	

	/**
	 * @covers DirectoryWalker::is_allowed_file()
	 */
	public function test_is_allowed_file() {
		// No extensions given, all allowed
		$this->assertTrue( DirectoryWalker::is_allowed_file( 'test.php' ) );

		// With extension given
		$this->assertTrue( DirectoryWalker::is_allowed_file( 'test.php', 'php' ) );

		// With extension given
		$this->assertFalse( DirectoryWalker::is_allowed_file( 'test.php', 'css' ) );
		
		// With array of extensions given
		$this->assertTrue( DirectoryWalker::is_allowed_file( 'test.php', array( 'php', 'css' ) ) );

		// With array of extensions given
		$this->assertFalse( DirectoryWalker::is_allowed_file( 'test.php', array( 'css', 'html' ) ) );
	}


	/**
	 * @covers DirectoryWalker::clear_file_list()
	 */
	public function test_clear_file_list() {

		// Has the complete cache been cleared ?
		DirectoryWalker::clear_file_list();
		$this->assertEmpty( PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'cache' ) );
	}


	/**
	 * @covers DirectoryWalker::clear_valid_exts()
	 */
	public function test_clear_exts() {

		// Has the complete cache been cleared ?
		DirectoryWalker::clear_exts();
		$this->assertEmpty( PHPUnit_Framework_Assert::readAttribute( 'DirectoryWalker', 'exts' ) );
	}
}
?>