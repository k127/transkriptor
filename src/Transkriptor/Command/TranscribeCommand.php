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
use Tokenizers\FrPhonemTokenizer;
use Transkriptor\FrIpaTranskriptor;
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

		switch ( $inLang ) {
			case 'fr':
				switch ( $outLang ) {
					case 'ipa':
						$transkriptor = new FrIpaTranskriptor();
						break;
					default:
						throw new InvalidOptionException(
							sprintf( "<error>Output language '%s' is not (yet) supported</error>" ), $outLang );
				}
				break;
			default:
				throw new InvalidOptionException(
					sprintf( "<error>Input language '%s' is not (yet) supported</error>" ), $inLang );
		}

		$tokens = $this->tokenize( $inLang, $inPhrase );

		$outPhrase = '';
		foreach ( $tokens as $token ) {
			if ( ( $token = trim( preg_replace( '/\(.*?\)/i', '', $token ) ) ) ) {
				$outPhrase .= $transkriptor->transcribe( $token );
			}
		}

		return trim( $outPhrase );
	}

	/**
	 * @param string $inLang
	 * @param string $inWord
	 *
	 * @return array All phonemes found in $inWord for $inLang language
	 * @throws Exception
	 */
	private function tokenize( $inLang, $inWord ) {
		switch ( $inLang ) {
			case 'fr':
				$tokenizer = new FrPhonemTokenizer();
				break;
			default:
				throw new Exception( sprintf( "<error>Language '%s' not yet implemented</error>", $inLang ) );
		}

		return $tokenizer->tokenize( $inWord );
	}
}
