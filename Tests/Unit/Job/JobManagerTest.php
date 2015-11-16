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
use Exception;
use DateTime;
use TYPO3\Jobqueue\Configuration\ExtConf;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Exception as JobQueueException;
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

	protected $extConf;

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

		// $this->jobManager = $this->getMock(JobManager::class);
		$this->jobManager = new JobManager();
		$this->inject($this->jobManager, 'queueManager', $this->queueManager);

		$this->extConf = $this->getMock(ExtConf::class, array('getMaxAttemps'), array(), '', FALSE);
		$this->inject($this->jobManager, 'extConf', $this->extConf);
	}
	public function tearDown (){
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
	public function queuePublishesMessageToQueue() {
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
	public function delayCallsQueue() {
		$job = new TestJob();
		$this->jobManager->delay($this->queueName, 5, $job);
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
	 * @expectedException TYPO3\Jobqueue\Exception
	 * @depends waitAndExecuteGetsAndExecutesJobFromQueue
	 */
	public function waitAndExecuteJobThrowsException (){
		$job = $this->getMock(TestJob::class, array('execute'), array(), '', FALSE);
		$job
			->expects($this->any())
			->method('execute')
			->will($this->returnValue(FALSE));
		$this->jobManager->queue($this->queueName, $job);
		$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
	}

	/**
	 * @test
	 * @depends waitAndExecuteJobThrowsException
	 */
	public function waitAndExecuteJobAttempsThreeTimes (){
		$attemps = 3;

		$this->extConf
			->expects($this->any())
			->method('getMaxAttemps')
			->will($this->returnValue($attemps));

		$job = $this->getMock(TestJob::class, array('execute'), array(), '', FALSE);
		$job
			->expects($this->any())
			->method('execute')
			->will($this->returnValue(FALSE));

		$this->jobManager->initializeObject();
		$this->jobManager->queue($this->queueName, $job);
		for ($i = 0; $i < $attemps + 99; $i++){
			try {
				$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
				if ($queuedJob === NULL){
					break;
				}
			} catch (JobQueueException $exception){
				$this->assertEquals(
					($i + 1 < $attemps) ? 1: 0,
					$this->testQueue->count(),
					'Job is not republished to queue!'
				);
			}
		}
		$this->assertEquals($attemps, $i, 'To many attemps!');
		$this->assertEquals(0, $this->testQueue->count(), 'Queue not empty!');
	}

}
