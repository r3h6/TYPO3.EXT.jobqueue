<?php

namespace TYPO3\Jobqueue\Job;

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

use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueInterface;
use TYPO3\Jobqueue\Registry;

/**
 * Worker
 */
class Worker
{
    const LIMIT_INFINITE = 0;
    const LIMIT_QUEUE = -1;

    /**
     * @var TYPO3\Jobqueue\Configuration\ExtensionConfiguration
     * @inject
     */
    protected $extensionConfiguration = null;

    /**
     * @var \TYPO3\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager = null;

    /**
     * @var \TYPO3\Jobqueue\Registry
     * @inject
     */
    protected $registry = null;

    /**
     * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager = null;

    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger = null;

    /**
     * Work on a queue and execute jobs.
     *
     * @param  string  $queueName the name of the queue
     * @param  integer $timeout   time a queue waits for a job in seconds
     * @param  integer $limit     number of jobs to be done, 0 for all jobs in queue, -1 for work infinite
     * @return void
     */
    public function work($queueName, $timeout = 0, $limit = 1)
    {
        $this->getLogger()->info(sprintf('Work on queue "%s" in process "%s"', $queueName, getmypid()));

        $memoryLimit = (int) $this->extensionConfiguration->get('memoryLimit');
        $lastRestart = $this->registry->get(Registry::DAEMON_KILL_KEY);

        if ($limit === self::LIMIT_INFINITE) {
            $timeout = max(1, $timeout);
        }

        do {
            if ($this->shouldRun()) {
                $continueWork = $this->waitAndExecute($queueName, $timeout);
                if ($limit === self::LIMIT_QUEUE && $continueWork === false) {
                    break;
                } else if ($limit > 0 && --$limit < 1) {
                    break;
                }
            }

            if ($this->memoryExceeded($memoryLimit) || $this->shouldRestart($lastRestart)) {
                $this->getLogger()->info(sprintf('Stopped work on queue "%s" in process "%s"', $queueName, getmypid()));
                break;
            }
        } while (true);
    }

    /**
     * Executes the next job.
     *
     * @param   string $queueName
     * @param   int $timeout
     * @return  boolean true if there are still jobs todo
     */
    protected function waitAndExecute($queueName, $timeout)
    {
        $continueWork = true;
        try {
            $job = $this->jobManager->waitAndExecute($queueName, $timeout);
            if ($job === null) {
                $continueWork = false;
            } else {
                $this->getLogger()->info(sprintf('Job "%s" (%s) done in process %s', $job->getLabel(), $job->getIdentifier(), getmypid()));
            }
        } catch (\Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
        return $continueWork;
    }

    /**
     * Checks if frontend is available or not.
     *
     * @return boolean     false if not.
     */
    protected function shouldRun()
    {
        return ((bool) $this->configurationManager->getLocalConfiguration('FE.pageUnavailable_force') === true);
    }

    /**
     * Returns true if kill signal has been broadcasted.
     *
     * @param  mixed $lastRestart
     * @return boolean
     */
    protected function shouldRestart($lastRestart)
    {
        return ($this->registry->get(Registry::DAEMON_KILL_KEY) !== $lastRestart);
    }

    /**
     * Check memory usage against a limit.
     *
     * @param  int $memoryLimit max usage in mb
     * @return boolean true if limit is exceeded
     */
    protected function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Get class logger
     *
     * @return TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }
        return $this->logger;
    }
}
