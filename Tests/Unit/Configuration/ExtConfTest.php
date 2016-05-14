<?php

namespace TYPO3\Jobqueue\Tests\Unit\Configuration;

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

use TYPO3\Jobqueue\Configuration\ExtConf;

/**
 * Unit tests for the ExtConf.
 */
class ExtConfTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    public function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][ExtConf::EXT_KEY] = serialize(['test' => 'TYPO3']);
    }

    /**
     * @test
     */
    public function getValue()
    {
        $extConf = new ExtConf();
        $this->assertEquals('TYPO3', $extConf->get('test'));
    }

    /**
     * @test
     */
    public function staticGetValue()
    {
        $this->assertEquals('TYPO3', ExtConf::get('test'));
    }

    /**
     * @test
     */
    public function setValue()
    {
        $extConf = new ExtConf();
        $extConf->set('test', 'ExtBase');
        $this->assertEquals('ExtBase', $extConf->get('test'));
    }

    /**
     * @test
     */
    public function staticSetValue()
    {
        ExtConf::set('test', 'ExtBase');
        $this->assertEquals('ExtBase', ExtConf::get('test'));
    }
}
