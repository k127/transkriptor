<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 13.11.16
 * Time: 15:22
 */

namespace Transkriptor\InputOption;


use Symfony\Component\Console\Input\InputOption;

class InputLanguageInputOption extends InputOption {

	const NAME = 'in_lang';
	const SHORT = 'i';
	const DESC = 'A ISO 639-1 (two letter) language code';

	public function __construct( $mode = InputOption::VALUE_REQUIRED, $default = null ) {
		parent::__construct( self::NAME, self::SHORT, $mode, self::DESC, $default );
	}
}
