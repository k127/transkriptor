<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 14.11.16
 * Time: 20:03
 */

namespace Tests\Transkriptor\Command;


use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Transkriptor\Command\TranscribeCommand;
use Transkriptor\InputOption\InputLanguageInputOption;
use Transkriptor\InputOption\OutputLanguageInputOption;
use Transkriptor\InputOption\PhraseInputOption;

/** @noinspection PhpUndefinedClassInspection */
class TranscribeCommandTest extends PHPUnit_Framework_TestCase {

	public function _testPhrase( $phrase ) {
		$application = new Application();

		$application->add( new TranscribeCommand() );

		$command       = $application->find( TranscribeCommand::NAME );
		$commandTester = new CommandTester( $command );
		$commandTester->execute( array(
			'command'                              => TranscribeCommand::NAME,
			'--' . InputLanguageInputOption::NAME  => 'fr',
			'--' . OutputLanguageInputOption::NAME => 'ipa',
			'--' . PhraseInputOption::NAME         => $phrase,
		) );

		// the output of the command in the console
		$output = $commandTester->getDisplay();

		return $output;
	}

	public function testFR2IPA() {

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   unstressed vowels
		//

		// a
		$this->assertContains( '[ipa] tabl', $this->_testPhrase( 'table' ) );
		$this->assertContains( '[ipa] sak', $this->_testPhrase( 'sac' ) );
		$this->assertContains( '[ipa] ʃa', $this->_testPhrase( 'chat' ) );
		$this->assertContains( '[ipa] bæɡɪdʒ', $this->_testPhrase( 'baggage' ) );
		$this->assertContains( '[ipa] matɛ̃', $this->_testPhrase( 'matin' ) );

		// e
		$this->assertRegExp( '/\[ipa\] ʒə?nu$/', $this->_testPhrase( 'genou' ) );
		$this->assertRegExp( '/\[ipa\] sə?ɡɔ̃$/', $this->_testPhrase( 'second' ) );
		$this->assertRegExp( '/\[ipa\] ʃə?val$/', $this->_testPhrase( 'cheval' ) );

		// -er/-et
		// ...
		
		// i/y
		// ...
		$this->assertContains( '[ipa] fisik', $this->_testPhrase( 'physique' ) );
		// ...
		
		// o
		// ...

		// u
		// ... 


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   stressed vowels
		//

//		$this->assertContains( '[ipa] kɛlkœ̃', $this->_testPhrase( 'quelqu\'un' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
	}
}
