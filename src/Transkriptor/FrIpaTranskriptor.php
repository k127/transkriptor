<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 17.11.16
 * Time: 01:03
 */

namespace Transkriptor;

/**
 * Class FrIpaTranskriptor
 * @package Transkriptor
 */
class FrIpaTranskriptor extends ATranskriptor {

	/**
	 * @return array
	 */
	protected function getMap() {
		return [
			'-'   => ' ',
			'a'   => 'a',
			'ai'  => 'ɛ',
			'au'  => 'o',
			'eau' => 'o',
			'an'  => 'ã',
			'e'   => 'ə',
			'ee'  => 'e',
			'ɛ'   => 'ɛ',
			'en'  => 'ɛ̃',
			'eu'  => 'œ',
			'oe'  => 'ø',
			'i'   => 'i',
			'in'  => 'ɛ̃',
			'o'   => 'ɔ',
			'oi'  => 'wa',
			'ou'  => 'u',
			'on'  => 'õ',  // 'ɔ̃',
			'ui'  => 'ɥ',
			'oui' => 'wi',
			'u'   => 'y',
			'un'  => 'œ̃',
			'g'   => 'ʒ',
			'gg'  => 'ɡ',
			'gn'  => 'ɲ',
			'b'   => 'b',
			'c'   => 's',
			'ch'  => 'ʃ',
			'd'   => 'd',
			'f'   => 'f',
			'ʒ'   => 'ʒ',
			'j'   => 'j',
			'k'   => 'k',
			'l'   => 'l',
			'm'   => 'm',
			'n'   => 'n',
			'r'   => 'ʀ',
			'p'   => 'p',
			's'   => 's',
			't'   => 't',
			'v'   => 'v',
			'z'   => 'z',
		];
	}
}
