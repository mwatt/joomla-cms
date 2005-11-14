<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $mosConfig_list_limit;

require_once( JPATH_ADMINISTRATOR .'/includes/pageNavigation.php' );

$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}", 'limitstart', 0 );

// hides Administrator or Super Administrator from list depending on usertype
$and = '';
// administrator check
if ( $my->gid == 24 ) {
	$and = "\n AND userid != '25'";
}
// manager check
if ( $my->gid == 23 ) {
	$and = "\n AND userid != '25'";
	$and .= "\n AND userid != '24'";
}

// get the total number of records
$query = "SELECT COUNT(*)"
. "\n FROM #__session"
. "\n WHERE userid != 0"
. $and
. "\n ORDER BY usertype, username"
;
$database->setQuery( $query );
$total = $database->loadResult();

// page navigation
$pageNav = new mosPageNav( $total, $limitstart, $limit );

$query = "SELECT *"
. "\n FROM #__session"
. "\n WHERE userid != 0"
. $and
. "\n ORDER BY usertype, username"
. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
;
$database->setQuery( $query );
$rows = $database->loadObjectList();
?>
<table class="adminlist">
<tr>
	<th colspan="4">
	<?php echo JText::_( 'Currently Logged in Users' ); ?>
	</th>
</tr>
<?php
$i = 0;
foreach ( $rows as $row ) {
	if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
		$link 	= 'index2.php?option=com_users&task=editA&hidemainmenu=1&id='. $row->userid;
		$name 	= '<a href="'. $link .'" title="'. JText::_( 'Edit User' ) .'">'. $row->username .'</a>';
	} else {
		$name 	= $row->username;
	}
	?>
	<tr>
		<td width="5%">
		<?php echo $pageNav->rowNumber( $i ); ?>
		</td>
		<td>
		<?php echo $name;?>
		</td>
		<td>
		<?php echo $row->usertype;?>
		</td>
		<?php
		if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
			?>
			<td>
			<a href="index2.php?option=com_users&task=flogout&id=<?php echo $row->userid; ?>">
			<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Logout' ); ?>" Title="<?php echo JText::_( 'Force Logout User' ); ?>" />
			</a>
			</td>
			<?php
		}
		?>
	</tr>
	<?php
	$i++;
}
?>
</table>
<?php echo $pageNav->getListFooter(); ?>
<input type="hidden" name="option" value="" />