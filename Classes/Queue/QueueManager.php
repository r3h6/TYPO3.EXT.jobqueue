<?php

namespace TYPO3\Jobqueue\Queue;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\Jobqueue\Exception as JobQueueException;
use TYPO3\Jobqueue\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Queue manager.
 */
class QueueManager implements SingletonInterface
{
    /**
     * ObjectManager.
     *
     * @var TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager = null;

    /**
     * @var array
     */
    protected $queues = array();

    /**
     * ExtConf.
     *
     * @var TYPO3\Jobqueue\Configuration\ExtConf
     * @inject
     */
    protected $extConf;

    /**
     * @param string $queueName
     * @return QueueInterface
     * @throws JobQueueException
     */
    public function getQueue($queueName)
    {
        if (!isset($this->queues[$queueName])) {
            $settings = $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue'];
            $className = $this->extConf->getDefaultQueue();
            if (isset($settings[$queueName])) {
                $className = isset($settings[$queueName]['className']) ? $settings[$queueName]['className'] : null;
                $options = isset($settings[$queueName]['options']) ? $settings[$queueName]['options'] : null;
            } else {
                $options = isset($settings[$className]['options']) ? $settings[$className]['options'] : null;
            }

            if (!isset($options['timeout'])) {
                $defaultTimeout = (int) $this->extConf->getDefaultTimeout();
                $options['timeout'] = ($defaultTimeout > 0) ? $defaultTimeout: null;
            }

            if (empty($className)) {
                throw new JobQueueException('No jobqueue class name configuration found.', 1448488276);
            }

            $classNameParts = ClassNamingUtility::explode($className);
            ExtensionManagementUtility::isLoaded(GeneralUtility::camelCaseToLowerCaseUnderscored($classNameParts['extensionName']), true);

            $queue = $this->objectManager->get($className, $queueName, $options);

            if (!($queue instanceof QueueInterface)) {
                throw new JobQueueException("Queue '$queueName' is not a queue.", 1446318455);
            }

            $this->queues[$queueName] = $queue;
        }

        return $this->queues[$queueName];
    }
}
