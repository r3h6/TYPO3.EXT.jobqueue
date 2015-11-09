<?php
namespace TYPO3\Jobqueue\Tests\Unit\Queue;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Queue\DatabaseQueue;
use TYPO3\Jobqueue\Configuration\ExtConf;
use TYPO3\Jobqueue\Tests\Unit\Fixtures\TestQueue;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Queue manager
 */
class QueueManagerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	protected $queueManager;

	protected $extConf;

	protected $objectManager;

	public function setUp (){
		$this->queueManager = new QueueManager();

		$this->extConf = $this->getMock(ExtConf::class, array('getDefaultQueue'), array(), '', FALSE);
		$this->inject($this->queueManager, 'extConf', $this->extConf);

		$this->objectManager = $this->getMock(ObjectManager::class, array('get'), array(), '', FALSE);
		$this->inject($this->queueManager, 'objectManager', $this->objectManager);
	}

	public function tearDown (){
		unset($this->queueManager, $this->extConf);
	}

	/**
	 * @test
	 */
	public function getQueueCreatesDefaultQueue() {
		$queueName = 'TestQueue';

		$this->extConf
			->expects($this->once())
			->method('getDefaultQueue')
			->will($this->returnValue(TestQueue::class));

		$this->objectManager
			->expects($this->once())
			->method('get')
			->with(
				$this->equalTo(TestQueue::class),
				$this->equalTo($queueName),
				$this->equalTo(NULL)
			)
			->will($this->returnValue(new TestQueue($queueName, NULL)));

		$testQueue = $this->queueManager->getQueue($queueName);
		$this->assertInstanceOf(TestQueue::class, $testQueue);
	}

	/**
	 * @test
	 */
	public function getQueueCreatesQueueByName() {
		$options = array('foo' => 'bar');

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue'][$queueName] = array(
			'className' => TestQueue::class,
			'options' => $options,
		);

		$this->objectManager
			->expects($this->once())
			->method('get')
			->with(
				$this->equalTo(TestQueue::class),
				$this->equalTo($queueName),
				$this->equalTo($options)
			)
			->will($this->returnValue(new TestQueue($queueName, $options)));

		$testQueue = $this->queueManager->getQueue($queueName);
		$this->assertInstanceOf(TestQueue::class, $testQueue);
	}
}
