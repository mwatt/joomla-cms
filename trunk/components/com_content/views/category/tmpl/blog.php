<?php if ($this->params->get('page_title')) : ?>
<div class="componentheading<?php echo $this->params->get('pageclass_sfx');?>">
	<?php echo $this->params->get('header'); ?>
</div>
<?php endif; ?>
<table class="blog<?php echo $this->params->get('pageclass_sfx');?>" cellpadding="0" cellspacing="0">
<?php if ($this->params->def('description', 1) || $this->params->def('description_image', 1)) :?>
<tr>
	<td valign="top">
	<?php if ($this->params->get('description_image') && $this->category->image) : ?>
		<img src="images/stories/<?php echo $this->category->image;?>" align="<?php echo $this->category->image_position;?>" hspace="6" alt="" />
	<?php endif; ?>
	<?php if ($this->params->get('description') && $this->category->description) : ?>
		<?php echo $this->category->description; ?>
	<?php endif; ?>
		<br/>
		<br/>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('leading', 1)) : ?>
<tr>
	<td valign="top">
	<?php for ($i = 0; $i < $this->params->get('leading'); $i ++) : ?>
		<?php if ($i >= $this->category->total) : break; endif; ?>
		<div>
		<?php $this->item($i); ?>
		</div>
	<?php endfor; ?>
	</td>
</tr>
<?php else : $i = 0; endif; ?>
<?php if ($this->params->def('intro', 4) && ($i < $this->category->total)) : ?>
<tr>
	<td valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
		<?php
			$divider = '';
			for ($z = 0; $z < $this->params->def('columns', 2); $z ++) :
				if ($z > 0) : $divider = " column_seperator"; endif; ?>
				<td valign="top" width="<?php echo intval(100 / $this->params->get('columns')) ?>%" class="article_column <?php echo $divider;?>">
				<?php for ($y = 0; $y < $this->params->get('intro') / $this->params->get('columns'); $y ++) :
					if ($i <= $this->params->get('intro') && ($i < $this->category->total)) :
						$this->item($i);
						$i ++;
					endif;
				endfor; ?>
				</td>
		<?php endfor; ?>
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('link', 4) && ($i < $this->category->total)) : ?>
<tr>
	<td valign="top">
		<div class="blog_more<?php echo $this->params->get('pageclass_sfx');?>">
			<?php $this->links($i);?>
		</div>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('pagination', 2)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesLinks($this->data->link); ?>
		<br /><br />
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->def('pagination_results', 1)) : ?>
<tr>
	<td valign="top" align="center">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>
<?php endif; ?>