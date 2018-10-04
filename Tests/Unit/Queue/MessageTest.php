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

/**
 * Unit test for the Message.
 */
class MessageTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    use \R3H6\Jobqueue\Tests\PhpunitCompatibilityTrait;

    /**
     * @var \R3H6\Jobqueue\Queue\Message
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = new \R3H6\Jobqueue\Queue\Message('');
    }

    protected function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function setIdentifierForStringSetsIdentifier()
    {
        $this->subject->setIdentifier('Conceived at T3CON10');
        $this->assertAttributeEquals('Conceived at T3CON10', 'identifier', $this->subject);
    }

    /**
     * @test
     */
    public function setStateForIntSetsState()
    {
        $this->subject->setState(123);
        $this->assertAttributeEquals(123, 'state', $this->subject);
    }

    /**
     * @test
     */
    public function setAttempsForIntSetsAttemps()
    {
        $this->subject->setAttemps(123);
        $this->assertAttributeEquals(123, 'attemps', $this->subject);
    }

    /**
     * @test
     */
    public function setAvailableAtForDateTimeSetsAvailableAt()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setAvailableAt($dateTimeFixture);
        $this->assertAttributeEquals($dateTimeFixture, 'availableAt', $this->subject);
    }

    /**
     * @test
     */
    public function toArray()
    {
        $this->assertSame([
            'identifier' => null,
            'payload' => '',
            'state' => \R3H6\Jobqueue\Queue\Message::STATE_NEW,
            'attemps' => 0,
        ], $this->subject->toArray(), 'Wrong initial values');
    }

    /**
     * @test
     */
    public function setAvailableAtAndGetDelay()
    {
        $dateTimeFixture = new \DateTime('now + 7sec');
        $this->subject->setAvailableAt($dateTimeFixture);
        $this->assertSame(7, $this->subject->getDelay());
    }
}
