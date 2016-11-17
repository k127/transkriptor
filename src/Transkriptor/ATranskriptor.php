<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 17.11.16
 * Time: 01:04
 */

namespace Transkriptor;

/**
 * Class ATranskriptor
 * @package Transkriptor
 */
abstract class ATranskriptor {

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	public function transcribe( $str ) {
		$map = $this->getMap();

		return $map[ $str ];
	}

	/** @return array */
	abstract protected function getMap();
}
