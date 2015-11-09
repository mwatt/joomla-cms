<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @since  12.1
 */
class JCrypt
{
	/**
	 * @var    JCryptCipher  The encryption cipher object.
	 * @since  12.1
	 */
	private $_cipher;

	/**
	 * @var    JCryptKey  The encryption key[/pair)].
	 * @since  12.1
	 */
	private $_key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   JCryptCipher  $cipher  The encryption cipher object.
	 * @param   JCryptKey     $key     The encryption key[/pair)].
	 *
	 * @since   12.1
	 */
	public function __construct(JCryptCipher $cipher = null, JCryptKey $key = null)
	{
		// Set the encryption key[/pair)].
		$this->_key = $key;

		// Set the encryption cipher.
		$this->_cipher = isset($cipher) ? $cipher : new JCryptCipherSimple;
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	public function decrypt($data)
	{
		try
		{
			return $this->_cipher->decrypt($data, $this->_key);
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   12.1
	 */
	public function encrypt($data)
	{
		return $this->_cipher->encrypt($data, $this->_key);
	}

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  JCryptKey
	 *
	 * @since   12.1
	 */
	public function generateKey(array $options = array())
	{
		return $this->_cipher->generateKey($options);
	}

	/**
	 * Method to set the encryption key[/pair] object.
	 *
	 * @param   JCryptKey  $key  The key object to set.
	 *
	 * @return  JCrypt
	 *
	 * @since   12.1
	 */
	public function setKey(JCryptKey $key)
	{
		$this->_key = $key;

		return $this;
	}

	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since   12.1
	 */
	public static function genRandomBytes($length = 16)
	{
		return random_bytes($length);
	}

	/**
	 * A timing safe comparison method. This defeats hacking
	 * attempts that use timing based attack vectors.
	 *
	 * @param   string  $known    A known string to check against.
	 * @param   string  $unknown  An unknown string to check.
	 *
	 * @return  boolean  True if the two strings are exactly the same.
	 *
	 * @since   3.2
	 */
	public static function timingSafeCompare($known, $unknown)
	{
		// Prevent issues if string length is 0
		$known .= chr(0);
		$unknown .= chr(0);

		$knownLength = strlen($known);
		$unknownLength = strlen($unknown);

		// Set the result to the difference between the lengths
		$result = $knownLength - $unknownLength;

		// Note that we ALWAYS iterate over the user-supplied length to prevent leaking length info.
		for ($i = 0; $i < $unknownLength; $i++)
		{
			// Using % here is a trick to prevent notices. It's safe, since if the lengths are different, $result is already non-0
			$result |= (ord($known[$i % $knownLength]) ^ ord($unknown[$i]));
		}

		// They are only identical strings if $result is exactly 0...
		return $result === 0;
	}

	/**
	 * Tests for the availability of updated crypt().
	 * Based on a method by Anthony Ferrera
	 *
	 * @return  boolean  Always returns true since 3.3
	 *
	 * @note    To be removed when PHP 5.3.7 or higher is the minimum supported version.
	 * @see     https://github.com/ircmaxell/password_compat/blob/master/version-test.php
	 * @since   3.2
	 * @deprecated  4.0
	 */
	public static function hasStrongPasswordSupport()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated without replacement.', JLog::WARNING, 'deprecated');

		if (!defined('PASSWORD_DEFAULT'))
		{
			// Always make sure that the password hashing API has been defined.
			include_once JPATH_ROOT . '/vendor/ircmaxell/password-compat/lib/password.php';
		}

		return true;
	}
}
