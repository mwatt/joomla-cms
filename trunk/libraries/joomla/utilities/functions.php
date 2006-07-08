<?php
/**
 * @version $Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

define( "_MOS_NOTRIM"   , 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW" , 0x0004 );

/**
 * Utility function to return a value from a named array or a specified default
 * @package Joomla.Framework
 * @param array A named array
 * @param string The key to search for
 * @param mixed The default value to give if no key found
 * @param int An options mask: _MOS_NOTRIM prevents trim, _MOS_ALLOWHTML allows safe html, _MOS_ALLOWRAW allows raw input
 * @since 1.0
 * @tutorial Joomla.Framework/mosgetparam.proc
 */
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	static $noHtmlFilter 	= null;
	static $safeHtmlFilter 	= null;

	$return = null;
	if (isset( $arr[$name] )) {
		$return = $arr[$name];

		if (is_string( $return )) {
			// trim data
			if (!($mask&_MOS_NOTRIM)) {
				$return = trim( $return );
			}

			if ($mask&_MOS_ALLOWRAW) {
				// do nothing
			} else if ($mask&_MOS_ALLOWHTML) {
				// do nothing - compatibility mode
				/*
				if (is_null( $safeHtmlFilter )) {
					$safeHtmlFilter = new InputFilter( null, null, 1, 1 );
				}
				$arr[$name] = $safeHtmlFilter->process( $arr[$name] );
				*/
			} else {
				// send to inputfilter
				if (is_null( $noHtmlFilter )) {
					jimport( 'phpinputfilter.inputfilter' );
					$noHtmlFilter = new InputFilter( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */ );
				}
				$return = $noHtmlFilter->process( $return );
			}

			// account for magic quotes setting
			if (!get_magic_quotes_gpc()) {
				$return = addslashes( $return );
			}
		}

		return $return;
	} else {
		return $def;
	}
}

/**
 * Strip slashes from strings or arrays of strings
 *
 * @package Joomla.Framework
 * @param mixed The input string or array
 * @return mixed String or array stripped of slashes
 * @since 1.0
 */
function mosStripslashes( &$value ) {
	$ret = '';
	if (is_string( $value )) {
		$ret = stripslashes( $value );
	} else {
		if (is_array( $value )) {
			$ret = array();
			foreach ($value as $key => $val) {
				$ret[$key] = mosStripslashes( $val );
			}
		} else {
			$ret = $value;
		}
	}
	return $ret;
}

/**
* Makes a variable safe to display in forms
*
* Object parameters that are non-string, array, object or start with underscore
* will be converted
*
* @package Joomla.Framework
* @param object An object to be parsed
* @param int The optional quote style for the htmlspecialchars function
* @param string|array An optional single field name or array of field names not
*					 to be parsed (eg, for a textarea)
* @since 1.0
*/
function mosMakeHtmlSafe( &$mixed, $quote_style=ENT_QUOTES, $exclude_keys='' ) {
	if (is_object( $mixed )) {
		foreach (get_object_vars( $mixed ) as $k => $v) {
			if (is_array( $v ) || is_object( $v ) || $v == NULL || substr( $k, 1, 1 ) == '_' ) {
				continue;
			}
			if (is_string( $exclude_keys ) && $k == $exclude_keys) {
				continue;
			} else if (is_array( $exclude_keys ) && in_array( $k, $exclude_keys )) {
				continue;
			}
			$mixed->$k = htmlspecialchars( $v, $quote_style );
		}
	}
}

/**
* Replaces &amp; with & for xhtml compliance
*
* Needed to handle unicode conflicts due to unicode conflicts
*
* @package Joomla.Framework
* @since 1.0
*/
function ampReplace( $text ) {
	$text = str_replace( '&&', '*--*', $text );
	$text = str_replace( '&#', '*-*', $text );
	$text = str_replace( '&amp;', '&', $text );
	$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
	$text = str_replace( '*-*', '&#', $text );
	$text = str_replace( '*--*', '&&', $text );

	return $text;
}

/**
* Copy the named array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
*
* @package Joomla.Framework
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
* @since 1.0
*/
function mosBindArrayToObject( $array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=true ) {
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	foreach (get_object_vars($obj) as $k => $v) {
		if( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
			if (strpos( $ignore, $k) === false) {
				if ($prefix) {
					$ak = $prefix . $k;
				} else {
					$ak = $k;
				}
				if (isset($array[$ak])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$ak];
				}
			}
		}
	}

	return true;
}

