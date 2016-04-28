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
     * @var TYPO3\Jobqueue\Configuration\ExtConf
     * @inject
     */
    protected $extConf;

    /**
     * @var \TYPO3\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager;

    /**
     * @var \TYPO3\Jobqueue\Registry
     * @inject
     */
    protected $registry;

    /**
     * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * Works on a queue till limit is reached, kill signal has sent or memory is exceeded.
     * @param  string  $queueName
     * @param  integer $timeout
     * @param  integer $limit
     * @param  integer $sleep
     * @param  integer $memoryLimit
     * @return void
     */
    public function work($queueName, $timeout = 0, $limit = 1, $sleep = null, $memoryLimit = null)
    {
        if ($sleep === null) {
            $sleep = (int) $this->extConf->getSleep();
        }
        if ($memoryLimit === null) {
            $memoryLimit = (int) $this->extConf->getMemoryLimit();
        }
        $sleep = max(1, $sleep);

        $pid = getmypid();
        $lastRestart = $this->registry->get(Registry::DAEMON_KILL_KEY);


        $this->getLogger()->info(sprintf('Started daemon in process "%s"', $pid));

        do {
            if ($this->shouldRun()) {
                $job = $this->executeNextJob($queueName, $timeout);
                if ($limit !== self::LIMIT_INFINITE && $job === null) {
                    break;
                } else if ($limit > 0 && --$limit < 1) {
                    break;
                }
            }
            $this->sleep($sleep);

            if ($this->memoryExceeded($memoryLimit) || $this->shouldRestart($lastRestart)) {
                $this->getLogger()->info(sprintf('Stopped daemon in process "%s"', $pid));
                break;
            }
        } while (true);
    }

    /**
     * Executes the next job.
     *
     * @param   string $queueName
     * @param   int $timeout
     * @return  JobInterface|null
     */
    protected function executeNextJob($queueName, $timeout)
    {
        $job = null;
        try {
            $job = $this->jobManager->waitAndExecute($queueName, $timeout);
            if ($job instanceof JobInterface) {
                $this->getLogger()->info(sprintf('Job "%s" (%s) done by %s', $job->getLabel(), $job->getIdentifier(), getmypid()));
            }
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
        return $job;
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
     * Sleep
     *
     * @param  int $sleep  seconds
     * @return void
     */
    protected function sleep($sleep)
    {
        sleep($sleep);
    }


    /**
     * Get class logger
     *
     * @return TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
    }
}
