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

namespace FlameCore\Webwork\Database;

/**
 * Class for managing a database connection
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Connection
{
    private static $drivers = array(
        'mysql' => 'MySQL'
    );

    /**
     * Opens a new database connection.
     *
     * @param string $driver The database driver to use
     * @param string $host The database server host (Default: 'localhost')
     * @param string $user The username for authenticating at the database server (Default: 'root')
     * @param string $password The password for authenticating at the database server (Default: empty)
     * @param string $database The name of the database
     * @param string $prefix The prefix of the database tables (Default: empty)
     * @return \FlameCore\Webwork\Database\Base\Connection Returns the Connection object.
     */
    public static function create($driver, $host = 'localhost', $user = 'root', $password = '', $database, $prefix = '')
    {
        $driverClass = self::getDriverClass($driver);

        if (!class_exists($driverClass))
            throw new \DomainException(sprintf('Database driver class "%s" is not available', $driverClass));

        if (!is_string($database) || empty($database))
            throw new \InvalidArgumentException('Database name is invalid');

        return new $driverClass($host, $user, $password, $database, $prefix);
    }

    /**
     * Gets the name of the driver class.
     *
     * @param string $driver The name of the driver
     * @return string Returns the name of the driver class.
     */
    private static function getDriverClass($driver)
    {
        if (!is_string($driver) || empty($driver))
            throw new \InvalidArgumentException('Database driver name is invalid');

        $driver = strtolower($driver);

        if (!isset(self::$drivers[$driver]))
            throw new \DomainException(sprintf('Database driver "%s" is not supported', $driver));

        return sprintf('%s\%s\Connection', __NAMESPACE__, self::$drivers[$driver]);
    }
}
