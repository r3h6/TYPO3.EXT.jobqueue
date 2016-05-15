<?php

namespace R3H6\Jobqueue\Tests\Unit\Job;

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

use R3H6\Jobqueue\Configuration\ExtensionConfiguration;
use R3H6\Jobqueue\Job\JobManager;
use R3H6\Jobqueue\Job\Worker;
use R3H6\Jobqueue\Domain\Repository\FailedJobRepository;
use R3H6\Jobqueue\Queue\QueueManager;
use R3H6\Jobqueue\Exception as JobQueueException;
use R3H6\Jobqueue\Tests\Unit\Fixtures\TestJob;
use R3H6\Jobqueue\Queue\MemoryQueue;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Unit tests for the JobManager.
 */
class JobManagerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var QueueManager
     */
    protected $queueManager = null;

    /**
     * @var JobManager
     */
    protected $jobManager = null;

    /**
     * @var MemoryQueue
     */
    protected $testQueue = null;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration = null;

    /**
     * @var FailedJobRepository
     */
    protected $failedJobRepository = null;

    protected $queueName = 'MemoryQueue';

    /**
     *
     */
    public function setUp()
    {
        // $this->jobManager = new JobManager();
        $this->jobManager = $this->getMock(JobManager::class, array('__destruct'), array(), '', false);

        $this->testQueue = new MemoryQueue($this->queueName, []);

        $this->queueManager = $this->getMock(QueueManager::class, array('getQueue', 'getQueues'), array(), '', false);

        $this->queueManager
            ->expects($this->any())
            ->method('getQueue')
            ->with($this->queueName)
            ->will($this->returnValue($this->testQueue));

        $this->queueManager
            ->expects($this->any())
            ->method('getQueues')
            ->will($this->returnValue(array()));

        $this->inject($this->jobManager, 'queueManager', $this->queueManager);

        $worker = $this->getMock(Worker::class, array('work'), array(), '', false);
        $this->inject($this->jobManager, 'worker', $this->worker);

        $this->extensionConfiguration = $this->getMock(ExtensionConfiguration::class, array('get'), array(), '', false);
        $this->inject($this->jobManager, 'extensionConfiguration', $this->extensionConfiguration);

        $this->failedJobRepository = $this->getMock(FailedJobRepository::class, array('add'), array(), '', false);
        $this->inject($this->jobManager, 'failedJobRepository', $this->failedJobRepository);

        $signalSlotDispatcher = $this->getMock(Dispatcher::class, array('dispatch'), array(), '', false);
        $this->inject($this->jobManager, 'signalSlotDispatcher', $signalSlotDispatcher);
    }
    public function tearDown()
    {
        unset(
            $this->jobManager,
            $this->queueManager,
            $this->testQueue,
            $this->extensionConfiguration
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
        $this->jobManager->delay($this->queueName, $job, 5);
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
     * @expectedException R3H6\Jobqueue\Exception
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

        $this->extensionConfiguration
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($attemps));

        $job = $this->getMock(TestJob::class, array('execute'), array(), '', false);
        $job
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(false));

        $this->jobManager->initializeObject();
        $this->jobManager->queue($this->queueName, $job);

        $this->failedJobRepository
            ->expects($this->once())
            ->method('add');

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
