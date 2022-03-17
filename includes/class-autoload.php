<?php
/**
 * Class for auto loading the classes, interfaces and traits.
 *
 * @package woowgallery
 * @author  Sergey Pasyuk
 */

namespace WoowGallery;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

use Exception;

try {
	spl_autoload_register( [ 'WoowGallery\Autoload', 'load_class' ] );
} catch ( Exception $e ) {
	wp_die( 'App Core Error' );
}

/**
 * Class Autoload
 */
class Autoload {

	/**
	 * File system directory separator.
	 *
	 * @var string
	 */
	const DIR_SEPARATOR = '/';

	/**
	 * Namespace mapping list:
	 * [{
	 *    namespace: string,
	 *    path: string
	 * }]
	 *
	 * @var array
	 */
	private static $namespaces = [];

	/**
	 * Add namespace mapping.
	 *
	 * @param string $namespace     Class / Interface namespace to autoload.
	 * @param string $absolute_path Namespace location.
	 */
	public static function add_namespace( $namespace, $absolute_path ) {

		$path = rtrim( $absolute_path, self::DIR_SEPARATOR );

		// Sanitize and validate path.
		if ( 0 !== validate_file( $path ) ) {
			return;
		}

		// Sanitize and validate namespace.
		$namespace = trim( $namespace, '\\' );

		// Check if both are not empty.
		if ( empty( $namespace ) || empty( $path ) ) {
			return;
		}

		// Add to list.
		self::$namespaces[] = (object) [
			'namespace' => $namespace,
			'path'      => $path,
		];

		// Sort namespaces by name length to reduce number of operations.
		usort(
			self::$namespaces,
			function ( $a, $b ) {
				return strlen( $b->namespace ) - strlen( $a->namespace );
			}
		);
	}

	/**
	 * Try to autoload class by it's name.
	 *
	 * @param string $class_name Class / Interface to find.
	 */
	public static function load_class( $class_name ) {

		// Sanitize class / interface name.
		$class_name = trim( $class_name, '\\' );
		if ( empty( $class_name ) ) {
			return;
		}

		// Try to find path.
		foreach ( self::$namespaces as $map ) {

			// Check if class belongs to the namespace.
			if ( 0 !== strpos( $class_name, $map->namespace ) ) {
				continue;
			}
			// Remove namespace from class.
			$class = substr( $class_name, strlen( $map->namespace ) + 1 );
			if ( empty( $class ) ) {
				continue;
			}

			// Try to include actual file.
			if ( self::include_class( $class, $map->path ) ) {
				break; // The class was loaded - exit the loop.
			}
		}
	}

	/**
	 * Try to include class / interface file.
	 *
	 * @param string $class          Class / Interface in the namespace.
	 * @param string $namespace_path Namespace path.
	 *
	 * @return boolean Flag states if class was loaded or not.
	 */
	protected static function include_class( $class, $namespace_path ) {

		// Check if there are namespaces registered.
		if ( empty( self::$namespaces ) ) {
			return false;
		}

		// Split path into dirs and files.
		$parts      = explode( '\\', $class );
		$parts      = array_map( [ __CLASS__, 'sanitize_file' ], $parts );
		$class_name = array_pop( $parts );

		// Check if it's class or interface or trait.
		$int_str = '-interface';
		$int_pos = strlen( $class_name ) - strlen( $int_str );

		$trait_str = '-trait';
		$trait_pos = strlen( $class_name ) - strlen( $trait_str );

		if ( strpos( $class_name, $int_str ) === $int_pos ) {
			$file_name = 'interface-' . substr( $class_name, 0, $int_pos ) . '.php';
		} elseif ( strpos( $class_name, $trait_str ) === $trait_pos ) {
			$file_name = 'trait-' . substr( $class_name, 0, $trait_pos ) . '.php';
		} else {
			$file_name = 'class-' . $class_name . '.php';
		}

		// Generate final class file location.
		$ds       = self::DIR_SEPARATOR;
		$abs_path = $namespace_path;

		// In case class has sub-namespaces.
		if ( $parts ) {
			$abs_path .= $ds . join( $ds, $parts );
		}

		// Class file itself.
		$abs_path .= $ds . $file_name;

		// Try to load.
		if ( 0 === validate_file( $abs_path ) && file_exists( $abs_path ) ) {
			include_once $abs_path;

			return true;
		}

		// Could not find / include class.
		return false;
	}

	/**
	 * Sanitize file or directory name.
	 *
	 * @param string $file_or_dir file or directory name.
	 *
	 * @return string
	 */
	protected static function sanitize_file( $file_or_dir ) {
		$result = strtolower( $file_or_dir );
		$result = str_replace( '_', '-', $result );

		return $result;
	}
}
