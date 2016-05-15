<?php

namespace R3H6\Jobqueue\Tests\Unit\Configuration;

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

use R3H6\Jobqueue\Configuration\ExtensionConfiguration;

/**
 * Unit tests for the ExtensionConfiguration.
 */
class ExtensionConfigurationTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    public function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][ExtensionConfiguration::EXT_KEY] = serialize(['test' => 'TYPO3']);
    }

    /**
     * @test
     */
    public function getValue()
    {
        $extensionConfiguration = new ExtensionConfiguration();
        $this->assertEquals('TYPO3', $extensionConfiguration->get('test'));
    }

    /**
     * @test
     */
    public function staticGetValue()
    {
        $this->assertEquals('TYPO3', ExtensionConfiguration::get('test'));
    }

    /**
     * @test
     */
    public function setValue()
    {
        $extensionConfiguration = new ExtensionConfiguration();
        $extensionConfiguration->set('test', 'ExtBase');
        $this->assertEquals('ExtBase', $extensionConfiguration->get('test'));
    }

    /**
     * @test
     */
    public function staticSetValue()
    {
        ExtensionConfiguration::set('test', 'ExtBase');
        $this->assertEquals('ExtBase', ExtensionConfiguration::get('test'));
    }
}
