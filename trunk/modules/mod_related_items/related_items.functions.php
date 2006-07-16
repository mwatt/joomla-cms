<?php
/**
* @version $Id: mod_related_items.php 4132 2006-06-26 12:52:06Z akede $
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
defined('_JEXEC') or die('Restricted access');

class JModRelatedItemsController extends JController
{
	var $params;

	function display()
	{
		$db						=& $this->getDBO();
		$mainframe				=& $this->getApplication();
		$user					=& $mainframe->getUser();
		$option					= JRequest::getVar( 'option', '' );
		$task					= JRequest::getUrl( 'task', '' );
		
		$moduleclass_sfx		= $this->params->get('moduleclass_sfx');
		$option					= JRequest::getVar( 'option' );
		$task					= JRequest::getVar( 'task' );
		$id						= JRequest::getVar( 'id', 0, '', 'int' );
		$showDate				= $this->params->get('showDate', 0);
		$now					= date('Y-m-d H:i:s', time());
		$nullDate				= $db->getNullDate();
		
		if ($option == 'com_content' && $task == 'view' && $id)
		{
			// select the meta keywords from the item
			$query = "SELECT metakey" .
					"\n FROM #__content" .
					"\n WHERE id = $id";
			$db->setQuery($query);
			if ($metakey = trim($db->loadResult()))
			{
				// explode the meta keys on a comma
				$keys = explode(',', $metakey);
				$likes = array ();
		
				// assemble any non-blank word(s)
				foreach ($keys as $key)
				{
					$key = trim($key);
					if ($key)
					{
						$likes[] = $db->getEscaped($key);
					}
				}
		
				if (count($likes))
				{
					// select other items based on the metakey field 'like' the keys found
					$query = "SELECT a.id, a.title, DATE_FORMAT(a.created, '%Y-%m-%d') AS created, a.sectionid, a.catid, cc.access AS cat_access, s.access AS sec_access, cc.published AS cat_state, s.published AS sec_state" .
							"\n FROM #__content AS a" .
							"\n LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id" .
							"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
							"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
							"\n WHERE a.id != $id" .
							"\n AND a.state = 1" .
							"\n AND a.access <= " .$user->get('gid') .
							"\n AND ( a.metakey LIKE '%".implode("%' OR a.metakey LIKE '%", $likes)."%' )" .
							"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
							"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
					$db->setQuery($query);
					$temp = $db->loadObjectList();
		
					$related = array ();
					if (count($temp))
					{
						foreach ($temp as $row)
						{
							if (($row->cat_state == 1 || $row->cat_state == '') && ($row->sec_state == 1 || $row->sec_state == '') && ($row->cat_access <= $user->get('gid') || $row->cat_access == '') && ($row->sec_access <= $user->get('gid') || $row->sec_access == ''))
							{
								$related[] = $row;
							}
						}
					}
					unset ($temp);
		
					if (count($related))
					{
					?>
						<ul class="relateditems<?php echo $moduleclass_sfx; ?>">
						<?php
		
						require_once (JApplicationHelper::getPath('helper', 'com_content'));
		
						foreach ($related as $item)
						{
							if ($option = "com_content" && $task = "view")
							{
								$Itemid = JContentHelper::getItemid($item->id);
							}
							$href = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=$item->id&amp;Itemid=$Itemid");
							?>
							<li>
								<a href="<?php echo $href; ?>">
									<?php if ($showDate) echo $item->created . " - "; ?>
									<?php echo $item->title; ?></a>
							</li>
							<?php
		
						}
						?>
						</ul>
						<?php
		
					}
				}
			}
		}
	}
}
?>