<?php
namespace TYPO3\Jobqueue\Queue;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Common". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\Jobqueue\Exception as JobQueueException;

/**
 * Queue manager
 */
class QueueManager implements SingletonInterface {

	/**
	 * ObjectManager
	 * @var TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager = NULL;

	/**
	 * @var array
	 */
	protected $queues = array();

	/**
	 *
	 * @param string $queueName
	 * @return QueueInterface
	 * @throws JobQueueException
	 */
	public function getQueue($queueName) {
		if (!isset($this->queues[$queueName])) {
			// if (!isset($this->settings['queues'][$queueName])) {
			// 	throw new JobQueueException('Queue "' . $queueName . '" is not configured', 1334054137);
			// }
			// if (!isset($this->settings['queues'][$queueName]['className'])) {
			// 	throw new JobQueueException('Option className for queue "' . $queueName . '" is not configured', 1334147126);
			// }
			// $queueObjectName = $this->settings['queues'][$queueName]['className'];
			// $options = isset($this->settings['queues'][$queueName]['options']) ? $this->settings['queues'][$queueName]['options'] : array();
			// $queue = new $queueObjectName($queueName, $options);

			$className = DatabaseQueue::class;
			$queue = $this->objectManager->get($className, $queueName);

			if (!($queue instanceof QueueInterface)){
				throw new Exception("Queue '$queueName' is not a queue.", 1446318455);
			}

			$this->queues[$queueName] = $queue;
		}
		return $this->queues[$queueName];
	}

}