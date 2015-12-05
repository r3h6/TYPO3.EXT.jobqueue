<?php

namespace TYPO3\Jobqueue\Tests\Unit\Fixtures;

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

use TYPO3\Jobqueue\Job\JobInterface;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueInterface;

/**
 * Test job
 */
class TestJob implements JobInterface
{
    /**
     * @var bool
     */
    protected $processed = false;

    /**
     * Do nothing.
     *
     * @param QueueInterface $queue
     * @param Message        $message
     *
     * @return bool
     */
    public function execute(QueueInterface $queue, Message $message)
    {
        $this->processed = true;

        return true;
    }

    /**
     * @return bool
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Get an optional identifier for the job.
     *
     * @return string A job identifier
     */
    public function getIdentifier()
    {
        return 'testjob';
    }

    /**
     * Get a readable label for the job.
     *
     * @return string A label for the job
     */
    public function getLabel()
    {
        return 'Test Job';
    }
}
