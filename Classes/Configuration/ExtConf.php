<?php

namespace TYPO3\Jobqueue\Configuration;

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
 * ExtConf
 */
class ExtConf implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var string
     */
    const EXT_KEY = 'jobqueue';

    /**
     * @var array
     */
    protected $configuration = array();

    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY])) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY])) {
                $this->configuration = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY];
            } else {
                $this->configuration = (array) unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY]);
            }
        }
    }

    public function __call($method, $args)
    {
        $key = (strpos($method, 'get') === 0) ? lcfirst(substr($method, 3)) : null;
        if ($key !== null && is_array($this->configuration) && array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        } else {
            return null;
        }
    }
}
