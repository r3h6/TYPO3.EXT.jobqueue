<?php

namespace TYPO3\Jobqueue\Hooks;

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
