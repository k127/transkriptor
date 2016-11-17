<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 17.11.16
 * Time: 00:53
 */

namespace Tokenizers;


use NlpTools\Tokenizers\TokenizerInterface;

/**
 * Class PhonemTokenizer
 * @package Tokenizers
 */
abstract class PhonemTokenizer implements TokenizerInterface {

	/** @var string */
	protected $language;

	/**
	 * PhonemTokenizer constructor.
	 *
	 * @param string $language2CC
	 */
	public function __construct( $language2CC ) {
		$this->language = $language2CC;
	}
}
