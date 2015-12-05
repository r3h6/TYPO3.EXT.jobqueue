<?php

namespace TYPO3\Jobqueue\Tests\Unit\Command;

use TYPO3\Jobqueue\Command\JobCommandController;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestJob;

class JobCommandControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    protected $jobManager;
    protected $subject;

    public function setUp()
    {
        parent::setUp();
        // $this->subject = $this->getMock(JobCommandController::class, [], [], '', false);
        $this->subject = new JobCommandController();

        $this->jobManager = $this->getMock(JobManager::class, ['waitAndExecute'], [], '', false);

        $this->inject($this->subject, 'jobManager', $this->jobManager);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->subject, $this->jobManager);
    }

    /**
     * @test
     */
    public function work()
    {
        $queueName = 'TestQueue';
        $this->jobManager
            ->expects($this->exactly(3))
            ->method('waitAndExecute')
            ->with($queueName)
            ->will($this->onConsecutiveCalls(
                new TestJob(),
                new TestJob(),
                null
            ));

        $this->subject->workCommand($queueName);
    }
}
