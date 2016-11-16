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
		//   (random)
		//

		$this->assertContains( '[bjɛ̃]', $this->_testPhrase( 'bien' ) );
		$this->assertContains( '[sjɛl]', $this->_testPhrase( 'ciel' ) );
		$this->assertContains( '[ʀjɛ̃]', $this->_testPhrase( 'rien' ) );
//		$this->assertEquals( '[de] [paʀi]', $this->_testPhrase( 'de Paris' ) );  // TODO
		$this->assertEquals( '[ku]', $this->_testPhrase( 'coup' ) );


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

		// é
//		$this->assertEquals( '[ɑ̃ʃɑ̃te]', $this->_testPhrase( 'enchanté' ) );    // TODO fix encoding problem
//		$this->assertEquals( '[eɡalmɑ̃]', $this->_testPhrase( 'également' ) );  // TODO fix encoding problem
//		$this->assertEquals( '[dezɔle]', $this->_testPhrase( 'désolé' ) );     // TODO fix encoding problem

		// è
//		$this->assertEquals( '[fʀɛʀ]', $this->_testPhrase( 'frère' ) );        // TODO fix encoding problem
//		$this->assertEquals( '[apʀɛ]', $this->_testPhrase( 'après' ) );        // TODO fix encoding problem
//		$this->assertEquals( '[tʀɛ]', $this->_testPhrase( 'très' ) );          // TODO fix encoding problem
//		$this->assertEquals( '[pʀɔblɛm]', $this->_testPhrase( 'problème' ) );  // TODO fix encoding problem

		// ê
//		$this->assertEquals( '[ɛtʀ]', $this->_testPhrase( 'être' ) );          // TODO fix encoding problem
//		$this->assertEquals( '[f(ə)nɛtʀ]', $this->_testPhrase( 'fenêtre' ) );  // TODO fix encoding problem
//		$this->assertEquals( '[mɛm]', $this->_testPhrase( 'même' ) );          // TODO fix encoding problem
//		$this->assertEquals( '[fɛt]', $this->_testPhrase( 'fête' ) );          // TODO fix encoding problem


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   vowel combinations
		//

		// ai
		$this->assertEquals( '[mɛzõ]', $this->_testPhrase( 'maison' ) );
		$this->assertEquals( '[movɛ]', $this->_testPhrase( 'mauvais' ) );
		$this->assertEquals( '[ʒɛ]', $this->_testPhrase( 'j\'ai' ) );

		// au/eau
		$this->assertEquals( '[ʒuʀno]', $this->_testPhrase( 'journaux' ) );
		$this->assertEquals( '[bo]', $this->_testPhrase( 'beau' ) );

		// eu/œu
//		$this->assertEquals( '[sœʀ]', $this->_testPhrase( 'sœur' ) );    // TODO fix encoding problem
		$this->assertEquals( '[flœʀ]', $this->_testPhrase( 'fleur' ) );
//		$this->assertEquals( '[œf]', $this->_testPhrase( 'œuf' ) );      // TODO fix encoding problem
		$this->assertEquals( '[blø]', $this->_testPhrase( 'bleu' ) );

		// oi
		$this->assertEquals( '[vwasi]', $this->_testPhrase( 'voici' ) );
		$this->assertEquals( '[twa]', $this->_testPhrase( 'toi' ) );
		$this->assertEquals( '[bwaʀ]', $this->_testPhrase( 'boire' ) );

		// ou
		$this->assertEquals( '[boku]', $this->_testPhrase( 'beaucoup' ) );
		$this->assertEquals( '[bõʒuʀ]', $this->_testPhrase( 'bonjour' ) );
		$this->assertEquals( '[nu]', $this->_testPhrase( 'nous' ) );

		// ui/oui
		$this->assertEquals( '[wi]', $this->_testPhrase( 'oui' ) );
		$this->assertEquals( '[wistiti]', $this->_testPhrase( 'ouistiti' ) );
//		$this->assertEquals( '[kɥizin]', $this->_testPhrase( 'cuisine' ) );     // TODO
//		$this->assertEquals( '[ɥi]', $this->_testPhrase( 'huit' ) );           // TODO
//		$this->assertEquals( '[ʒe] [sɥi]', $this->_testPhrase( 'je suis' ) );  // TODO


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//
		//   nasal vowels
		//

		// ...

//		$this->assertEquals( '', $this->_testPhrase( '' ) );
//		$this->assertEquals( '', $this->_testPhrase( '' ) );
//		$this->assertEquals( '', $this->_testPhrase( '' ) );
//		$this->assertEquals( '', $this->_testPhrase( '' ) );

		$this->assertEquals( '[kɛlkœ̃]', $this->_testPhrase( 'quelqu\'un' ) );
	}
}
