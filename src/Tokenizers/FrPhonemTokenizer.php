<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 17.11.16
 * Time: 00:54
 */

namespace Tokenizers;


use Exception;

class FrPhonemTokenizer extends PhonemTokenizer {

	/**
	 * FrPhonemTokenizer constructor.
	 */
	public function __construct() {
		parent::__construct( 'fr' );
	}

	/**
	 * Break a character sequence to a token sequence
	 *
	 * @param  string $str The text for tokenization
	 *
	 * @return array All phonemes found in $inWord for the French language
	 * @throws Exception
	 */
	public function tokenize( $str ) {
		$useLiaison        = false;
		$tokens            = [];
		$tokenId           = 0;
		$wordCharPatternFR = '/[a-zàâçéèêëîïôûùüÿñæœ]/i';
		$str               = preg_replace( '/(\w)\'(\w)/', '$1$2', $str );  // remove '
		// TODO remove punctuation
		$stringArray = str_split( $str );
		for ( $i = 0; $i < sizeof( $stringArray ); $i ++ ) {
			$ch  = $stringArray[ $i ];
			$ch2 = array_key_exists( $i + 1, $stringArray ) ? $stringArray[ $i + 1 ] : '';
			$ch3 = array_key_exists( $i + 2, $stringArray ) ? $stringArray[ $i + 2 ] : '';
			$ch4 = array_key_exists( $i + 3, $stringArray ) ? $stringArray[ $i + 3 ] : '';
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
							if ( $ch3 == 's' && preg_match( $wordCharPatternFR, $ch4 ) ) {
								$tokens[ $tokenId ] = 'z';
								$tokenId ++;
								$i ++;
							}
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
							if ( ! preg_match( $wordCharPatternFR, $ch3 ) ) {
								$tokens[ $tokenId ] = 'oe';
								$tokenId ++;
								$i ++;
							} else {
								$tokens[ $tokenId ] = 'eu';
								$tokenId ++;
								$i ++;
							}
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
						case 'l':
							if ( $ch3 != 'l' ) {
								$tokens[ $tokenId ] = 'ɛ';
								$tokenId ++;
								break( 2 );
							}
							break;
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
					switch ( $ch2 ) {
						case 'n':
							$tokens[ $tokenId ] = 'in';
							$tokenId ++;
							$i ++;
							break( 2 );
						case 'e':
							$tokens[ $tokenId ] = 'j';
							$tokenId ++;
							break( 2 );
						default:
							$tokens[ $tokenId ] = 'i';
							$tokenId ++;
							break( 2 );
					}
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
								if ( $ch3 == 'p' && ! preg_match( $wordCharPatternFR, $ch4 ) ) {
									$tokens[ $tokenId ] = 'ou(p)';
									$tokenId ++;
									$i += 2;
								} else {
									$tokens[ $tokenId ] = 'ou';
									$tokenId ++;
									$i ++;
								}
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
						case 'a':
						case 'e':
						case 'i':
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
					$tokens[ $tokenId ] = 'f';
					$tokenId ++;
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
					} else {
						$tokens[ $tokenId ] = 'p';
						$tokenId ++;
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
					if ( ! preg_match( $wordCharPatternFR, $ch2 ) ) {
						$tokens[ $tokenId ] = '(x)';
						$tokenId ++;
					}
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
