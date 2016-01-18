<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php $microdata = new JMicrodata('Article'); ?>
<ul class="mostread<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) : ?>
	<li <?php echo $microdata->displayScope();?>>
		<a href="<?php echo $item->link; ?>" <?php echo $microdata->property('url')->display(); ?>>
			<span <?php echo $microdata->property('name')->display();?>>
				<?php echo $item->title; ?>
			</span>
		</a>
	</li>
<?php endforeach; ?>
</ul>
