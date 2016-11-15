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
		$this->assertEquals( '[tabl]', $this->_testPhrase( 'table' ) );
		$this->assertEquals( '[sak]', $this->_testPhrase( 'sac' ) );
		$this->assertEquals( '[ʃa]', $this->_testPhrase( 'chat' ) );
//		$this->assertEquals( '[bæɡɪdʒ]', $this->_testPhrase( 'baggage' ) );  // TODO
		$this->assertEquals( '[matɛ̃]', $this->_testPhrase( 'matin' ) );

		// e
		$this->assertEquals( '[ʒənu]', $this->_testPhrase( 'genou' ) );
		$this->assertEquals( '[səkõ]', $this->_testPhrase( 'second' ) );
		$this->assertEquals( '[ʃəval]', $this->_testPhrase( 'cheval' ) );

		// -er/-et
		$this->assertEquals( '[mãʒe]', $this->_testPhrase( 'manger' ) );
		$this->assertEquals( '[e]', $this->_testPhrase( 'et' ) );

		// i/y
		$this->assertEquals( '[li]', $this->_testPhrase( 'lit' ) );
//		$this->assertEquals( '[minyt]', $this->_testPhrase( 'minute' ) );  // TODO
		$this->assertEquals( '[kuʀiʀ]', $this->_testPhrase( 'courir' ) );
//		$this->assertEquals( '[sistɛm]', $this->_testPhrase( 'système' ) );  // TODO fix encoding problem
		$this->assertEquals( '[fisik]', $this->_testPhrase( 'physique' ) );

		// o
		$this->assertEquals( '[bɔt]', $this->_testPhrase( 'botte' ) );
		$this->assertEquals( '[ɔm]', $this->_testPhrase( 'homme' ) );
//		$this->assertEquals( '[velo]', $this->_testPhrase( 'vélo' ) );  // TODO fix encoding problem
		$this->assertEquals( '[ɛ̃diɡɔ]', $this->_testPhrase( 'indigo' ) );

		// u
		$this->assertEquals( '[ʒy]', $this->_testPhrase( 'jus' ) );
		$this->assertEquals( '[tisy]', $this->_testPhrase( 'tissu' ) );
		$this->assertEquals( '[ytil]', $this->_testPhrase( 'utile' ) );


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   stressed vowels
		//

		// ...


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   vowel combinations
		//

		// ai
		$this->assertContains( '[mɛzõ]', $this->_testPhrase( 'maison' ) );
		$this->assertContains( '[movɛ]', $this->_testPhrase( 'mauvais' ) );
		$this->assertContains( '[ʒɛ]', $this->_testPhrase( 'j\'ai' ) );

		// au/eau
		$this->assertContains( '[ʒuʀno]', $this->_testPhrase( 'journaux' ) );
		$this->assertContains( '[bo]', $this->_testPhrase( 'beau' ) );

		// eu/œu
		// ...

		// oi
		// ...

		// ou
		// ...

		// ui/oui
		// ...


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   nasal vowels
		//

		// ...

//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );
//		$this->assertContains( '', $this->_testPhrase( '' ) );

		$this->assertContains( 'kɛlkœ̃', $this->_testPhrase( 'quelqu\'un' ) );
	}
}
