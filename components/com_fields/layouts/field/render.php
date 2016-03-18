<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

if (! key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = $field->label;
$value = $field->value;
if (! $value)
{
	return;
}

$class = $field->render_class;
?>

<dd class="dpfield-entry <?php echo $class;?>" id="dpfield-entry-<?php echo $field->id;?>">
	<span class="dpfield-label"><?php echo htmlentities($label);?>: </span>
	<span class="dpfield-value"><?php echo $value;?></span>
</dd>
