<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class for all HTML drawing classes
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTML
{
	function _( $type )
	{
		//Initialise variables
		$file = '';
		$func = $type;
		
		// Check to see if we need to load a helper file
		if(substr_count($type, '.')) 
		{
			$parts = explode('.', $type);
			$file		= preg_replace( '#[^A-Z0-9_]#i', '', $parts[0] );
			$func		= preg_replace( '#[^A-Z0-9_]#i', '', $parts[1] );
		}
		
		$className	= 'JHTML'.ucfirst($file);
		
		if (!class_exists( $className ))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JHTML::addIncludePath(), strtolower($file).'.php'))
			{
				require_once $path;

				if (!class_exists( $className ))
				{
					JError::raiseWarning( 0, 'JHTM '. $className.'::' .$func. ' not found in file.' );
					return false;
				} 
			}
			else
			{
				JError::raiseWarning( 0, 'JHTML ' . $file . ' not supported. File not found.' );
				return false;
			}
		}

		if (is_callable( array( $className, $func ) ))
		{
			$args = func_get_args();
			array_shift( $args );
			return call_user_func_array( array( $className, $func ), $args );
		}
		else
		{
			JError::raiseWarning( 0, $className.'::'.$func.' not supported.' );
			return false;
		}
	}

	/**
	 * Write a <a></a> element
	 *
	 *  @access public
	 * @param string 	The relative URL to use for the href attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @param integer	Set the SSL functionality
	 * @since 1.5
	 */

	function link($url, $text, $attribs = null, $ssl = 0)
	{
		$href = JRoute::_($url, true, $ssl);

		if (is_array($attribs)) {
			$attribs = JHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<a href="'.$href.'" '.$attribs.'>'.$text.'</a>';
	}

	/**
	 * Write a <img></amg> element
	 *
	 * @access public
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @since 1.5
	 */
	function image($url, $alt, $attribs = null)
	{
		global $mainframe;

		$src = substr( $url, 0, 4 ) != 'http' ? $mainframe->getCfg('live_site') . $url : $url;

		 if (is_array($attribs)) {
			$attribs = JHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<img src="'.$src.'" alt="'.$alt.'" '.$attribs.' />';

	}

	/**
	 * Write a <iframe></iframe> element
	 *
	 * @access public
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @param integer	Set the SSL functionality
	 * @since 1.5
	 */
	function iframe($url, $name, $attribs = null, $ssl = 0)
	{
		$src = JRoute::_($url, true, $ssl);

		 if (is_array($attribs)) {
			$attribs = JHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<iframe src="'.$src.'" '.$attribs.' />';

	}

	/**
	 * Returns formated date according to current local and adds time offset
	 *
	 * @access public
	 * @param string date in an US English date format
	 * @param string format optional format for strftime
	 * @returns formated date
	 * @see strftime
	 * @since 1.5
	 */
	function date($date, $format = DATE_FORMAT_LC, $offset = NULL)
	{
		jimport('joomla.utilities.date');

		if(is_null($offset))
		{
			$config =& JFactory::getConfig();
			$offset = $config->getValue('config.offset');
		}
		$instance = new JDate($date);
		$instance->setOffset($offset);

		return $instance->toFormat($format);
	}
	
	/**
	 * Creates a tooltip with an image as button
	 *
	 * @access public
	 * @param string 
	 * @param string
	 * @param string 
	 * @param string
	 * @param string
	 * @param boolean   
	 * @returns
	 * @since 1.5
	 */
	function tooltip($tooltip, $title='', $image='tooltip.png', $text='', $href='', $link=1)
	{
		global $mainframe;

		$tooltip	= addslashes(htmlspecialchars($tooltip));
		$title		= addslashes(htmlspecialchars($title));

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		if ( !$text ) {
			$image 	= $url . 'includes/js/ThemeOffice/'. $image;
			$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
		} else {
			$text 	= JText::_( $text, true );
		}

		if($title) {
			$title = $title.'::';
		}

		$style = 'style="text-decoration: none; color: #333;"';

		if ( $href ) {
			$href = JRoute::_( $href );
			$style = '';
		}
		if ( $link ) {
			$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'><a href="'. $href .'">'. $text .'</a></span>';
		} else {
			$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'>'. $text .'</span>';
		}

		return $tip;
	}
	
	/**
	 * Add a directory where JHTML should search for helpers. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since	1.5
	 */
	function addIncludePath( $path='' )
	{
		static $paths;
	
		if (!isset($paths)) {
			$paths = array( JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'html' );
		}
		
		if (!empty( $path ) && !in_array( $path, $paths )) {
			array_unshift($paths, JPath::clean( $path ));
		}
		return $paths;
	}

	function _implode_assoc($inner_glue = "=", $outer_glue = "\n", $array = null, $keepOuterKey = false)
	{
		$output = array();

		foreach($array as $key => $item)
		if (is_array ($item)) {
			if ($keepOuterKey)
				$output[] = $key;
			// This is value is an array, go and do it again!
			$output[] = JHTML::_implode_assoc($inner_glue, $outer_glue, $item, $keepOuterKey);
		} else
			$output[] = $key . $inner_glue . $item;

		return implode($outer_glue, $output);
	}
}
