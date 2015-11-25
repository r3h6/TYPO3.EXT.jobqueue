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
	 * ExtConf
	 * @var TYPO3\Jobqueue\Configuration\ExtConf
	 * @inject
	 */
	protected $extConf;

	/**
	 *
	 * @param string $queueName
	 * @return QueueInterface
	 * @throws JobQueueException
	 */
	public function getQueue($queueName) {
		if (!isset($this->queues[$queueName])) {
			$settings = $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue'];
			$className = $this->extConf->getDefaultQueue();
			if (isset($settings[$queueName])){
				$className = isset($settings[$queueName]['className']) ? $settings[$queueName]['className']: NULL;
				$options = isset($settings[$queueName]['options']) ? $settings[$queueName]['options']: NULL;
			} else {
				$options = isset($settings[$className]['options']) ? $settings[$className]['options']: NULL;
			}

			if (empty($className)){
				throw new JobQueueException("No jobqueue class name configuration found.", 1448488276);
			}

			$queue = $this->objectManager->get($className, $queueName, $options);

			if (!($queue instanceof QueueInterface)){
				throw new JobQueueException("Queue '$queueName' is not a queue.", 1446318455);
			}

			$this->queues[$queueName] = $queue;
		}
		return $this->queues[$queueName];
	}

	public function getQueues (){
		return $this->queues;
	}

}