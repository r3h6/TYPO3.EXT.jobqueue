<?php

namespace TYPO3\Jobqueue\Tests\Unit\Domain\Model;

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

/**
 * Test case for class \TYPO3\Jobqueue\Domain\Model\FailedJob.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author R3 H6 <r3h6@outlook.com>
 */
class FailedJobTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TYPO3\Jobqueue\Domain\Model\FailedJob
     */
    protected $subject = null;

    public function setUp()
    {
        $this->subject = new \TYPO3\Jobqueue\Domain\Model\FailedJob('TestQueue', 'Payload');
    }

    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getQueueNameReturnsInitialValueForString()
    {
        $this->assertSame(
            'TestQueue',
            $this->subject->getQueueName()
        );
    }

    /**
     * @test
     */
    public function setQueueNameForStringSetsQueueName()
    {
        $this->subject->setQueueName('Conceived at T3CON10');

        $this->assertAttributeEquals(
            'Conceived at T3CON10',
            'queueName',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getPayloadReturnsInitialValueForString()
    {
        $this->assertSame(
            'Payload',
            $this->subject->getPayload()
        );
    }

    /**
     * @test
     */
    public function setPayloadForStringSetsPayload()
    {
        $this->subject->setPayload('Conceived at T3CON10');

        $this->assertAttributeEquals(
            'Conceived at T3CON10',
            'payload',
            $this->subject
        );
    }
}
