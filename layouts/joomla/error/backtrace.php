<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$class = isset($displayData['class']) ? $displayData['class'] : 'table table-striped table-bordered';
$backtraceList = $displayData['backtrace'];
?>
<table cellpadding="0" cellspacing="0" class="Table <?php echo $class ?>">
	<tr>
		<td colspan="3" class="TD">
			<strong>Call stack</strong>
		</td>
	</tr>

	<tr>
		<td class="TD">
			<strong>#</strong>
		</td>
		<td class="TD">
			<strong>Function</strong>
		</td>
		<td class="TD">
			<strong>Location</strong>
		</td>
	</tr>

	<?php foreach ($backtraceList as $k => $backtrace) : ?>
		<tr>
			<td class="TD">
				<?php echo $k + 1; ?>
			</td>

			<?php if (isset($backtrace['class'])): ?>
				<td class="TD">
					<?php echo $backtrace['class'] . $backtrace['type'] . $backtrace['function'] . '()'; ?>
				</td>
			<?php else: ?>
				<td class="TD">
					<?php echo $backtrace['function'] . '()'; ?>
				</td>
			<?php endif; ?>

			<?php if (isset($backtrace['file'])): ?>
				<td class="TD">
					<?php
					$location   = $backtrace['file'] . ':' . $backtrace['line'];
					$linkformat = ini_get('xdebug.file_link_format');

					if ($linkformat)
					{
						$link = $linkformat;
						$link = str_replace('%f', $backtrace['file'], $link);
						$link = str_replace('%l', $backtrace['line'], $link);

						$location = '<a href="' . $link . '">' . $location . '</a>';
					}

					echo $location;
					?>
				</td>
			<?php else: ?>
				<td class="TD">
					&#160;
				</td>
			<?php endif; ?>
		</tr>
	<?php endforeach; ?>
</table>