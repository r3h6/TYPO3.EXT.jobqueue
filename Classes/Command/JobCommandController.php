<?php

namespace TYPO3\Jobqueue\Command;

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

use Exception;
use TYPO3\Jobqueue\Job\JobInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Job command controller.
 */
class JobCommandController extends CommandController
{
    // Argument 'all' for work command.
    const ARG_ALL_QUEUES = 'all';

    /**
     * @var \TYPO3\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager;

    /**
     * Work on a queue and execute jobs.
     *
     * @param string      $queueName The name of the queue
     * @param int    $timeout Timeout in seconds or null for no timeout
     * @see JobCommandController::ARG_ALL_QUEUES
     * @todo Exception handling
     */
    public function workCommand($queueName, $timeout = null)
    {
        $queueNames = GeneralUtility::trimExplode(',', $queueName);
        if ($queueName === self::ARG_ALL_QUEUES) {
            throw new Exception("Argument all is not yet implemented!", 1449346695);
        }

        foreach ($queueNames as $queueName) {
            do {
                try {
                    $job = $this->jobManager->waitAndExecute($queueName, $timeout);
                } catch (Exception $exception) {
                    throw $exception;
                }
            } while ($job instanceof JobInterface);
        }
    }

    /**
     * List jobs in a queue.
     *
     * @param string $queueName The name of the queue
     * @param int    $limit     Number of jobs to list
     * @cli
     */
    public function listCommand($queueName, $limit = 1)
    {
        $jobs = $this->jobManager->peek($queueName, $limit);
        $totalCount = $this->jobManager->getQueueManager()->getQueue($queueName)->count();

        $this->outputFormatted('List jobs for queue "%s"...', [$queueName]);

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

        $this->outputFormatted('List infos for queue "%s"...', [$queueName]);
        $this->outputFormatted('<b>Class:</b> %s', [get_class($queue)]);

        if (is_array($options) && !empty($options)) {
            foreach ($options as $key => $value) {
                $this->outputFormatted('<b>%s:</b> %s', [ucfirst($key), ($value === null) ? 'null': $value]);
            }
        }
    }
}
