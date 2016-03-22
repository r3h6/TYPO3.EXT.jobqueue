<?php

namespace TYPO3\Jobqueue;

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
 * Registry
 */
class Registry extends \TYPO3\CMS\Core\Registry
{
    const REGISTRY_NAMESPACE = 'tx_jobqueue';

    public function set($key, $value)
    {
        parent::set(self::REGISTRY_NAMESPACE, $key, $value);
    }

    public function get($key, $defaultValue = null)
    {
        $this->validateNamespace(self::REGISTRY_NAMESPACE);
        // if (!$this->isNamespaceLoaded(self::REGISTRY_NAMESPACE)) {
            $this->loadEntriesByNamespace(self::REGISTRY_NAMESPACE);
        // }
        return isset($this->entries[self::REGISTRY_NAMESPACE][$key]) ? $this->entries[self::REGISTRY_NAMESPACE][$key] : $defaultValue;
    }

    /**
     * @return TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabasConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