function mosObjectToArray($p_obj) {
	$retarray = null;
	if(is_object($p_obj))
	{
		$retarray = array();
		foreach (get_object_vars($p_obj) as $k => $v)
		{
			if(is_object($v))
			$retarray[$k] = mosObjectToArray($v);
			else
			$retarray[$k] = $v;
		}
	}
	return $retarray;
}

/**
 * Utility function to map an array to a stdClass object.
 *
 * @package	Joomla.Framework
 * @param	array	$p_array	The array to map.
 * @return	object	The object mapped from the given array.
 * @since	1.5
 */
function josArrayToObject($p_array) {
	$ret_obj = null;
	if(is_array($p_array)) {
		$ret_obj = new stdClass();
		foreach ($p_array as $k => $v) {
			if(is_array($v))
			$ret_obj->$k = josArrayToObject($v);
			else
			$ret_obj->$k = $v;
		}
	}
	return $ret_obj;
}

/**
 * Utility function redirect the browser location to another url
 *
 * @package Joomla.Framework
 * Can optionally provide a message.
 * @param string $url The URL to redirect to
 * @param string $msg A message to display on redirect
 * @since 1.0
 */
function josRedirect( $url, $msg='' )
{
   global $mainframe;

    /*
     * Instantiate an input filter and process the URL and message
     */
	jimport( 'phpinputfilter.inputfilter' );
	$iFilter = new InputFilter();
	$url = $iFilter->process( $url );
	if (!empty($msg)) {
		$msg = $iFilter->process( $msg );
	}

	if ($iFilter->badAttributeValue( array( 'href', $url ))) {
		$url = $mainframe->getBasePath();
	}

	/*
	 * If the message exists, prepare it (url encoding)
	 */
	if (trim( $msg )) {
	 	if (strpos( $url, '?' )) {
			$url .= '&josmsg=' . urlencode( $msg );
		} else {
			$url .= '?josmsg=' . urlencode( $msg );
		}
	}

	/*
	 * If the headers have been sent, then we cannot send an additional location header
	 * so we will output a javascript redirect statement.
	 */
	if (headers_sent()) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		//@ob_end_clean(); // clear output buffer
		header( 'HTTP/1.5 301 Moved Permanently' );
		header( "Location: ". $url );
	}
	exit();
}

function josErrorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
	$text = nl2br( $text );
	$text = addslashes( $text );
	$text = strip_tags( $text );

	switch ( $mode ) {
		case 2:
			echo "<script>$action</script> \n";
			break;

		case 1:
		default:
			echo "<script>alert('$text'); $action</script> \n";
			echo '<noscript>';
			echo "$text\n";
			echo '</noscript>';
			break;
	}

	exit;
}

/**
 * Format a backtrace error
 *
 * @package Joomla.Framework
 * @param string An optional message
 * @since 1.5
 */
function mosBackTrace( $message='' ) {
	if (function_exists( 'debug_backtrace' )) {
		echo '<div align="left">';
		if ($message) {
			echo '<p><strong>' . $message . '</strong></p>';
		}
		foreach( debug_backtrace() as $back) {
			if (@$back['file']) {
				echo '<br />' . str_replace( JPATH_ROOT, '', $back['file'] ) . ':' . $back['line'];
			}
		}
		echo '</div>';
	}
}

/**
* Displays a not authorised message
*
* If the user is not logged in then an addition message is displayed.
*
* @package Joomla.Framework
* @since 1.0
*/
function mosNotAuth() {
	global $mainframe;

	$user =& $mainframe->getUser();
	echo JText::_('ALERTNOTAUTH');
	if ($user->get('id') < 1) {
		echo "<br />" . JText::_( 'You need to login.' );
	}
}

function mosTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 ) {
	if (@$children[$id] && $level <= $maxlevel) {
		foreach ($children[$id] as $v) {
			$id = $v->id;

			if ( $type ) {
				$pre 	= '<sup>L</sup>&nbsp;';
				$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} else {
				$pre 	= '- ';
				$spacer = '&nbsp;&nbsp;';
			}

			if ( $v->parent == 0 ) {
				$txt 	= $v->name;
			} else {
				$txt 	= $pre . $v->name;
			}
			$pt = $v->parent;
			$list[$id] = $v;
			$list[$id]->treename = "$indent$txt";
			$list[$id]->children = count( @$children[$id] );
			$list = mosTreeRecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
		}
	}
	return $list;
}

/**
 * @package Joomla.Framework
 * @param string SQL with ordering As value and 'name field' AS text
 * @param integer The length of the truncated headline
 * @since 1.0
 */
