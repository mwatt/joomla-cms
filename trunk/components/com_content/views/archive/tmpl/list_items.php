<ul id="archive-list" style="list-style: none;">
<?php foreach ($items as $item) : ?>
<li class="row<?php echo ($item->odd +1 ); ?>">
	<?php if ($params->get('title'))              : ?>    
	<h4 class="title">
		<a href="<?php echo JURI::resolve('index.php?option=com_content&amp;view=article&amp;id='.$item->id.'&amp;Itemid='.$Itemid); ?>">
			<?php echo $item->title; ?>
		</a>
	</h4>
	<?php endif; ?>
	<h5 class="metadata">
		<?php if ($params->get('date')) : ?>
		<span class="created-date">
			<?php echo JText::_('Created').': '.$item->created; ?>
		</span>
		<?php endif; ?>
		<?php if ($params->get('author')) : ?>
		<span class="author">
			<?php echo JText::_('Author').': '; echo $item->created_by_alias ? $item->created_by_alias : $item->author; ?>
		</span>
		<?php endif; ?>
	</h5>
	<div class="intro">
		<?php echo substr($item->introtext, 0, 255); ?>...
	</div>
</li>
<?php endforeach; ?>
</ul>
<div id="navigation">
	<span><?php echo $pagination->writePagesLinks($link); ?></span>
	<span><?php echo $pagination->writePagesCounter(); ?></span>
</div>
