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

    public function daemon($pid, $queueName, $timeout = 0)
    {
        $key = 'daemon:' . $pid;

        $lastRestart = $this->registry->get($key);
        $sleep = 10;
        $memory = 64;//ini_get('memory_limit');

        $this->getLogger()->error(sprintf('Run daemon %s', $key));
                $this->getLogger()->error($memory);
                $this->getLogger()->error(memory_get_usage());

        while (true) {
            if ($this->daemonShouldRun()) {
                $this->run($queueName, $timeout);
            } else {
                $this->sleep($sleep);
            }

            if ($this->memoryExceeded($memory) || $this->queueShouldRestart($key, $lastRestart)) {
                $this->getLogger()->error(sprintf('Stop daemon %s', $key));
                $this->stop();
            }
            break;
        }
                $this->getLogger()->error(sprintf('Done daemon %s', $key));
    }

    public function run($queueName, $timeout = 0)
    {
        // do {
        try {
            $job = $this->jobManager->waitAndExecute($queueName, $timeout);
        } catch (Exception $exception) {
            // throw $exception;
        }
        // } while ($job instanceof JobInterface);
    }

    protected function daemonShouldRun()
    {
        return ((bool) $this->configurationManager->getLocalConfiguration('FE.pageUnavailable_force') === false);
    }

    protected function queueShouldRestart($key, $lastRestart)
    {
        return ($this->registry->get($key) !== $lastRestart);
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
