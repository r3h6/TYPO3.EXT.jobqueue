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
 *
 * API for the sys_registry. This registry doesn't cache the values as the core does.
 *
 * @see TYPO3\CMS\Core\Registry
 */
class Registry implements \TYPO3\CMS\Core\SingletonInterface
{
    const DAEMON_KILL_KEY = 'daemon:kill';

    protected static $table = 'sys_registry';
    protected static $namespace = 'tx_jobqueue';

    /**
     * Set value by key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $serializedValue = serialize($value);
        $count = $this->getDatabasConnection()->exec_SELECTcountRows('uid', static::$table, $this->getWhere($key));
        if ($count < 1) {
            $this->getDatabasConnection()->exec_INSERTquery(static::$table, array(
                'entry_namespace' => static::$namespace,
                'entry_key' => $key,
                'entry_value' => $serializedValue
            ));
        } else {
            $this->getDatabasConnection()->exec_UPDATEquery(static::$table, $this->getWhere($key), array(
                'entry_value' => $serializedValue
            ));
        }
    }

    /**
     * Delete key.
     *
     * @param  string $key
     */
    public function delete($key)
    {
        $this->getDatabasConnection()->exec_DELETEquery(static::$table, $this->getWhere($key));
    }

    /**
     * Get value by key.
     *
     * @param  string $key
     * @param  mixed  $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        $row = $this->getDatabasConnection()->exec_SELECTgetSingleRow('entry_value', static::$table, $this->getWhere($key));
        if (is_array($row)) {
            return unserialize($row['entry_value']);
        }
        return $defaultValue;
    }

    /**
     * Returns where clause for statement.
     *
     * @param  string $key
     * @return string
     */
    protected function getWhere($key)
    {
        return 'entry_namespace = ' . $this->getDatabasConnection()->fullQuoteStr(static::$namespace, static::$table) . ' AND entry_key = ' . $this->getDatabasConnection()->fullQuoteStr($key, static::$table);
    }

    /**
     * @return TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabasConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
