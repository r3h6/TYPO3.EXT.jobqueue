<?php

namespace R3H6\Jobqueue\Tests\Unit\Queue;

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

use R3H6\Jobqueue\Queue\QueueManager;
use R3H6\Jobqueue\Configuration\ExtensionConfiguration;
use R3H6\Jobqueue\Queue\MemoryQueue;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Unit tests for the QueueManager.
 */
class QueueManagerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    protected $queueManager;

    protected $extensionConfiguration;

    protected $objectManager;

    public function setUp()
    {
        $this->queueManager = new QueueManager();

        $this->extensionConfiguration = $this->getMock(ExtensionConfiguration::class, array('get'), array(), '', false);
        $this->inject($this->queueManager, 'extensionConfiguration', $this->extensionConfiguration);

        $this->objectManager = $this->getMock(ObjectManager::class, array('get'), array(), '', false);
        $this->inject($this->queueManager, 'objectManager', $this->objectManager);
    }

    public function tearDown()
    {
        unset($this->queueManager, $this->extensionConfiguration);
    }

    /**
     * @test
     */
    public function getQueueCreatesDefaultQueue()
    {
        $queueName = 'MemoryQueue';
        $timeout = null;

        $this->extensionConfiguration
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                $this->equalTo('defaultQueue'),
                $this->equalTo('defaultTimeout')
            )
            ->will($this->onConsecutiveCalls(MemoryQueue::class, $timeout));

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo(MemoryQueue::class),
                $this->equalTo($queueName),
                $this->equalTo(['timeout' => $timeout])
            )
            ->will($this->returnValue(new MemoryQueue($queueName, null)));

        $runtimeQueue = $this->queueManager->getQueue($queueName);
        $this->assertInstanceOf(MemoryQueue::class, $runtimeQueue);
    }

    /**
     * @test
     */
    public function getQueueCreatesQueueByName()
    {
        $options = array(
            'foo' => 'bar',
            'timeout' => null,
        );

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'][$queueName] = array(
            'className' => MemoryQueue::class,
            'options' => $options,
        );

        $this->objectManager
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo(MemoryQueue::class),
                $this->equalTo($queueName),
                $this->equalTo($options)
            )
            ->will($this->returnValue(new MemoryQueue($queueName, $options)));

        $runtimeQueue = $this->queueManager->getQueue($queueName);
        $this->assertInstanceOf(MemoryQueue::class, $runtimeQueue);
    }
}
