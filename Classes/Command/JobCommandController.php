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

use Exception;
use TYPO3\Jobqueue\Job\JobInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Job command controller.
 */
class JobCommandController extends CommandController
{
    const ARG_ALL_QUEUES = 'all';

    /**
     * @var \TYPO3\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager;

    /**
     * Work on a queue and execute jobs.
     *
     * @param string $queueName The name of the queue
     * @todo Exception handling
     */
    public function workCommand($queueName)
    {
        $queueNames = GeneralUtility::trimExplode(',', $queueName);
        if ($queueName === self::ARG_ALL_QUEUES) {
            $queueNames = $this->jobManager->getQueueManager()->getQueueNames();
        }

        foreach ($queueNames as $queueName) {
            do {
                try {
                    $job = $this->jobManager->waitAndExecute($queueName);
                } catch (Exception $exception) {
                    throw $exception;
                }
            } while ($job instanceof JobInterface);
        }
    }

    /**
     * List queued jobs.
     *
     * @param string $queueName The name of the queue
     * @param int    $limit     Number of jobs to list
     * @cli
     */
    public function listCommand($queueName, $limit = 1)
    {
        $jobs = $this->jobManager->peek($queueName, $limit);
        $totalCount = $this->jobManager->getQueueManager()->getQueue($queueName)->count();
        foreach ($jobs as $job) {
            $this->outputLine('<u>%s</u>', array($job->getLabel()));
        }

        if ($totalCount > count($jobs)) {
            $this->outputLine('(%d omitted) ...', array($totalCount - count($jobs)));
        }
        $this->outputLine('(<b>%d total</b>)', array($totalCount));
    }

    /**
     * @param string $queueName The name of the queue
     * @cli
     */
    public function infoCommand($queueName)
    {
        $queue = $this->jobManager->getQueueManager()->getQueue($queueName);

        $options = $queue->getOptions();

        $this->outputFormatted('Class: %s', [get_class($queue)]);
        $this->outputFormatted('Options:');
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $this->outputFormatted('%s: %s', [$key, $value], 3);
            }
        } else {
            $this->outputFormatted('NULL', [], 3);
        }
    }
}
