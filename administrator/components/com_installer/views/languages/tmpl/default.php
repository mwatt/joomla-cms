<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<div id="installer-languages" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=languages');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
			<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
			<div class="clearfix"></div>
			<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LANGUAGECODE', 'element', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="center">
							<?php echo JText::_('JVERSION'); ?>
						</th>
						<th width="5%" class="center nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_TYPE'); ?>
						</th>
						<th width="40%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'update_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php $version = new JVersion; ?>
				<?php foreach ($this->items as $i => $language) : ?>
					<?php
					// Get language code and language image.
					preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $language->element, $element);
					$language->code  = $element[1];
					$language->image = strtolower(str_replace('-', '_', $language->code));
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $language->update_id, false, 'cid'); ?>
						</td>
						<td>
							<label for="cb<?php echo $i; ?>">
								<?php echo JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->name, array('title' => $language->name), true) . '&nbsp;' . $language->name; ?>
							</label>
						</td>
						<td class="center small hidden-phone">
							<?php echo $language->code; ?>
						</td>
						<td class="center small">
								<?php // Display a Note if language pack version is not equal to Joomla version ?>
								<?php if (substr($language->version, 0, 3) != $version::RELEASE || substr($language->version, 0, 5) != $version->getShortVersion()) : ?>
									<span class="label label-warning hasTooltip" title="<?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $language->version; ?></span>
								<?php else : ?>
									<span class="label label-success"><?php echo $language->version; ?></span>
								<?php endif; ?>
						</td>
						<td class="center small hidden-phone">
							<?php echo JText::_('COM_INSTALLER_TYPE_' . strtoupper($language->type)); ?>
						</td>
						<td class="small hidden-phone">
							<a href="<?php echo $language->detailsurl; ?>" target="_blank"><?php echo $language->detailsurl; ?></a>
						</td>
						<td class="small hidden-phone">
							<?php echo $language->update_id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