function mosGetOrderingList( $sql, $chop='30' ) {

	$db =& JFactory::getDBO();
	$order = array();
	$db->setQuery( $sql );
	if (!($orders = $db->loadObjectList())) {
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		} else {
			$order[] = mosHTML::makeOption( 1, JText::_( 'first' ) );
			return $order;
		}
	}
	$order[] = mosHTML::makeOption( 0, '0 '. JText::_( 'first' ) );
	for ($i=0, $n=count( $orders ); $i < $n; $i++) {

		if (JString::strlen($orders[$i]->text) > $chop) {
			$text = JString::substr($orders[$i]->text,0,$chop)."...";
		} else {
			$text = $orders[$i]->text;
		}

		$order[] = mosHTML::makeOption( $orders[$i]->value, $orders[$i]->value.' ('.$text.')' );
	}
	$order[] = mosHTML::makeOption( $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' '. JText::_( 'last' ) );

	return $order;
}

/**
* Returns formated date according to current local and adds time offset
*
* @package Joomla.Framework
* @param string date in datetime format
* @param string format optional format for strftime
* @param offset time offset if different than global one
* @returns formated date
* @since 1.0
*/
function mosFormatDate( $date, $format="", $offset="" )
{
	global $mainframe;

	$lang = $mainframe->getLanguage();
	if ( $format == '' ) {
		// %Y-%m-%d %H:%M:%S
		$format = JText::_( 'DATE_FORMAT_LC' );
	}
	if ( $offset == '' ) {
		$offset = $mainframe->getCfg('offset');
	}
	if ( $date && ereg( "([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})", $date, $regs ) ) {
		$date = mktime( $regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1] );
		$date = $date > -1 ? strftime( $format, $date + ($offset*60*60) ) : '-';
	}

	// for Windows there is a need to convert the date string to utf-8.
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && function_exists('iconv')) {
		return iconv($lang->getWinCP(), "UTF-8", $date);
	}

	return $date;
}

/**
* Returns current date according to current local and time offset
*
* @package Joomla.Framework
* @param string format optional format for strftime
* @returns current date
* @since 1.0
*/
function mosCurrentDate( $format="" )
{
	global $mainframe;

	if ($format=="") {
		$format = JText::_( 'DATE_FORMAT_LC' );
	}
	$date = strftime( $format, time() + ($mainframe->getCfg('offset')*60*60) );
	return $date;
}

/**
* Utility function to provide ToolTips
*
* @package Joomla.Framework
* @param string ToolTip text
* @param string Box title
* @returns HTML code for ToolTip
* @since 1.0
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='', $link=1 )
{
	global $mainframe;

	$tooltip = addslashes(htmlspecialchars($tooltip));
	$title   = addslashes(htmlspecialchars($title));

	$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

	if ( $width ) {
		$width = ', WIDTH, \''.$width .'\'';
	}

	if ( $title ) {
		$title = ', CAPTION, \''. JText::_( $title ) .'\'';
	}

	if ( !$text ) {
		$image 	= $url . 'includes/js/ThemeOffice/'. $image;
		$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
	} else {
		$text 	= JText::_( $text, true );
    }

	$style = 'style="text-decoration: none; color: #333;"';

	if ( $href ) {
		$href = ampReplace( $href );
		$style = '';
	}

	$mousover = 'return overlib(\''. JText::_( $tooltip, true ) .'\''. $title .', BELOW, RIGHT'. $width .');';

	$tip = '<!--'. JText::_( 'Tooltip' ) .'--> \n';
	if ( $link ) {
		$tip = '<a href="'. $href .'" onmouseover="'. $mousover .'" onmouseout="return nd();" '. $style .'>'. $text .'</a>';
	} else {
		$tip = '<span onmouseover="'. $mousover .'" onmouseout="return nd();" '. $style .'>'. $text .'</span>';
	}

	return $tip;
}

function mosCreateGUID(){
	srand((double)microtime()*1000000);
	$r = rand() ;
	$u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
	$m = md5 ($u);
	return($m);
}

function mosCompressID( $ID ){
	return(Base64_encode(pack("H*",$ID)));
}

function mosExpandID( $ID ) {
	return ( implode(unpack("H*",Base64_decode($ID)), '') );
}

/**
 * Provides a secure hash based on a seed
 *
 * @package Joomla.Framework
 * @param string Seed string
 * @return string
 * @since 1.0
 */
function mosHash( $seed ) {
	return md5( $GLOBALS['mosConfig_secret'] . md5( $seed ) );
}

