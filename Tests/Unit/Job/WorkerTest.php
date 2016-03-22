<?php

namespace TYPO3\Jobqueue\Tests\Unit\Job;

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

use TYPO3\Jobqueue\Job\Worker;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\Jobqueue\Registry;

/**
 * Unit tests for the Worker.
 */
class WorkerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var Worker
     */
    protected $worker;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var Registry
     */
    protected $registry;

    /**
     *
     */
    public function setUp()
    {

        $this->worker = $this->getMock(Worker::class, array('getLogger', 'daemonShouldRun', 'memoryExceeded', 'queueShouldRestart'), array(), '', false);

        $this->registry = $this->getMock(Registry::class, array('get', 'set'), array(), '', false);
        $this->inject($this->worker, 'registry', $this->registry);

        $this->logger = $this->getMock(Logger::class, get_class_methods(Logger::class), array(), '', false);
        $this->worker
            ->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->logger));
    }
    public function tearDown()
    {
        unset(
            $this->worker,
            $this->registry,
            $this->logger
        );
    }

    /**
     * @test
     */
    public function workerStartsNewDaemon()
    {
        $this->registry
            ->expects($this->once())
            ->method('set');
        $this->worker->daemon('test');
    }
}
