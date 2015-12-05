<?php

namespace TYPO3\Jobqueue\Tests\Unit\Job;

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

use TYPO3\Jobqueue\Configuration\ExtConf;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Exception as JobQueueException;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestJob;
use TYPO3\Jobqueue\Queue\RuntimeQueue;

/**
 * Unit tests for the JobManager.
 */
class JobManagerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * @var JobManager
     */
    protected $jobManager;

    protected $testQueue;

    protected $extConf;

    protected $queueName = 'RuntimeQueue';
    /**
     *
     */
    public function setUp()
    {
        $this->testQueue = new RuntimeQueue($this->queueName, null);

        $this->queueManager = $this->getMock(QueueManager::class, array('getQueue'), array(), '',  false);
        $this->queueManager
            ->expects($this->any())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->testQueue));

        // $this->jobManager = $this->getMock(JobManager::class);
        $this->jobManager = new JobManager();
        $this->inject($this->jobManager, 'queueManager', $this->queueManager);

        $this->extConf = $this->getMock(ExtConf::class, array('getMaxAttemps'), array(), '', false);
        $this->inject($this->jobManager, 'extConf', $this->extConf);
    }
    public function tearDown()
    {
        unset(
            $this->queueManager,
            $this->jobManager,
            $this->testQueue,
            $this->extConf
        );
    }

    /**
     * @test
     */
    public function queuePublishesMessageToQueue()
    {
        $job = new TestJob();
        $this->jobManager->queue($this->queueName, $job);

        $messages = $this->jobManager->peek($this->queueName);
        $this->assertInternalType('array', $messages, 'Peek does not return messages array!');
        $this->assertCount(1, $messages, 'Messages does not contain published job.');
        $this->assertContainsOnlyInstancesOf(TestJob::class, $messages, 'Messages array can only contain TestJob instances.');
    }

    /**
     * @test
     */
    public function delayCallsQueue()
    {
        $job = new TestJob();
        $this->jobManager->delay($this->queueName, 5, $job);
    }

    /**
     * @test
     * @depends queuePublishesMessageToQueue
     */
    public function waitAndExecuteGetsAndExecutesJobFromQueue()
    {
        $job = new TestJob();
        $this->jobManager->queue($this->queueName, $job);

        $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
        $this->assertInstanceOf(TestJob::class, $queuedJob);
        $this->assertTrue($queuedJob->getProcessed());
    }

    /**
     * @test
     * @expectedException TYPO3\Jobqueue\Exception
     * @depends waitAndExecuteGetsAndExecutesJobFromQueue
     */
    public function waitAndExecuteJobThrowsException()
    {
        $job = $this->getMock(TestJob::class, array('execute'), array(), '', false);
        $job
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false));
        $this->jobManager->queue($this->queueName, $job);
        $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
    }

    /**
     * @test
     * @depends waitAndExecuteJobThrowsException
     */
    public function waitAndExecuteJobAttempsThreeTimes()
    {
        $attemps = 3;

        $this->extConf
            ->expects($this->any())
            ->method('getMaxAttemps')
            ->will($this->returnValue($attemps));

        $job = $this->getMock(TestJob::class, array('execute'), array(), '', false);
        $job
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false));

        $this->jobManager->initializeObject();
        $this->jobManager->queue($this->queueName, $job);
        for ($i = 0; $i < $attemps + 99; ++$i) {
            try {
                $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
                if ($queuedJob === null) {
                    break;
                }
            } catch (JobQueueException $exception) {
                if ($i + 1 < $attemps) {
                    $this->assertEquals(1, $this->testQueue->count(), 'Job is not republished to queue!');
                }
            }
        }
        $this->assertEquals($attemps, $i, 'To many attemps!');
        $this->assertEquals(0, $this->testQueue->count(), 'Queue not empty!');
    }
}
