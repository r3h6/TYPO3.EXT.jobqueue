<?php
namespace TYPO3\Jobqueue\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Bastian Waidelich
 *           R3 H6 <r3h6@outlook.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * Message
 */
class Job extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Queue name
	 *
	 * @var string
	 */
	protected $queueName = '';

	/**
	 * Payload
	 *
	 * @var string
	 */
	protected $payload = '';

	/**
	 * State
	 *
	 * @var integer
	 */
	protected $state = 0;

	/**
	 * Attemps
	 *
	 * @var integer
	 */
	protected $attemps = 0;

	/**
	 * Starttime
	 *
	 * @var \DateTime
	 */
	protected $starttime = NULL;

	/**
	 * Timestamp
	 *
	 * @var \DateTime
	 */
	protected $tstamp = NULL;

	/**
	 * Returns the payload
	 *
	 * @return string $payload
	 */
	public function getPayload() {
		return $this->payload;
	}

	/**
	 * Sets the payload
	 *
	 * @param string $payload
	 * @return void
	 */
	public function setPayload($payload) {
		$this->payload = $payload;
	}

	/**
	 * Returns the state
	 *
	 * @return int $state
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets the state
	 *
	 * @param int $state
	 * @return void
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Returns the attemps
	 *
	 * @return int $attemps
	 */
	public function getAttemps() {
		return $this->attemps;
	}

	/**
	 * Sets the attemps
	 *
	 * @param int $attemps
	 * @return void
	 */
	public function setAttemps($attemps) {
		$this->attemps = $attemps;
	}

	/**
	 * Returns the queueName
	 *
	 * @return string $queueName
	 */
	public function getQueueName() {
		return $this->queueName;
	}

	/**
	 * Sets the queueName
	 *
	 * @param string $queueName
	 * @return void
	 */
	public function setQueueName($queueName) {
		$this->queueName = $queueName;
	}

	/**
	 * Returns the starttime
	 *
	 * @return \DateTime starttime
	 */
	public function getStarttime() {
		return $this->starttime;
	}

	/**
	 * Sets the starttime
	 *
	 * @param \DateTime $starttime
	 * @return \DateTime starttime
	 */
	public function setStarttime(\DateTime $starttime = NULL) {
		$this->starttime = $starttime;
	}

	/**
	 * Returns the tstamp
	 *
	 * @return \DateTime tstamp
	 */
	public function getTstamp() {
		return $this->tstamp;
	}

	/**
	 * Sets the tstamp
	 *
	 * @param \DateTime $tstamp
	 * @return \DateTime tstamp
	 */
	public function setTstamp(\DateTime $tstamp) {
		$this->tstamp = $tstamp;
	}

}