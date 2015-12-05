<?php

namespace TYPO3\Jobqueue\Hooks;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Common". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\Jobqueue\Command\JobCommandController;

/**
 * Jobs
 */
class Jobs implements SingletonInterface
{
    public function spool()
    {
        /** @var TYPO3\Jobqueue\Command\JobCommandController $jobManager */
        $jobCommandController = GeneralUtility::makeInstance(ObjectManager::class)->get(JobCommandController::class);

        $jobCommandController->workCommand(JobCommandController::ARG_ALL_QUEUES);
    }
}
