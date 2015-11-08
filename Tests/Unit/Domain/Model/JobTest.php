<?php

namespace TYPO3\Jobqueue\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 R3 H6 <r3h6@outlook.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for class \TYPO3\Jobqueue\Domain\Model\Job.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author R3 H6 <r3h6@outlook.com>
 */
class JobTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \TYPO3\Jobqueue\Domain\Model\Job
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \TYPO3\Jobqueue\Domain\Model\Job();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getQueueNameReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getQueueName()
		);
	}

	/**
	 * @test
	 */
	public function setQueueNameForStringSetsQueueName() {
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
	public function getPayloadReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getPayload()
		);
	}

	/**
	 * @test
	 */
	public function setPayloadForStringSetsPayload() {
		$this->subject->setPayload('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'payload',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStateReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getState()
		);
	}

	/**
	 * @test
	 */
	public function setStateForIntegerSetsState() {
		$this->subject->setState(12);

		$this->assertAttributeEquals(
			12,
			'state',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAttempsReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getAttemps()
		);
	}

	/**
	 * @test
	 */
	public function setAttempsForIntegerSetsAttemps() {
		$this->subject->setAttemps(12);

		$this->assertAttributeEquals(
			12,
			'attemps',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStarttimeReturnsInitialValueForDateTime() {
		$this->assertEquals(
			NULL,
			$this->subject->getStarttime()
		);
	}

	/**
	 * @test
	 */
	public function setStarttimeForDateTimeSetsStarttime() {
		$dateTimeFixture = new \DateTime();
		$this->subject->setStarttime($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'starttime',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getTstampReturnsInitialValueForDateTime() {
		$this->assertEquals(
			NULL,
			$this->subject->getTstamp()
		);
	}

	/**
	 * @test
	 */
	public function setTstampForDateTimeSetsTstamp() {
		$dateTimeFixture = new \DateTime();
		$this->subject->setTstamp($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'tstamp',
			$this->subject
		);
	}
}
