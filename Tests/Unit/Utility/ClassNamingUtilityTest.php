<?php

namespace R3H6\Jobqueue\Tests\Unit\Utility;

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

use R3H6\Jobqueue\Utility\ClassNamingUtility;

/**
 * Unit test for the ClassNamingUtility.
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
                    'vendorName' => 'R3H6',
                    'extensionName' => 'Jobqueue',
                    'subpackageKey' => 'Queue',
                    'className' => 'MemoryQueue',
                ],
                'R3H6\\Jobqueue\\Queue\\MemoryQueue'
            ],
            [
                [
                    'vendorName' => 'R3H6',
                    'extensionName' => 'JobqueueDatabase',
                    'subpackageKey' => 'Queue',
                    'className' => 'DatabaseQueue',
                ],
                'R3H6\\JobqueueDatabase\\Queue\\DatabaseQueue'
            ],
        ];
    }
}
