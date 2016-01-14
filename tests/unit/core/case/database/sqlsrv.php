<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Abstract test case class for Microsoft SQL Server database testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCaseDatabaseSqlsrv extends TestCaseDatabase
{
	/**
	 * @var    JDatabaseDriverSqlsrv  The active database driver being used for the tests.
	 * @since  12.1
	 */
	protected static $driver;

	/**
	 * @var    array  The database driver options for the connection.
	 * @since  12.1
	 */
	private static $options = array('driver' => 'sqlsrv');

	/**
	 * @var    JDatabaseDriverSqlsrv  The saved database driver to be restored after these tests.
	 * @since  12.1
	 */
	private static $stash;

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * An example DSN would be: host=localhost;port=5432;dbname=joomla_ut;user=utuser;pass=ut1234
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function setUpBeforeClass()
	{
		// First let's look to see if we have a DSN defined or in the environment variables.
		if (defined('JTEST_DATABASE_SQLSRV_DSN') || getenv('JTEST_DATABASE_SQLSRV_DSN'))
		{
			$dsn = defined('JTEST_DATABASE_SQLSRV_DSN') ? JTEST_DATABASE_SQLSRV_DSN : getenv('JTEST_DATABASE_SQLSRV_DSN');
		}
		else
		{
			return;
		}

		// First let's trim the sqlsrv: part off the front of the DSN if it exists.
		if (strpos($dsn, 'sqlsrv:') === 0)
		{
			$dsn = substr($dsn, 7);
		}

		// Split the DSN into its parts over semicolons.
		$parts = explode(';', $dsn);

		// Parse each part and populate the options array.
		foreach ($parts as $part)
		{
			list ($k, $v) = explode('=', $part, 2);

			switch ($k)
			{
				case 'host':
					self::$options['host'] = $v;
					break;
				case 'dbname':
					self::$options['database'] = $v;
					break;
				case 'user':
					self::$options['user'] = $v;
					break;
				case 'pass':
					self::$options['password'] = $v;
					break;
			}
		}

		try
		{
			// Attempt to instantiate the driver.
			self::$driver = JDatabaseDriver::getInstance(self::$options);
		}
		catch (RuntimeException $e)
		{
			self::$driver = null;
		}

		// If for some reason an exception object was returned set our database object to null.
		if (self::$driver instanceof Exception)
		{
			self::$driver = null;
		}

		// Setup the factory pointer for the driver and stash the old one.
		self::$stash = JFactory::$database;
		JFactory::$database = self::$driver;
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function tearDownAfterClass()
	{
		JFactory::$database = self::$stash;
		self::$driver = null;
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 *
	 * @since   12.1
	 */
	protected function getConnection()
	{
		// Compile the connection DSN.
		$dsn = 'sqlsrv:Server=' . self::$options['host'] . ';Database=' . self::$options['database'];

		// Create the PDO object from the DSN and options.
		$pdo = new PDO($dsn, self::$options['user'], self::$options['password']);

		return $this->createDefaultDBConnection($pdo, self::$options['database']);
	}
}
