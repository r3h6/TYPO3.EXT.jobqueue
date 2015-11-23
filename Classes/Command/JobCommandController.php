<?php
namespace TYPO3\Jobqueue\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Common". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Job command controller
 */
class JobCommandController extends CommandController {

	/**
	 * @var \TYPO3\Jobqueue\Job\JobManager
	 * @inject
	 */
	protected $jobManager;

	/**
	 * Work on a queue and execute jobs
	 *
	 * @param string $queueName The name of the queue
	 * @return void
	 */
	public function workCommand($queueName) {
		do {
			$message = $this->jobManager->waitAndExecute($queueName);
		} while ($message instanceof Message);
	}

	/**
	 * List queued jobs
	 *
	 * @param string $queueName The name of the queue
	 * @param integer $limit Number of jobs to list
	 * @return void
	 * @cli
	 */
	public function listCommand($queueName, $limit = 1) {
		$jobs = $this->jobManager->peek($queueName, $limit);
		$totalCount = $this->jobManager->getQueue($queueName)->count();
		foreach ($jobs as $job) {
			$this->outputLine('<u>%s</u>', array($job->getLabel()));
		}

		if ($totalCount > count($jobs)) {
			$this->outputLine('(%d omitted) ...', array($totalCount - count($jobs)));
		}
		$this->outputLine('(<b>%d total</b>)', array($totalCount));
	}

	/**
	 *
	 * @param string $queueName The name of the queue
	 * @return void
	 * @cli
	 */
	public function infoCommand ($queueName){
		$queue = $this->jobManager->getQueue($queueName);

		$options = $queue->getOptions();

		$this->outputFormatted('Class: %s', [get_class($queue)]);
		$this->outputFormatted('Options:');
		if (is_array($options)){
			foreach ($options as $key => $value){
				$this->outputFormatted('%s: %s', [$key, $value], 3);
			}
		} else {
			$this->outputFormatted('NULL', [], 3);
		}
	}
}