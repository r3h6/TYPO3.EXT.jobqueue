<?php

namespace TYPO3\Jobqueue\Tests\Unit\Command;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use TYPO3\Jobqueue\Command\JobCommandController;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestJob;

/**
 * Unit test for the JobCommandController.
 */
class JobCommandControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    protected $jobManager;
    protected $subject;

    public function setUp()
    {
        parent::setUp();
        // $this->subject = $this->getMock(JobCommandController::class, [], [], '', false);
        $this->subject = new JobCommandController();

        $this->jobManager = $this->getMock(JobManager::class, ['waitAndExecute', '__destruct'], [], '', false);

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
