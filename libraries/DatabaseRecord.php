<?php
/**
 * Webwork
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package  FlameCore\Webwork
 * @version  0.1-dev
 * @link     http://www.flamecore.org
 * @license  ISC License <http://opensource.org/licenses/ISC>
 */

namespace FlameCore\Webwork;

/**
 * This class represents a Database Record
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
abstract class DatabaseRecord
{
    /**
     * The data of the record
     *
     * @var array
     */
    protected $data = array();

    /**
     * Fetches the data of the record. This method must retrieve the data.
     *
     * @param mixed $identifier The identifier of the record
     */
    abstract public function __construct($identifier);

    /**
     * Checks wheter or not the record with given identifier exists
     *
     * @param int $identifier The identifier of the record
     * @return bool
     */
    abstract public static function exists($identifier);

    /**
     * Updates the given columns in the database table
     *
     * @param array $columns Names and values of columns to be updated (Format: [name => value, ...])
     * @return bool
     */
    abstract protected function update($columns);

    /**
     * Returns the value of a data entry
     *
     * @param string $key The key of the data entry
     * @return mixed
     */
    final protected function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    /**
     * Returns the value of a list item in a data entry
     *
     * @param string $key The key of the data entry
     * @param string $subkey The key of the list item
     * @return mixed
     */
    final protected function getListItem($key, $subkey)
    {
        return isset($this->data[$key][$subkey]) ? $this->data[$key][$subkey] : false;
    }

    /**
     * Sets the value of a data entry
     *
     * @param string $key The key of the data entry
     * @param mixed $value The new value of the data entry
     * @return bool
     */
    final protected function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this->update([$key => $this->encode($value)]);
    }

    /**
     * Sets the values of multiple data entries
     *
     * @param array $values The new values of the data entries
     * @return bool
     */
    final protected function setMultiple($values)
    {
        if (!is_array($values))
            throw new \InvalidArgumentException('The $values parameter must be an array');

        $this->data = array_merge($this->data, $values);

        return $this->update($values);
    }

    /**
     * Sets the value of a list item in a data entry
     *
     * @param string $key The key of the data entry
     * @param string $subkey The key of the list item
     * @param mixed $value The new value of the list item
     * @return bool
     */
    final protected function setListItem($key, $subkey, $value)
    {
        $this->data[$key][$subkey] = $value;

        return $this->update([$key => $this->encode($this->data[$key])]);
    }

    /**
     * Sets the values of multiple list items in a data entry
     *
     * @param string $key The key of the data entry
     * @param array $items The new values of the list items
     * @return bool
     */
    final protected function setListItems($key, $items)
    {
        if (!is_array($items))
            throw new \InvalidArgumentException('The $items parameter must be an array');

        $this->data[$key] = array_merge($this->data[$key], $items);

        return $this->update([$key => $this->encode($this->data[$key])]);
    }

    /**
     * Encodes the given value for usage in a database statement
     *
     * @param mixed $value The value
     * @return mixed
     */
    final protected function encode($value)
    {
        if (is_object($value) && $value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        } elseif (is_array($value)) {
            return json_encode($value);
        } else {
            return $value;
        }
    }

    /**
     * Decodes the value as given datatype
     *
     * @param string $value The value
     * @param string $type The datatype of the value
     * @return mixed
     */
    final protected function decode($value, $type)
    {
        if ($type == 'int') {
            return (int) $value;
        } elseif ($type == 'float') {
            return (float) $value;
        } elseif ($type == 'bool') {
            return (bool) $value;
        } elseif ($type == 'datetime') {
            return new DateTime($value);
        } elseif ($type == 'array') {
            return json_decode($value, true);
        } else {
            return $value;
        }
    }

    /**
     * Sets all data with given type mapping
     *
     * @param array $data The data from database
     * @param array $typemap The type mapping
     */
    final protected function setData(Array $data, Array $typemap)
    {
        foreach ($typemap as $key => $type) {
            $this->data[$key] = $this->decode($data[$key], $type);
        }
    }
}
