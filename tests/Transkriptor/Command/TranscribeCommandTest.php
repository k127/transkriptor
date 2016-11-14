<?php
/**
 * Created by PhpStorm.
 * User: klaushartl
 * Date: 14.11.16
 * Time: 20:03
 */

namespace Tests\Transkriptor\Command;


use Cilex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Transkriptor\Command\TranscribeCommand;
use Transkriptor\InputOption\InputLanguageInputOption;
use Transkriptor\InputOption\OutputLanguageInputOption;
use Transkriptor\InputOption\PhraseInputOption;

/** @noinspection PhpUndefinedClassInspection */
class CreateUserCommandTest extends \PHPUnit_Framework_TestCase {

	public function testExecute() {

		$app = new Application( 'Test' );
		$app->command( new TranscribeCommand() );

		$commandTester = new CommandTester( new TranscribeCommand() );
		$commandTester->execute( array(
//			'command'                              => TranscribeCommand::NAME,
//			'--' . InputLanguageInputOption::NAME  => 'fr',
//			'--' . OutputLanguageInputOption::NAME => 'ipa',
//			'--' . PhraseInputOption::NAME         => 'physique',
		) );

		// the output of the command in the console
		$output = $commandTester->getDisplay();
		$this->assertContains( 'fisik', $output );
	}
}
