<?php
/**
 * Simple Directory Walker
 *
 * Walks through a directory and retrieves the names of all files and directories
 * - Optionally recursively walks child-directories
 * - Optionally filter the retrieved list for files with comply with a list of allowed extensions
 * - Results are cached for best performance
 *
 * File:		class.DirectoryWalker.php
 * @package		DirectoryWalker
 * @version		1.0
 * @link		https://github.com/jrfnl/DirectoryWalker
 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *				<simple.directory.walker@adviesenzo.nl>
 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <simple.directory.walker@adviesenzo.nl> All rights reserved
 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @since		2013-07-03
 */
if ( !class_exists( 'DirectoryWalker' ) ) {
	/**
	 * Simple Directory Walker
	 *
	 * @package		DirectoryWalker
	 * @version		1.0
	 * @link		https://github.com/jrfnl/DirectoryWalker
	 * @author		Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
	 *				<simple.directory.walker@adviesenzo.nl>
	 * @copyright	(c) 2013, Advies en zo, Meedenken en -doen <simple.directory.walker@adviesenzo.nl>
	 *				All rights reserved
	 * @license		http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
	 */
	class DirectoryWalker {

		/**
		 * Version number of this class
		 * @type	string	VERSION		version number of this class
		 */
		const VERSION = '1.0';

		/**
		 * @static
		 * @var		array	$cache		cache of retrieved file lists
		 */
		protected static $cache = array();

		/**
		 * @static
		 * @var		array	$exts		cache of validated groups of allowed extensions
		 */
		protected static $exts = array();


		/**
  		 * Retrieve the (cached) file list for path
  		 *
  		 * @uses	DirectoryWalker::$cache
  		 * @uses	DirectoryWalker::validate_exts()
  		 * @uses	DirectoryWalker::traverse_directory()
  		 *
  		 * @static
  		 *
		 * @param	string			$path
		 * @param	bool|null		$recursive
		 * @param	string|array	$allowed_extensions
		 * @return	array
		 */
		public static function get_file_list( $path, $recursive = false, $allowed_extensions = null ) {

			// Validate and prep received parameters
			if ( !is_bool( $recursive ) ) {
				$recursive = false;
			}

			$ext_string = 'all';
			$allowed_extensions = self::validate_exts( $allowed_extensions );
			if ( isset( $allowed_extensions ) ) {
				$ext_string = implode( '_', $allowed_extensions );
			}

			// Retrieve the file list if not in cache yet
			if ( !isset( self::$cache[$path][$recursive][$ext_string] ) ) {
				if ( count( $allowed_extensions ) > 0 ) {
					self::$cache[$path][$recursive][$ext_string] = self::traverse_directory( $path, $recursive, $allowed_extensions );
				}
				else {
					self::$cache[$path][$recursive][$ext_string] = self::traverse_directory( $path, $recursive );
				}
			}

			return self::$cache[$path][$recursive][$ext_string];
		}


		/**
		 * Traverse a directory listing and return an array with file names
		 *
		 * Purposefully ignores directory entries starting with a '.' so as to prevent 'unsafe'
		 * files, such as .htaccess and higher directories getting in the list.
		 * Note: this also means that directories such as /.git/ and /.idea/ will be ignored too.
		 *
  		 * @uses	DirectoryWalker::traverse_directory()
  		 * @uses	DirectoryWalker::is_allowed_file()
  		 *
  		 * @static
  		 *
		 * @param	string			$path
		 * @param	bool			$recursive
		 * @param	string|array	$allowed_extensions
		 * @param	string			$prefix			internal use
		 * @param	array			$file_list		internal use
		 * @return	array
		 */
		private static function traverse_directory( $path, $recursive = false, $allowed_extensions = null, $prefix = '', $file_list = array() ) {

			$slash = ( strrchr( $path, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );
			$list  = scandir( $path );

			if ( is_array( $list ) && count( $list ) > 0 ) {
				foreach ( $list as $filename ) {
					// Skip if the file is an 'unsafe' one such as .htaccess or
					// higher directory references
					if ( strpos( $filename, '.' ) !== 0 ) {
						$path_to_file = $path . $slash . $filename;

						// If it's a file, check against valid extensions and add to the list
						if ( is_file( $path_to_file ) === true ) {
							if ( !isset( $allowed_extensions ) ) {
								$file_list[] = $prefix . $filename;
							}
							else if ( self::is_allowed_file( $filename, $allowed_extensions ) === true ) {
								$file_list[] = $prefix . $filename;
							}
						}
						// If it's a directory and recursive is true, run this function on the subdirectory
						else if ( is_dir( $path_to_file ) === true && $recursive === true ) {
							$file_list = self::traverse_directory( $path_to_file . DIRECTORY_SEPARATOR, $recursive, $allowed_extensions, $prefix . $filename . DIRECTORY_SEPARATOR, $file_list );
						}

						unset( $path_to_file );
					}
					unset( $filename );
				}
			}

			return $file_list;
		}


		/**
		 * Check if a file name has one of the allowed extensions
		 * Allows for extensions such as tar.gz
		 *
  		 * @uses	DirectoryWalker::validate_exts()
  		 *
  		 * @static
		 *
		 * @param	string	$file_name
		 * @param	array	$allowed_extensions
		 * @return	bool
		 */
		public static function is_allowed_file( $file_name, $allowed_extensions = null ) {

			$allowed_extensions = self::validate_exts( $allowed_extensions );

			// All extensions allowed
			if ( ! isset( $allowed_extensions ) ) {
				return true;
			}
			
			// Otherwise we will have a valid array of strings as extensions are prepped
			foreach ( $allowed_extensions as $ext ) {
				/* @todo Should this use the below first version to get round different character lengths
				 in utf-8 strings with lower- and higher case ? *test* */
/*				$file_name = strtolower( $file_name );
				$pos = strrpos( $file_name, '.' . $ext );*/

				$pos = strripos( $file_name, '.' . $ext );
				if ( ( strlen( $file_name ) - strlen( $ext ) - 1 ) === $pos ) {
					return true;
				}
			}

			return false;
		}


		/**
		 * Validate and type cast the passed $allowed_extensions
		 *
		 * @todo - 	Determine if we need to cache invalid lists => null as well,
		 * 			to avoid those having to validate over and over ?
		 *
  		 * @uses	DirectoryWalker::$exts
  		 *
  		 * @static
		 *
		 * @param	string|array|null	$allowed_extensions
		 * @return	array|null
		 */
		private static function validate_exts( $allowed_extensions = null ) {
			
			// Break quickly if there's nothing to validate
			if ( !isset( $allowed_extensions ) ) {
				return $allowed_extensions;
			}

			// Check the cache
			if ( is_array( $allowed_extensions ) && sort( $allowed_extensions ) ) {
				$ext_string = implode( '_', $allowed_extensions );
				if ( isset( self::$exts[$ext_string] ) ) {
					return self::$exts[$ext_string];
				}
			}
			
			/* Validate */

			// Make sure it's an array
			if ( is_string( $allowed_extensions ) && $allowed_extensions !== '' ) {
				$allowed_extensions = explode( ',', $allowed_extensions );
			}
			else {
				$allowed_extensions = (array) $allowed_extensions;
			}
			
			// Nothing there, break
			if ( count( $allowed_extensions ) === 0 ) {
				return null;
			}
			
			// Validate the values
			$clean = array();
			foreach ( $allowed_extensions as $ext ) {
				if ( is_string( $ext ) ) {
					// Strip out a . at the start
					$ext = trim( ltrim( $ext, '.' ) );
					// Make the array content consistent
					$ext = strtolower( $ext );

					if ( $ext !== '' ) {
						$clean[] = $ext;
					}
				}
			}
			
			// Nothing valid found, break
			if ( count( $clean ) === 0 ) {
				return null;
			}

			// Cache the result & return it
			sort( $clean );
			$ext_string = implode( '_', $clean );
			self::$exts[$ext_string] = $clean;

			return $clean;
		}


		/**
		 * Clear the file list cache for one set of parameters or clear the complete cache if no path is given
		 *
  		 * @uses	DirectoryWalker::$cache
  		 * @uses	DirectoryWalker::validate_exts()
  		 *
  		 * @static
		 *
		 * @param	string			$path
		 * @param	bool			$recursive
		 * @param	string|array	$allowed_extensions
		 * @return	void
		 */
		public static function clear_file_list( $path = null, $recursive = false, $allowed_extensions = null ) {

			// Validate and prep received parameters
			if ( !is_bool( $recursive ) ) {
				$recursive = false;
			}

			$ext_string = 'all';
			$allowed_extensions = self::validate_exts( $allowed_extensions );
			if ( isset( $allowed_extensions ) ) {
				$ext_string = implode( '_', $allowed_extensions );
			}

			// Clear (selected) cache
			if ( is_string( $path ) ) {
				unset( self::$cache[$path][$recursive][$ext_string] );
			}
			else {
				self::$cache = array();
			}
		}


		/**
		 * Clear the validated extensions cache for one set of extensions or clear the complete cache if no set given
		 *
  		 * @uses	DirectoryWalker::$exts
  		 *
  		 * @static
  		 *
		 * @param	string|array	$allowed_extensions
		 * @return	void
		 */
		public static function clear_exts( $allowed_extensions = null ) {
			// Check the cache
			if ( ( isset( $allowed_extensions ) && is_array( $allowed_extensions ) ) && sort( $allowed_extensions ) ) {
				$ext_string = implode( '_', $allowed_extensions );
				unset( self::$exts[$ext_string] );
			}
			else {
				self::$exts = array();
			}
		}
	} /* End of class */
} /* End of class-exists wrapper */
?>