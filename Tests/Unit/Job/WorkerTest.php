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

use R3H6\Jobqueue\Job\Worker;
use TYPO3\CMS\Core\Log\Logger;
use R3H6\Jobqueue\Registry;
use R3H6\Jobqueue\Configuration\ExtensionConfiguration;
use R3H6\Jobqueue\Job\JobManager;
use R3H6\Jobqueue\Tests\Unit\Fixtures\TestJob;

/**
 * Unit tests for the Worker.
 */
class WorkerTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    use \R3H6\Jobqueue\Tests\PhpunitCompatibilityTrait;

    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var JobManager
     */
    protected $jobManager;

    /**
     *
     */
    public function setUp()
    {

        $this->worker = $this->getMock(Worker::class, array('getLogger', 'shouldRun', 'memoryExceeded', 'shouldRestart'), array(), '', false);

        $this->registry = $this->getMock(Registry::class, array('get', 'set'), array(), '', false);
        $this->inject($this->worker, 'registry', $this->registry);

        $this->logger = $this->getMock(Logger::class, get_class_methods(Logger::class), array(), '', false);
        $this->worker
            ->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->logger));
        $this->worker
            ->expects($this->any())
            ->method('shouldRun')
            ->will($this->returnValue(true));

        $this->extensionConfiguration = $this->getMock(ExtensionConfiguration::class, array('get'), array(), '', false);
        $this->inject($this->worker, 'extensionConfiguration', $this->extensionConfiguration);

        $this->jobManager = $this->getMock(JobManager::class, array('waitAndExecute', '__destruct'), array(), '', false);
        $this->inject($this->worker, 'jobManager', $this->jobManager);
    }
    public function tearDown()
    {
        unset(
            $this->worker,
            $this->registry,
            $this->logger,
            $this->extensionConfiguration
        );
    }

    /**
     * @test
     */
    public function workOneJob()
    {
        $queueName = 'test' . uniqid();
        $timeout = 3;
        $job = new TestJob();
        $this->jobManager
            ->expects($this->once())
            ->method('waitAndExecute')
            ->with($queueName, $timeout)
            ->will($this->returnValue($job));
        $this->worker->work($queueName, $timeout);
    }

    /**
     * @test
     */
    public function workAsLongLimitIsNotReached()
    {
        $queueName = 'test' . uniqid();
        $timeout = 3;
        $limit = 5;
        $job = new TestJob();
        $this->jobManager
            ->expects($this->exactly($limit))
            ->method('waitAndExecute')
            ->with($queueName, $timeout)
            ->will($this->onConsecutiveCalls($job, $job, $job, null, null));
        $this->worker->work($queueName, $timeout, $limit);
    }

    /**
     * @test
     */
    public function workAsLongQueueHasJobs()
    {
        $queueName = 'test' . uniqid();
        $timeout = 3;
        $job = new TestJob();
        $this->jobManager
            ->expects($this->exactly(2))
            ->method('waitAndExecute')
            ->with($queueName, $timeout)
            ->will($this->onConsecutiveCalls($job, null));
        $this->worker->work($queueName, $timeout, Worker::LIMIT_QUEUE);
    }

    /**
     * @test
     */
    public function stoppWorkWhenMemoryExceeds()
    {
        $queueName = 'test' . uniqid();
        $timeout = 3;
        $memoryLimit = 123;

        $this->extensionConfiguration
            ->expects($this->any())
            ->method('get')
            ->with('memoryLimit')
            ->will($this->returnValue($memoryLimit));

        $job = new TestJob();
        $this->jobManager
            ->expects($this->once())
            ->method('waitAndExecute')
            ->with($queueName, $timeout)
            ->will($this->returnValue($job));
        $this->worker
            ->expects($this->any())
            ->method('memoryExceeded')
            ->with($memoryLimit)
            ->will($this->returnValue(true));
        $this->worker->work($queueName, $timeout, Worker::LIMIT_QUEUE);
    }
}
