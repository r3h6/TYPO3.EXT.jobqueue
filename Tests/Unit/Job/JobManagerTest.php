<?php
namespace TYPO3\Jobqueue\Tests\Unit\Job;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestJob;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestQueue;

/**
 * Unit tests for the JobManager
 */
class JobManagerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var QueueManager
	 */
	protected $queueManager;

	/**
	 * @var JobManager
	 */
	protected $jobManager;

	protected $testQueue;

	protected $queueName = 'TestQueue';
	/**
	 *
	 */
	public function setUp() {
		$this->testQueue = new TestQueue($this->queueName, NULL);

		$this->queueManager = $this->getMock(QueueManager::class, array('getQueue'), array(), '',  FALSE);
		$this->queueManager
			->expects($this->any())
			->method('getQueue')
			->with($this->queueName)
			->will($this->returnValue($this->testQueue));


		$this->jobManager = new JobManager();
		$this->inject($this->jobManager, 'queueManager', $this->queueManager);
	}
	public function tearDown (){
		unset($this->queueManager, $this->jobManager, $this->testQueue);
	}

	/**
	 * @test
	 */
	public function queuePublishesMessageToQueue() {
		$job = new TestJob();
		$this->jobManager->queue($this->queueName, $job);

		$messages = $this->jobManager->peek($this->queueName);
		$this->assertInternalType('array', $messages);
		$this->assertContainsOnlyInstancesOf(TestJob::class, $messages);
	}

	/**
	 * @test
	 * @depends queuePublishesMessageToQueue
	 */
	public function waitAndExecuteGetsAndExecutesJobFromQueue() {
		$job = new TestJob();
		$this->jobManager->queue($this->queueName, $job);

		$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
		$this->assertInstanceOf(TestJob::class, $queuedJob);
		$this->assertTrue($queuedJob->getProcessed());
	}

	/**
	 * @test
	 */
	public function waitAndExecuteJobThrowsException (){
		$job = $this->getMock(TestJob::class, array('execute'), array(), '', FALSE);
		$job
			->expects($this->once())
			->method('execute')
			->with(
				$this->identicalTo($this->testQueue),
				$this->instanceOf(Message::class)
			)
			->will($this->throwException(new Exception()));

		$this->jobManager->queue($this->queueName, $job);
		try {
			$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
		} catch (Exception $exception){

		}
	}

}