/**
 * Mail function (uses phpMailer)
 *
 * @package Joomla.Framework
 * @param string $from From e-mail address
 * @param string $fromname From name
 * @param mixed $recipient Recipient e-mail address(es)
 * @param string $subject E-mail subject
 * @param string $body Message body
 * @param boolean $mode false = plain text, true = HTML
 * @param mixed $cc CC e-mail address(es)
 * @param mixed $bcc BCC e-mail address(es)
 * @param mixed $attachment Attachment file name(s)
 * @param mixed $replyto Reply to email address(es)
 * @param mixed $replytoname Reply to name(s)
 * @return boolean True on success
 * @since 1.5
 */
function josMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null ) {
	global $mainframe;

	jimport('joomla.utilities.mail');

	/*
	 * Get a JMail instance
	 */
	$mail =& JMail::getInstance();

	$mail->setSender(array($from, $fromname));
	$mail->setSubject($subject);
	$mail->setBody($body);

	/*
	 * Are we sending the email as HTML?
	 */
	if ( $mode ) {
		$mail->IsHTML(true);
	}

	$mail->addRecipient($recipient);
	$mail->addCC($cc);
	$mail->addBCC($bcc);
	$mail->addAttachment($attachment);

	/*
	 * Take care of reply email addresses
	 */
	$numReplyTo = count($replyto);
	for ($i=0;$i < $numReplyTo; $i++) {
		$mail->addReplyTo(array($replyto[$i], $replytoname[$i]));
	}

	/*
	 * Send the email
	 */
	$sent = $mail->Send();

	/*
	 * Set debug information
	 * TODO: Common debug template perhaps?
	 */
	if( $mainframe->getCfg( 'debug' ) ) {
		//$mosDebug->message( "Mails send: $mailssend");
	}
	if( $mail->error_count > 0 ) {
		//$mosDebug->message( "The mail message $fromname <$from> about $subject to $recipient <b>failed</b><br /><pre>$body</pre>", false );
		//$mosDebug->message( "Mailer Error: " . $mail->ErrorInfo . "" );
	}

	return $sent;
}

/**
 * Sends mail to administrator for approval of a user submission
 *
 * @package Joomla.Framework
 * @param string $adminName Name of administrator
 * @param string $adminEmail Email address of administrator
 * @param string $email [NOT USED TODO: Deprecate?]
 * @param string $type Type of item to approve
 * @param string $title Title of item to approve
 * @param string $author Author of item to approve
 * @return boolean True on success
 * @since 1.5
 */
function josSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author, $url = null ) {
	global $mainframe;

	if(!isset($url)) {
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();
	}

    $strAdminDir = 'administrator';

	$subject = JText::_( 'User Submitted' ) ." '". $type ."'";

	$message = sprintf ( JText::_( 'MAIL_MSG_ADMIN' ), $adminName, $type, $title, $author, $url, $url, $strAdminDir, $type);
    $message .= JText::_( 'MAIL_MSG') ."\n";

	eval ("\$message = \"$message\";");

	return josMail($mainframe->getCfg( 'mailfrom' ), $mainframe->getCfg( 'fromname' ), $adminEmail, $subject, $message);
}

/**
 * Method to process internal Joomla URLs
 *
 * @package Joomla.Framework
 * @param string $url Absolute or Relative URL to Joomla resource
 * @param int $ssl Secure state for the processed URL
 *    1: Make URL secure using global secure site URL
 *    0: Leave URL in the same secure state as it was passed to the function
 *   -1: Make URL unsecure using the global unsecure site URL
 * @param int $sef Search engine friendly state for the processed URL
 *    1: Make URL search engine friendly
 *    0: Leave URL in the same sef state as it was passed to the function
 * @since 1.5
 */
