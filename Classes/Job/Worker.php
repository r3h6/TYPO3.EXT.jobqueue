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

    public function daemon($queueName, $timeout = 0)
    {
        $pid = getmypid();

        $lastRestart = $this->registry->get(Registry::DAEMON_RESTART_KEY);
        $sleep = 1;
        $memory = 64;

        $this->getLogger()->info(sprintf('Started daemon in process "%s"', $pid));

        while (true) {
            if ($this->daemonShouldRun()) {
                $this->run($queueName, $timeout);
            }
            $this->sleep($sleep);

            if ($this->memoryExceeded($memory) || $this->queueShouldRestart($lastRestart)) {
                $this->getLogger()->info(sprintf('Stopped daemon in process "%s"', $pid));
                $this->stop();
            }
        }
    }

    public function run($queueName, $timeout = 0)
    {
        // do {
        try {
            $job = $this->jobManager->waitAndExecute($queueName, $timeout);
            if ($job instanceof JobInterface) {
                $this->getLogger()->info(sprintf('Job "%s" (%s) done by %s', $job->getLabel(), $job->getIdentifier(), getmypid()));
            }
        } catch (Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
        // } while ($job instanceof JobInterface);
    }

    protected function daemonShouldRun()
    {
        return ((bool) $this->configurationManager->getLocalConfiguration('FE.pageUnavailable_force') === true);
    }

    protected function queueShouldRestart($lastRestart)
    {
        return ($this->registry->get(Registry::DAEMON_RESTART_KEY) !== $lastRestart);
    }

    protected function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    protected function sleep($sleep)
    {
        sleep($sleep);
    }

    protected function stop()
    {
        die;
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
