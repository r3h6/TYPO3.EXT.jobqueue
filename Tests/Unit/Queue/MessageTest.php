<?php
namespace TYPO3\Jobqueue\Tests\Unit\Queue;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * MessageTest
 */
class MessageTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Jobqueue\Queue\Message
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \TYPO3\Jobqueue\Queue\Message('');
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function setIdentifierForStringSetsIdentifier() {
		$this->subject->setIdentifier('Conceived at T3CON10');
		$this->assertAttributeEquals('Conceived at T3CON10', 'identifier', $this->subject);
	}

	/**
	 * @test
	 */
	public function setStateForIntSetsState() {
		$this->subject->setState(123);
		$this->assertAttributeEquals(123, 'state', $this->subject);
	}

	/**
	 * @test
	 */
	public function setAttempsForIntSetsAttemps() {
		$this->subject->setAttemps(123);
		$this->assertAttributeEquals(123, 'attemps', $this->subject);
	}

	/**
	 * @test
	 */
	public function setAvailableAtForDateTimeSetsAvailableAt() {
		$dateTimeFixture = new \DateTime();
		$this->subject->setAvailableAt($dateTimeFixture);
		$this->assertAttributeEquals($dateTimeFixture, 'availableAt', $this->subject);
	}

	/**
	 * @test
	 */
	public function toArray (){
		$this->assertSame([
			'identifier' => NULL,
			'payload' => '',
			'state' => \TYPO3\Jobqueue\Queue\Message::STATE_NEW,
			'attemps' => 0
		], $this->subject->toArray(), 'Wrong initial values');
	}

	/**
	 * @test
	 */
	public function setAvailableAtAndGetDelay() {
		$dateTimeFixture = new \DateTime('now + 7sec');
		$this->subject->setAvailableAt($dateTimeFixture);
		$this->assertSame(7, $this->subject->getDelay());
	}
}