function josURL( $url, $ssl=0, $sef=1 ) {
	global $mainframe;

	/*
	 * Get the base request URL from the JApplication object
	 */
	$RURL = $mainframe->getBaseURL();

	/*
	 * First we need to get the secure/unsecure URLs.  To do this we get the
	 * request URL from the JApplication and do a quick test.  If the first 5
	 * characters of the RURL are 'https', then we are on an ssl connection over
	 * https and need to set our secure URL to the current request URL, if not,
	 * and the scheme is 'http', then we need to do a quick string manipulation
	 * to switch schemes.
	 */
	if ( substr( $RURL, 0, 5 ) == 'https' )
	{
		$secure 	= $RURL;
		$unsecure	= 'http'.substr( $RURL, 5 );
	} elseif ( substr( $RURL, 0, 4 ) == 'http' )
	{
		$secure		= 'https'.substr( $RURL, 4 );
		$unsecure	= $RURL;
	}

	/*
	 * If we want to SEF the url, and the SEF function exists... lets pass the
	 * url through it.
	 */
	if ( ( $sef == 1 ) && ( function_exists('sefRelToAbs' ) ) ) {
		$url = sefRelToAbs( $url );
	}

	/*
	 * Were we fed a relative URL?
	 */
	if ( substr( $url,0,4 ) != 'http' ) {
		$url = $RURL . $url;
	}

	/*
	 * Ensure that proper secure site url is used if ssl flag set and url
	 * doesn't already include it
	 */
	if ($ssl == 1 && strstr($url, $unsecure)) {
		$url = str_replace( $unsecure, $secure , $url );
	}

	/*
	 * Ok, now if the SSL flag is set to always unsecure, and we are in SSL
	 * mode, lets change the link to use the unsecure URL
	 */
	if ($ssl == -1 && strstr($url, $secure)) {
		$url = str_replace( $secure, $unsecure , $url );
	}

	return $url;
}

/**
 * Method to extract key/value pairs out of a string with xml style attributes
 *
 * @package	Joomla.Framework
 * @param	string	$string	String containing xml style attributes
 * @return	array	Key/Value pairs for the attributes
 * @since	1.5
 */
function josParseAttributes( $string ) {

	/*
	 * Initialize variables
	 */
	$attr		= array();
	$retarray	= array();
	/*
	 * Lets grab all the key/value pairs using a regular expression
	 */
	preg_match_all( '/([\w]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr );

	if (is_array($attr))
	{
		$numPairs = count($attr[1]);
		for($i = 0; $i < $numPairs; $i++ )
		{
			$retarray[$attr[1][$i]] = $attr[2][$i];
		}
	}
	return $retarray;
}

/**
* Prepares results from search for display
*
* @package Joomla.Framework
* @param string The source string
* @param int Number of chars to trim
* @param string The searchword to select around
* @return string
* @since 1.5
*/
function mosPrepareSearchContent( $text, $length=200, $searchword ) {
	// strips tags won't remove the actual jscript
	$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
	$text = preg_replace( '/{.+?}/', '', $text);
	//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text );
	// replace line breaking tags with whitespace
	$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );

	return mosSmartSubstr( strip_tags( $text ), $length, $searchword );
}

/**
* returns substring of characters around a searchword
*
* @package Joomla.Framework
* @param string The source string
* @param int Number of chars to return
* @param string The searchword to select around
* @return string
* @since 1.0
*/
function mosSmartSubstr($text, $length=200, $searchword) {
  $wordpos = JString::strpos(JString::strtolower($text), JString::strtolower($searchword));
  $halfside = intval($wordpos - $length/2 - JString::strlen($searchword));
  if ($wordpos && $halfside > 0) {
	return '...' . JString::substr($text, $halfside, $length) . '...';
  } else {
	return JString::substr( $text, 0, $length);
  }
}

/**
 * Function to convert array to integer values
 *
 * @package Joomla.Framework
 * @param array
 * @param int A default value to assign if $array is not an array
 * @return array
 * @since 1.0
 */
function mosArrayToInts( &$array, $default=null ) {
	if (is_array( $array )) {
		$n = count( $array );
		for ($i = 0; $i < $n; $i++) {
			$array[$i] = intval( $array[$i] );
		}
	} else {
		if (is_null( $default )) {
			return array();
		} else {
			return array( $default );
		}
	}
}

/**
* Sorts an Array of objects
*
* @package Joomla.Framework
* @since 1.0
*/
function SortArrayObjects_cmp( &$a, &$b ) {
	global $csort_cmp;

	if ( $a->$csort_cmp['key'] > $b->$csort_cmp['key'] ) {
		return $csort_cmp['direction'];
	}

	if ( $a->$csort_cmp['key'] < $b->$csort_cmp['key'] ) {
		return -1 * $csort_cmp['direction'];
	}

	return 0;
}

/**
* Sorts an Array of objects
*
* @package Joomla.Framework
* @param integer 	$sort_direction [1 = Ascending] [-1 = Descending]
* @since 1.0
*/
function SortArrayObjects( &$a, $k, $sort_direction=1 ) {
	global $csort_cmp;

	$csort_cmp = array(
		'key'		  => $k,
		'direction'	=> $sort_direction
	);

	usort( $a, 'SortArrayObjects_cmp' );

	unset( $csort_cmp );
}

?>
