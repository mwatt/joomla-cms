<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/stemmer/porter_en.php';

/**
 * Test class for FinderIndexerStemmerPorter_En.
 * Generated by PHPUnit on 2012-06-10 at 14:54:11.
 */
class FinderIndexerStemmerPorter_EnTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var FinderIndexerStemmerPorter_En
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new FinderIndexerStemmerPorter_En;
	}

	/**
	 * Tests the stem method of the porter_en stemmer
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStem()
	{
		$this->assertEquals(
			'parti',
			$this->object->stem('party', 'en'),
			'Tests for the proper stem of a word ending in y'
		);

		$this->assertEquals(
			'backfil',
			$this->object->stem('backfill', 'en'),
			'Tests for the proper stem of a word ending in a double consonant'
		);

		$this->assertEquals(
			'babi',
			$this->object->stem('babies', 'en'),
			'Tests for the proper stem of a pluralized word whose singular form ends in y'
		);

		$this->assertEquals(
			'passion',
			$this->object->stem('passionate', 'en'),
			'Tests for the proper stem of a word ending in ate'
		);

		$this->assertEquals(
			'irrat',
			$this->object->stem('irrational', 'en'),
			'Tests for the proper stem of a word ending in ational'
		);

		$this->assertEquals(
			'cynic',
			$this->object->stem('cynical', 'en'),
			'Tests for the proper stem of a word ending in ical'
		);

		$this->assertEquals(
			'bicycl',
			$this->object->stem('bicycling', 'en'),
			'Tests for the proper stem of a word ending in ing'
		);

		$this->assertEquals(
			'substitut',
			$this->object->stem('substitution', 'en'),
			'Tests for the proper stem of a word ending in ion'
		);
	}

	/**
	 * Tests the stem method of the porter_en language stemmer to ensure it doesn't stem short words
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStemShort()
	{
		$this->assertEquals(
			'ab',
			$this->object->stem('ab', 'en')
		);
	}

	/**
	 * Tests the stem method of the porter_en language stemmer to ensure it only stems English
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testStemWrongLanguage()
	{
		$this->assertEquals(
			'party',
			$this->object->stem('party', 'fr')
		);
	}
}
