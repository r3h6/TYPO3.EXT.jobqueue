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

/**
 * Job command controller
 */
class JobCommandController extends CommandController {

	/**
	 * @var JobManager
	 * @inject TYPO3\Jobqueue\Job\JobManager
	 */
	protected $jobManager;

	/**
	 * @var QueueManager
	 * @todo
	 */
	protected $queueManager;

	/**
	 * Work on a queue and execute jobs
	 *
	 * @param string $queueName The name of the queue
	 * @return void
	 */
	public function workCommand($queueName) {
		do {
			$this->jobManager->waitAndExecute($queueName);
		} while (TRUE);
	}

	/**
	 * List queued jobs
	 *
	 * @param string $queueName The name of the queue
	 * @param integer $limit Number of jobs to list
	 * @return void
	 */
	public function listCommand($queueName, $limit = 1) {
		$jobs = $this->jobManager->peek($queueName, $limit);
		$totalCount = $this->queueManager->getQueue($queueName)->count();
		foreach ($jobs as $job) {
			$this->outputLine('<u>%s</u>', array($job->getLabel()));
		}

		if ($totalCount > count($jobs)) {
			$this->outputLine('(%d omitted) ...', array($totalCount - count($jobs)));
		}
		$this->outputLine('(<b>%d total</b>)', array($totalCount));
	}


	/**
	 * [helpCommand description]
	 * @return [type] [description]
	 * @cli
	 */
	public function helpCommand (){

	}
}