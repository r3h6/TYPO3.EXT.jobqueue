<?php

namespace TYPO3\Jobqueue\Tests\Unit\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Jobqueue\Utility\ClassNamingUtility;

/**
 * ClassNamingUtilityTest.
 */
class ClassNamingUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @test
     * @dataProvider explodeDataProvider
     */
    public function explode($expected, $className)
    {
        $this->assertArraySubset($expected, ClassNamingUtility::explode($className), "Failed for '$className'!");
    }

    public function explodeDataProvider()
    {
        return [
            [
                [
                    'vendorName' => 'TYPO3',
                    'extensionName' => 'Jobqueue',
                    'subpackageKey' => 'Queue',
                    'className' => 'RuntimeQueue',
                ],
                'TYPO3\\Jobqueue\\Queue\\RuntimeQueue'
            ],
            [
                [
                    'vendorName' => 'TYPO3',
                    'extensionName' => 'JobqueueDatabase',
                    'subpackageKey' => 'Queue',
                    'className' => 'DatabaseQueue',
                ],
                'TYPO3\\JobqueueDatabase\\Queue\\DatabaseQueue'
            ],
        ];
    }
}
