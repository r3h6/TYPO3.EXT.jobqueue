<?php
namespace TYPO3\Jobqueue\Domain\Repository;

use TYPO3\Jobqueue\Domain\Model\Job;
use TYPO3\Jobqueue\Queue\Message;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 R3 H6 <r3h6@outlook.com>
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
 * The repository for Messages
 */
class JobRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	protected $defaultOrderings = array(
		'tstamp' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
	);

	/**
	 * @param $queueName
	 */
	public function findNextByQueueName($queueName) {
		/** @var TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
		$query = $this->createQuery();
		$constraints = array();
		$constraints[] = $query->equals('state', Message::STATE_PUBLISHED);
		$constraints[] = $query->logicalOr(
			$query->equals('starttime', 0),
			$query->lessThanOrEqual('starttime', time())
		);
		$query->matching(
			$query->logicalAnd($constraints)
		);
		return $query->execute()->getFirst();
	}

}