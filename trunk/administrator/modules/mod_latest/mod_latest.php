<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Get the user object for the logged in user
$user	=& JFactory::getUser();
$db		=& JFactory::getDBO();

$query = "SELECT a.id, a.sectionid, a.title, a.created, u.name, a.created_by_alias, a.created_by"
. "\n FROM #__content AS a"
. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
. "\n WHERE a.state <> -2"
. "\n ORDER BY created DESC"
;
$db->setQuery( $query, 0, 10 );
$rows = $db->loadObjectList();
?>

<table class="adminlist">
<?php
foreach ($rows as $row) {
	if ( $row->sectionid == 0 ) {
		$link = 'index2.php?option=com_typedcontent&amp;task=edit&amp;hidemainmenu=1&amp;id='. $row->id;
	} else {
		$link = 'index2.php?option=com_content&amp;task=edit&amp;hidemainmenu=1&amp;id='. $row->id;
	}

	if ( $user->authorize( 'administration', 'manage', 'components', 'com_users' ) ) {
		if ( $row->created_by_alias ) {
			$author = $row->created_by_alias;
		} else {
			$linkA 	= 'index2.php?option=com_users&task=editA&amp;hidemainmenu=1&id='. $row->created_by;
			$author = '<a href="'. $linkA .'" title="'. JText::_( 'Edit User' ) .'">'. htmlspecialchars( $row->name, ENT_QUOTES ) .'</a>';
		}
	} else {
		if ( $row->created_by_alias ) {
			$author = $row->created_by_alias;
		} else {
			$author = htmlspecialchars( $row->name, ENT_QUOTES );
		}
	}
	?>
	<tr>
		<td>
		<a href="<?php echo $link; ?>">
		<?php echo htmlspecialchars($row->title, ENT_QUOTES);?>
		</a>
		</td>
		<td>
		<?php echo $row->created;?>
		</td>
		<td>
		<?php echo $author;?>
		</td>
	</tr>
	<?php
}
?>
</table>