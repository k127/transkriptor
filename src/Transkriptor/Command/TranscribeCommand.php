<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 13.11.16
 * Time: 15:08
 */

namespace Transkriptor\Command;


use Exception;
use Symfony\Component\Console\Command\Command;
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

		if ( $outLang == 'ipa' ) {
			$outPhrase = preg_replace( '/\s+/', ' ', $outPhrase );
			$outPhrase = '[' . implode( '] [', explode( ' ', $outPhrase ) ) . ']';
		}

		if ( $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE ) {
			$output->writeln( sprintf( '<comment>[%s] %s</comment>', $inLang, $inPhrase ) );
			$output->writeln( sprintf( '<info>[%s] %s</info>', $outLang, $outPhrase ) );
		} else {
			$output->write( sprintf( '<info>%s</info>', $outPhrase ) );
		}

		if ( strpos( $outPhrase, '<' ) !== false && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE ) {
			$output->writeln( '<comment>Please note that phonemes in < > are not yet validated</comment>' );
		}

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
			'i'   => 'i',
			'in'  => 'ɛ̃',
			'o'   => 'ɔ',
			'oi'  => 'w',
			'ou'  => 'u',
			'on'  => 'õ',  // 'ɔ̃',
			'ui'  => 'ɥ',
			'oui' => 'ɥ',
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
			's'   => 's',
			't'   => 't',
			'v'   => 'v',
			'z'   => 'z',
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

		$useLiaison        = false;
		$tokens            = [];
		$tokenId           = 0;
		$wordCharPatternFR = '/[a-zàâçéèêëîïôûùüÿñæœ]/i';
		$inPhrase          = preg_replace( '/(\w)\'(\w)/', '$1$2', $inPhrase );  // remove '
		// TODO remove punctuation
		$inPhraseA = str_split( $inPhrase );
		for ( $i = 0; $i < sizeof( $inPhraseA ); $i ++ ) {
			$ch  = $inPhraseA[ $i ];
			$ch2 = array_key_exists( $i + 1, $inPhraseA ) ? $inPhraseA[ $i + 1 ] : '';
			$ch3 = array_key_exists( $i + 2, $inPhraseA ) ? $inPhraseA[ $i + 2 ] : '';
			$ch4 = array_key_exists( $i + 3, $inPhraseA ) ? $inPhraseA[ $i + 3 ] : '';
			if ( ! preg_match( $wordCharPatternFR, $ch ) && ! array_key_exists( $tokenId, $tokens ) ) {
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
							$tokens[ $tokenId ] = 'a';
							$tokenId ++;
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
							switch ( $ch3 ) {
								case 'o':
									$tokens[ $tokenId ] = 'e';
									$tokenId ++;
									break( 3 );
								case 'n':
									$tokens[ $tokenId ] = 'ɛ';
									$tokenId ++;
									break( 3 );
								default:
									$tokens[ $tokenId ] = 'en';
									$tokenId ++;
									$i ++;
									break( 3 );
							}
						case 'r':
							if ( ! preg_match( $wordCharPatternFR, $ch3 ) ) {
								$tokens[ $tokenId ] = 'ee(r)';
								$tokenId ++;
								$i ++;
							} else {
								$tokens[ $tokenId ] = 'e';
								$tokenId ++;
							}
							break( 2 );
						case 't':
							if ( ! preg_match( $wordCharPatternFR, $ch3 ) ) {
								$tokens[ $tokenId ] = 'ee(t)';
								$tokenId ++;
								$i ++;
								break( 2 );
							}
							break;
						case 's':
							if ( $ch3 == 't' && ! preg_match( $wordCharPatternFR, $ch4 ) ) {
								$tokens[ $tokenId ] = 'e(st)';
								$tokenId ++;
								$i += 2;
								break( 2 );
							}
							break;
					}
					if ( ! preg_match( $wordCharPatternFR, $ch2 ) ) {
						$tokens[ $tokenId ] = $useLiaison ? 'e' : '(e)';
						$tokenId ++;
					} else {
						$tokens[ $tokenId ] = 'e';
						$tokenId ++;
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
						/** @noinspection PhpMissingBreakStatementInspection */
						case 'n':
							if ( $ch3 != 'n' && $ch4 != 'e' ) {
								$tokens[ $tokenId ] = 'on';
								$tokenId ++;
								$i ++;
								break( 2 );
							}
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
							$tokens[ $tokenId ] = 'u';
							$tokenId ++;
							break( 2 );
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
						case 'h':
							$tokens[ $tokenId ] = 'ch';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 'k';
							$tokenId ++;
							break( 2 );
					}
					break;
				case 'd':
					if ( ! preg_match( $wordCharPatternFR, $ch2 ) ) {
						$tokens[ $tokenId ] = $useLiaison ? 'd' : '(d)';
						$tokenId ++;
					} else {
						$tokens[ $tokenId ] = 'd';
						$tokenId ++;
					}
					break;
				case 'f':
					break;
				case 'g':
					switch ( $ch2 ) {
						case 'e':
							$tokens[ $tokenId ] = 'g';
							$tokenId ++;
//							TODO verify
//							$i ++;
							break( 2 );
						case 'g':
							$tokens[ $tokenId ] = 'gg';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'n':
							$tokens[ $tokenId ] = 'gn';
							$tokenId ++;
							$i ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 'gg';
							$tokenId ++;
							break( 2 );
					}
					break;
				case 'h':
					$tokens[ $tokenId ] = '(h)';
					$tokenId ++;
					break;
				case 'j':
					$tokens[ $tokenId ] = 'ʒ';
					$tokenId ++;
					break;
				case 'k':
					break;
				case 'l':
					$tokens[ $tokenId ] = 'l';
					$tokenId ++;
					break;
				case 'm':
					if ( $ch2 == 'm' && $ch3 == 'e' ) {
						$tokens[ $tokenId ] = 'm(me)';
						$tokenId ++;
						$i += 2;
					} else {
						$tokens[ $tokenId ] = 'm';
						$tokenId ++;
					}
					break;
				case 'n':
					if ( $ch2 == 'n' && $ch3 == 'e' ) {
						$tokens[ $tokenId ] = 'n(ne)';
						$tokenId ++;
						$i += 2;
					} else {
						$tokens[ $tokenId ] = 'n';
						$tokenId ++;
					}
					break;
				case 'p':
					if ( $ch2 == 'h' ) {
						$tokens[ $tokenId ] = 'f';
						$tokenId ++;
						$i ++;
					}
					break;
				case 'q':
					if ( $ch2 == 'u' ) {
						$tokens[ $tokenId ] = 'k';
						$tokenId ++;
						$i ++;
					}
					break;
				case 'r':
					$tokens[ $tokenId ] = 'r';
					$tokenId ++;
					break;
				case 's':
					switch ( $ch2 ) {
//						case 'e':
//							 TODO verify
//							$tokens[ $tokenId ] = 'z';
//							$tokenId ++;
//							break( 2 );
						case 'a':
							$tokens[ $tokenId ] = 's';
							$tokenId ++;
							break( 2 );
						case 's':
							$tokens[ $tokenId ] = 's(s)';
							$tokenId ++;
							$i ++;
							break( 2 );
					}
					if ( ! preg_match( $wordCharPatternFR, $ch2 ) ) {
						$tokens[ $tokenId ] = $useLiaison ? 's' : '(s)';
						$tokenId ++;
					} else {
						$tokens[ $tokenId ] = 's';
						$tokenId ++;
					}
					break;
				case 't':
					switch ( $ch2 ) {
						case 'i':
							if ( $ch3 == 'o' ) {
								$tokens[ $tokenId ] = 's';
								$tokenId ++;
								$tokens[ $tokenId ] = 'j';
								$tokenId ++;
								$i ++;
							} else {
								$tokens[ $tokenId ] = 't';
								$tokenId ++;
							}
							break( 2 );
						case 't':
							$tokens[ $tokenId ] = 't(t)';
							$tokenId ++;
							$i ++;
							break( 2 );
					}
					if ( ! preg_match( $wordCharPatternFR, $ch2 ) ) {
						$tokens[ $tokenId ] = $useLiaison ? 't' : '(t)';
						$tokenId ++;
					} else {
						$tokens[ $tokenId ] = 't';
						$tokenId ++;
					}
					break;
				case 'v':
					$tokens[ $tokenId ] = 'v';
					$tokenId ++;
					break;
				case 'w':
					break;
				case 'x':
					break;
				case 'z':
					break;
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
