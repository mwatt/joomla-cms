<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

/**
 * PackageInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerPackageInstaller extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function onInstallerAddInstallationTab()
	{
		echo JHtml::_('bootstrap.addTab', 'myTab', 'package', JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE', true));
		?>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
			<div class="control-group">
				<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
				<div class="controls">
					<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="button" onclick="Joomla.submitbuttonpackage()">
					<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER__UPLOAD_AND_INSTALL'); ?></button>
			</div>

			<input type="hidden" name="installtype" value="upload"/>
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');

		JFactory::getDocument()->addScriptDeclaration('
			Joomla.submitbuttonpackage = function()
			{
				var form = document.getElementById("adminForm");
		
				// do field validation 
				if (form.install_package.value == "")
				{
					alert("' . JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE') . '");
				}
				else
				{
					jQuery("#loading").css("display", "block");
					form.submit();
				}
			};
		');
	}
}
