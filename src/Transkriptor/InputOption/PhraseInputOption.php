<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 13.11.16
 * Time: 15:22
 */

namespace Transkriptor\InputOption;


use Symfony\Component\Console\Input\InputOption;

class PhraseInputOption extends InputOption {
	const NAME = 'phrase';
	const SHORT = 'p';
	const DESC = 'The phrase to transcribe';

	public function __construct( $mode = InputOption::VALUE_REQUIRED, $default = null ) {
		parent::__construct( self::NAME, self::SHORT, $mode, self::DESC, $default );
	}
}
