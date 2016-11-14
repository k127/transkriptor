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

	public function testExecute() {

		$application = new Application();

		$application->add( new TranscribeCommand() );

		$command       = $application->find( TranscribeCommand::NAME );
		$commandTester = new CommandTester( $command );
		$commandTester->execute( array(
			'command'                              => TranscribeCommand::NAME,
			'--' . InputLanguageInputOption::NAME  => 'fr',
			'--' . OutputLanguageInputOption::NAME => 'ipa',
			'--' . PhraseInputOption::NAME         => 'physique',
		) );

		// the output of the command in the console
		$output = $commandTester->getDisplay();
		$this->assertContains( 'fisik', $output );
	}
}
