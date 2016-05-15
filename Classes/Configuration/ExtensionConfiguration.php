<?php

namespace R3H6\Jobqueue\Configuration;

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
 * ExtensionConfiguration
 */
class ExtensionConfiguration implements \TYPO3\CMS\Core\SingletonInterface
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

    private function _get($key)
    {
        $value = (is_array($this->configuration) && array_key_exists($key, $this->configuration)) ? $this->configuration[$key]: null;
        if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($value)) {
            return (int) $value;
        }

        return $value;
    }

    private function _set($key, $value)
    {
        $this->configuration[$key] = $value;
    }

    public function __call($method, $args)
    {
        if (method_exists($this, '_' . $method)) {
            return call_user_func_array([$this, '_' . $method], $args);
        }
        throw new \RuntimeException("Method $method doesn't exist", 1461958193);
    }

    public static function __callStatic($method, $args)
    {
        $instance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class);
        return call_user_func_array([$instance, $method], $args);
    }
}
