<?php

/**
 * Class MGO_DirFilter
 */
class MGO_ImageFilter extends RecursiveFilterIterator {

	protected $exclude = array();
	protected $formats = array( '.jpeg', '.jpg', '.png', '.gif' );

	/**
	 * MGO_DirFilter constructor.
	 *
	 * @param $iterator
	 * @param array $exclude
	 */
	public function __construct( $iterator, $exclude ) {
		parent::__construct( $iterator );

		$exclude = (array) $exclude;

		foreach ( $exclude as $path ) {
			$sanitized = $this->sanitize_path( $path );
			if ( ! empty( $sanitized ) ) {
				array_push( $this->exclude, $sanitized );
			}
		}
	}

	/**
	 * Accept?
	 * @return bool
	 */
	public function accept() {

		$path = $this->getPathname();

		if ( $this->isDir() ) {

			$name = $this->getFilename();
			if ( in_array( $name, array( '.', '..', 'cgi-bin', '.DS_Store' ) ) ) {
				return false;
			}

			$sanitized = $this->sanitize_path( $path );
			if ( in_array( $sanitized, $this->exclude ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Children
	 *
	 * @return MGO_ImageFilter|RecursiveFilterIterator
	 */
	public function getChildren() {
		return new MGO_ImageFilter( $this->getInnerIterator()->getChildren(), $this->exclude );
	}

	/**
	 * Sanitize path
	 *
	 * @param $path
	 *
	 * @return string
	 */
	private function sanitize_path( $path ) {
		return rtrim( $path, '/' );
	}
}