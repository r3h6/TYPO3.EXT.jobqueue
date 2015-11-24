<?php
namespace TYPO3\Jobqueue\Tests\Unit\Command;


use TYPO3\Jobqueue\Command\JobCommandController;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class JobCommandTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	protected $jobManger;
	protected $commandController;

	public function setUp (){
		parent::setUp();
		$this->commandController = $this->getMock(JobCommandController::class, [], [], '', FALSE);

		$this->jobManger = $this->getMock(JobManager::class, ['waitAndExecute'], [], '', FALSE);

		$this->inject($this->commandController, 'jobManger', $this->jobManger);
	}

	public function tearDown (){
		parent::tearDown();
		unset($this->commandController);
	}

	/**
	 * @test
	 */
	public function work (){
		$queueName = 'TestQueue';
		$this->jobManger
			->expects($this->exactly(3))
			->method('waitAndExecute')
			->with($queueName)
			->will($this->onConsecutiveCalls(new Message(), new Message(), NULL));

		$this->commandController->workCommand($queueName);
	}
}