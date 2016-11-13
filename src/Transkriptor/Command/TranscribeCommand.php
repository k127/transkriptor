<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 13.11.16
 * Time: 15:08
 */

namespace Transkriptor\Command;


use Cilex\Command\Command;
use Exception;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Transkriptor\InputOption\InputLanguageInputOption;
use Transkriptor\InputOption\OutputLanguageInputOption;
use Transkriptor\InputOption\PhraseInputOption;

class TranscribeCommand extends Command {
	const NAME = 'transcribe';
	const DESC = 'Transcribes a phrase of a given natural language into IPA';

	protected function configure() {
		$this->setName( self::NAME )->setDescription( self::DESC )->setDefinition(
			new InputDefinition(
				array(
					new InputLanguageInputOption( InputOption::VALUE_REQUIRED, 'fr' ),
					new OutputLanguageInputOption( InputOption::VALUE_REQUIRED, 'ipa' ),
					new PhraseInputOption(),
				)
			)
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {

		$inLang  = trim( strtolower( $input->getOption( InputLanguageInputOption::NAME ) ) );
		$outLang = trim( strtolower( $input->getOption( OutputLanguageInputOption::NAME ) ) );

		if ( ! ( $inPhrase = strtolower( $input->getOption( PhraseInputOption::NAME ) ) ) ) {
			throw new InvalidOptionException( sprintf( "<error>Missing input option '%s'</error>" ),
				PhraseInputOption::NAME );
		}

		$outPhrase = $this->transcribe( $inLang, $outLang, $inPhrase );
		$output->writeln( sprintf( '<comment>[%s] %s</comment>', $inLang, $inPhrase ) );
		$output->writeln( sprintf( '<info>[%s] %s</info>', $outLang, $outPhrase ) );

		$output->writeln( '<comment>Please note that phonemes in < > are not yet validated</comment>' );

		return 0;
	}

	protected function interact( InputInterface $input, OutputInterface $output ) {
		$helper = $this->getHelper( 'question' );

		if ( ! ( $phrase = $input->getOption( PhraseInputOption::NAME ) ) ) {
			$question = new Question( 'Please enter the phrase: ' );
			$input->setOption( PhraseInputOption::NAME, $helper->ask( $input, $output, $question ) );
		}
	}

	private function transcribe( $inLang, $outLang, $inPhrase ) {
		if ( ! in_array( $inLang, self::SUPPORTED_INPUT_LANGUAGES ) ) {
			throw new InvalidOptionException( sprintf( "<error>Input language '%s' is not (yet) supported</error>" ),
				$inLang );
		}
		if ( ! in_array( $outLang, self::SUPPORTED_OUTPUT_LANGUAGES ) ) {
			throw new InvalidOptionException( sprintf( "<error>Output language '%s' is not (yet) supported</error>" ),
				$outLang );
		}

		$tokens = $this->tokenize( $inLang, $inPhrase );

		$outPhrase = '';
		foreach ( $tokens as $token ) {
			if ( ( $token = trim( preg_replace( '/\(.*?\)/i', '', $token ) ) ) ) {
				$outPhrase .= self::IPA[ $inLang ][ $token ];
			}
		}

		// leading consonants // TODO move to tokenizer
		$outPhrase = preg_replace( '/(\W)h(\w)/i', '$1$2', $outPhrase );  // silent

		// trailing consonants // TODO move to tokenizer
		$outPhrase = preg_replace( '/(\w)s(\W)/i', '$1$2', $outPhrase );  // silent

		return trim( $outPhrase );
	}

	const SUPPORTED_INPUT_LANGUAGES = [ 'fr' ];
	const SUPPORTED_OUTPUT_LANGUAGES = [ 'ipa' ];
	const IPA = [
		'fr' => [
			'-'   => ' ',
			'ai'  => 'ɛ',
			'au'  => 'o',
			'eau' => 'o',
			'an'  => 'ã',
			'e'   => 'ə',
			'i'   => '(TODO i)',
			'o'   => '(TODO o)',
			'oi'  => 'w',
			'ou'  => 'u',
			'on'  => 'õ',  // 'ɔ̃',
			'ui'  => 'ɥ',
			'oui' => 'ɥ',
			'un'  => 'œ̃',
			'g'   => '(ʒ|g)',
			'gn'  => 'ɲ',
			'b'   => 'b',
			'c'   => 's',
			'd'   => 'd',
			'j'   => 'ʒ',
			'k'   => 'k',
			'l'   => 'l',
			'm'   => 'm',
			'r'   => 'ʀ',
			's'   => 's',
		],
	];

	/**
	 * @param string $inLang
	 * @param string $inPhrase
	 *
	 * @return array All phonemes found in $inPhrase for $inLang language
	 * @throws Exception
	 */
	private function tokenize( $inLang, $inPhrase ) {
		switch ( $inLang ) {
			case 'fr':
				return $this->tokenizeFR( $inPhrase );
			default:
				throw new Exception( sprintf( "<error>Language '%s' not yet implemented</error>", $inLang ) );
		}
	}

	/**
	 * @param $inPhrase
	 *
	 * @return array All phonemes found in $inPhrase for the French language
	 * @throws Exception
	 */
	private function tokenizeFR( $inPhrase ) {

		$useLiaison = false;
		$tokens     = [];
		$tokenId    = 0;
		$inPhraseA  = str_split( $inPhrase );
		for ( $i = 0; $i < sizeof( $inPhraseA ); $i ++ ) {
			$ch  = $inPhraseA[ $i ];
			$ch2 = array_key_exists( $i + 1, $inPhraseA ) ? $inPhraseA[ $i + 1 ] : '';
			$ch3 = array_key_exists( $i + 2, $inPhraseA ) ? $inPhraseA[ $i + 2 ] : '';
			$ch4 = array_key_exists( $i + 3, $inPhraseA ) ? $inPhraseA[ $i + 3 ] : '';
			if ( ! preg_match( '/[a-z]/i', $ch ) && ! array_key_exists( $tokenId, $tokens ) ) {
				// FIXME if ( ! ( ! array_key_exists( $tokenId - 1, $tokens ) || $tokens[ $tokenId - 1 ] !== '-' ) ) {
				$tokens[ $tokenId ] = '-';
				$tokenId ++;
				// FIXME }
				continue;
			} elseif ( array_key_exists( $tokenId, $tokens ) ) {
				throw new Exception( "<error>\$tokenId hadn't been increased</error>" );
			}
			switch ( $ch ) {
				case 'a':
					switch ( $ch2 ) {
						case 'i':
							$tokens[ $tokenId ] = 'ai';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'u':
							$tokens[ $tokenId ] = 'au';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'n':
							$tokens[ $tokenId ] = 'an';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
					}
					break;
				case 'e':
					switch ( $ch2 ) {
						case 'a':
							if ( $ch3 == 'u' ) {
								$tokens[ $tokenId ] = 'eau';
								$tokenId ++;
								$i += 2;
								break( 2 );
							}
							break;
						case 'u':
							$tokens[ $tokenId ] = 'eu';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'n':
							$tokens[ $tokenId ] = 'en';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'r':
							if ( ! preg_match( '/[a-z]/i', $ch3 ) ) {
								$tokens[ $tokenId ] = 'e(r)';
								$tokenId ++;
								$i ++;
								break( 2 );
							}
							break;
						case 't':
							if ( ! preg_match( '/[a-z]/i', $ch3 ) ) {
								$tokens[ $tokenId ] = 'e(t)';
								$tokenId ++;
								$i ++;
								break( 2 );
							}
							break;
						case 's':
							if ( $ch3 == 't' && ! preg_match( '/[a-z]/i', $ch4 ) ) {
								$tokens[ $tokenId ] = 'e(st)';
								$tokenId ++;
								$i += 2;
								break( 2 );
							}
							break;
					}
					if ( ! preg_match( '/[a-z]/i', $ch2 ) ) {
						if ( $useLiaison ) {
							$tokens[ $tokenId ] = 'e';
						} else {
							$tokens[ $tokenId ] = '(e)';
						}
						$tokenId ++;
						break;
					}
					break;
				case 'i':
					if ( $ch2 == 'n' ) {
						$tokens[ $tokenId ] = 'in';
						$tokenId ++;
						$i ++;
					} else {
						$tokens[ $tokenId ] = 'i';
						$tokenId ++;
					}
					break;
				case 'o':
					switch ( $ch2 ) {
						case 'i':
							$tokens[ $tokenId ] = 'oi';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'u':
							if ( $ch3 == 'i' ) {
								$tokens[ $tokenId ] = 'oui';
								$tokenId ++;
								$i += 2;
							} else {
								$tokens[ $tokenId ] = 'ou';
								$tokenId ++;
								$i ++;
							}
							break( 2 );
						case 'n':
							$tokens[ $tokenId ] = 'on';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 'o';
							$tokenId ++;
							break( 2 );
					}
					break;
				case 'u':
					switch ( $ch2 ) {
						case 'i':
							$tokens[ $tokenId ] = 'ui';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'n':
							$tokens[ $tokenId ] = 'un';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
					}
					break;
				case 'y':
					$tokens[ $tokenId ] = 'i';
					$tokenId ++;
					break;
				case 'b':
					$tokens[ $tokenId ] = 'b';
					$tokenId ++;
					break;
				case 'c':
					if ( $ch2 == '\'' ) {
						$ch2 = $ch3;
						$i ++;
					}
					switch ( $ch2 ) {
						case 'e':
						case 'a':
							$tokens[ $tokenId ] = 'c';
							$tokenId ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 'k';
							$tokenId ++;
							break( 2 );
					}
					break;
				case 'd':
					$tokens[ $tokenId ] = 'd';
					$tokenId ++;
					break;
				case 'f':
				case 'g':
					if ( $ch2 == 'n' ) {
						$tokens[ $tokenId ] = 'gn';
						$tokenId ++;
						$i ++;
					} else {
						$tokens[ $tokenId ] = 'g';
						$tokenId ++;
					}
					break;
				case 'h':
					$tokens[ $tokenId ] = '(h)';
					$tokenId ++;
					break;
				case 'j':
					$tokens[ $tokenId ] = 'j';
					$tokenId ++;
					break;
				case 'k':
				case 'l':
					$tokens[ $tokenId ] = 'l';
					$tokenId ++;
					break;
				case 'm':
					$tokens[ $tokenId ] = 'm';
					$tokenId ++;
					break;
				case 'n':
				case 'p':
				case 'q':
				case 'r':
					$tokens[ $tokenId ] = 'r';
					$tokenId ++;
					break;
				case 's':
					switch ( $ch2 ) {
						case 'e':
							$tokens[ $tokenId ] = 'z';
							$tokenId ++;
							break( 2 );
						case 'a':
							$tokens[ $tokenId ] = 's';
							$tokenId ++;
							break( 2 );
						case 's':
							$tokens[ $tokenId ] = 's(s)';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 's';
							$tokenId ++;
							break( 2 );
					}
					break;
				case 't':
					if ( ! preg_match( '/[a-z]/i', $ch2 ) ) {
						if ( $useLiaison ) {
							$tokens[ $tokenId ] = 't';
						} else {
							$tokens[ $tokenId ] = '(t)';
						}
						$tokenId ++;
					}
					break;
				case 'v':
				case 'w':
				case 'x':
				case 'z':
				default:
			}
		}

		/*
		foreach ( $tokens as &$token ) {
			$token = trim( $token );
		}
		*/

		return $tokens;
	}
}
