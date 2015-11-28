<?php

namespace TYPO3\Jobqueue\Tests\Unit\Command;

use TYPO3\Jobqueue\Command\JobCommandController;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\Message;

class JobCommandTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    protected $jobManger;
    protected $commandController;

    public function setUp()
    {
        parent::setUp();
        $this->commandController = $this->getMock(JobCommandController::class, [], [], '', false);

        $this->jobManger = $this->getMock(JobManager::class, ['waitAndExecute'], [], '', false);

        $this->inject($this->commandController, 'jobManger', $this->jobManger);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->commandController);
    }

    /**
     * @test
     */
    public function work()
    {
        $queueName = 'TestQueue';
        $this->jobManger
            ->expects($this->exactly(3))
            ->method('waitAndExecute')
            ->with($queueName)
            ->will($this->onConsecutiveCalls(new Message(), new Message(), null));

        $this->commandController->workCommand($queueName);
    }
}
