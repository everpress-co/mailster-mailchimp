<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit84ce8ae1ccc2b5cdbc5cd3185e4c47e2 {

	private static $loader;

	public static function loadClassLoader( $class ) {
		if ( 'Composer\Autoload\ClassLoader' === $class ) {
			require __DIR__ . '/ClassLoader.php';
		}
	}

	public static function getLoader() {
		if ( null !== self::$loader ) {
			return self::$loader;
		}

		spl_autoload_register( array( 'ComposerAutoloaderInit84ce8ae1ccc2b5cdbc5cd3185e4c47e2', 'loadClassLoader' ), true, true );
		self::$loader = $loader = new \Composer\Autoload\ClassLoader();
		spl_autoload_unregister( array( 'ComposerAutoloaderInit84ce8ae1ccc2b5cdbc5cd3185e4c47e2', 'loadClassLoader' ) );

		$useStaticLoader = PHP_VERSION_ID >= 50600 && ! defined( 'HHVM_VERSION' ) && ( ! function_exists( 'zend_loader_file_encoded' ) || ! zend_loader_file_encoded());
		if ( $useStaticLoader ) {
			require_once __DIR__ . '/autoload_static.php';

			call_user_func( \Composer\Autoload\ComposerStaticInit84ce8ae1ccc2b5cdbc5cd3185e4c47e2::getInitializer( $loader ) );
		} else {
			$map = require __DIR__ . '/autoload_namespaces.php';
			foreach ( $map as $namespace => $path ) {
				$loader->set( $namespace, $path );
			}

			$map = require __DIR__ . '/autoload_psr4.php';
			foreach ( $map as $namespace => $path ) {
				$loader->setPsr4( $namespace, $path );
			}

			$classMap = require __DIR__ . '/autoload_classmap.php';
			if ( $classMap ) {
				$loader->addClassMap( $classMap );
			}
		}

		$loader->register( true );

		return $loader;
	}
}
