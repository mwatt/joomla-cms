<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package		Joomla
 * @subpackage	Search
 */
class SearchHelper
{
	function santiseSearchWord(&$searchword, $searchphrase)
	{
		$ignored = false;

		$lang =& JFactory::getLanguage();

		$search_ignore	= array();
		$tag			= $lang->getTag();
		$ignoreFile		= $lang->getLanguagePath().DS.$tag.DS.$tag.'.ignore.php';
		if (file_exists($ignoreFile)) {
			include $ignoreFile;
		}

	 	// check for words to ignore
		$aterms = explode( ' ', JString::strtolower( $searchword ) );

		// first case is single ignored word
		if ( count( $aterms ) == 1 && in_array( JString::strtolower( $searchword ), $search_ignore ) ) {
			$ignored = true;
		}

		// next is to remove ignored words from type 'all' searches with multiple words
		if ( count( $aterms ) > 1 && $searchphrase == 'any' ) {
			$pruned = array_diff( $aterms, $search_ignore );
			$searchword = implode( ' ', $pruned );
		}

		return $ignored;
	}

	function limitSearchWord(&$searchword)
	{
		$restriction = false;

		// limit searchword to 20 characters
		if ( JString::strlen( $searchword ) > 20 ) {
			$searchword 	= JString::substr( $searchword, 0, 19 );
			$restriction 	= true;
		}

		// searchword must contain a minimum of 3 characters
		if ( $searchword && JString::strlen( $searchword ) < 3 ) {
			$searchword 	= '';
			$restriction 	= true;
		}

		return $restriction;
	}

	function logSearch( $search_term )
	{
		global $mainframe;

		$db =& JFactory::getDBO();

		$params = &JComponentHelper::getParams( 'com_search' );
		$enable_log_searches = $params->get('enabled');

		$search_term = $db->getEscaped( trim( $search_term) );

		if ( @$enable_log_searches )
		{
			$db =& JFactory::getDBO();
			$query = 'SELECT hits'
			. ' FROM #__core_log_searches'
			. ' WHERE LOWER( search_term ) = "'.$search_term.'"'
			;
			$db->setQuery( $query );
			$hits = intval( $db->loadResult() );
			if ( $hits ) {
				$query = 'UPDATE #__core_log_searches'
				. ' SET hits = ( hits + 1 )'
				. ' WHERE LOWER( search_term ) = "'.$search_term.'"'
				;
				$db->setQuery( $query );
				$db->query();
			} else {
				$query = 'INSERT INTO #__core_log_searches VALUES ( "'.$search_term.'", 1 )';
				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	/**
	 * Prepares results from search for display
	 *
	 * @param string The source string
	 * @param int Number of chars to trim
	 * @param string The searchword to select around
	 * @return string
	 */
	function prepareSearchContent( $text, $length = 200, $searchword )
	{
		// strips tags won't remove the actual jscript
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
		$text = preg_replace( '/{.+?}/', '', $text);
		//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text );
		// replace line breaking tags with whitespace
		$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );

		return SearchHelper::_smartSubstr( strip_tags( $text ), $length, $searchword );
	}

	/**
	 * returns substring of characters around a searchword
	 *
	 * @param string The source string
	 * @param int Number of chars to return
	 * @param string The searchword to select around
	 * @return string
	 */
	function _smartSubstr($text, $length = 200, $searchword)
	{
  		$wordpos = JString::strpos(JString::strtolower($text), JString::strtolower($searchword));
  		$halfside = intval($wordpos - $length/2 - JString::strlen($searchword));
  		if ($wordpos && $halfside > 0) {
			return '...' . JString::substr($text, $halfside, $length) . '...';
  		} else {
			return JString::substr( $text, 0, $length);
  		}
	}
}
?>
