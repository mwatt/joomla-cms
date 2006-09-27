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

/**
 * Plugin installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerPlugin extends JInstaller
{

	/**
	 * Custom install method
	 *
	 * @access	public
	 * @param	string	$p_fromdir	Directory from which to install the plugin
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function install($p_fromdir)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'plugin'))
		{
			return false;
		}

		// Get the root node of the XML document
		$root = & $this->_xmldoc->documentElement;

		/*
		 * Set the component name
		 */
		$e = & $root->getElementsByPath('name', 1);
		$this->_extensionName = $e->getText();

		/*
		 * Backward Compatability
		 * @TODO Deprecate in 2.0
		 */
		$specialTag = & $root->getAttribute('type');

		/*
		 * Set the plugin path
		 */
		$folder = & $root->getAttribute('group');
		$this->_extensionDir = JPath::clean(JPATH_SITE.DS.'plugins'.DS.$folder);

		/*
		 * If the plugin directory does not exist, lets create it
		 */
		$created = false;
		if (!file_exists($this->_extensionDir))
		{
			if (!$created = JFolder::create($this->_extensionDir))
			{
				JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('Failed to create directory').' "'.$this->extensionDir.'"');
				return false;
			}
		}

		/*
		 * If we created the plugin directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created)
		{
			$this->_stepStack[] = array('type' => 'folder', 'path' => $this->_extensionDir);
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->_parseFiles('files', $specialTag, JText::_('No file is marked as module file')) === false)
		{
			JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('Failed to copy files to').' "'.$this->_extensionDir.'"');

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}

		/*
		 * Copy extra files
		 */
		$this->_parseFiles('media');
		$this->_parseFiles('languages', null, null, true);

		/*
		 * Check to make sure a plugin by the same name is not already installed
		 */
		$query = "SELECT `id` " .
				"\nFROM `#__plugins` " .
				"\nWHERE element = '".$this->_extensionName."'";

		$db->setQuery($query);
		if (!$db->query())
		{
			JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('SQL error').': '.$db->stderr(true));
			return false;
		}

		// If value is loaded then a plugin with the same name DOES exist
		$id = $db->loadResult();

		if (!$id)
		{
			$row = & JTable::getInstance('plugin', $db);
			$row->name = $this->_extensionName;
			$row->ordering = 0;
			$row->folder = $folder;
			$row->iscore = 0;
			$row->access = 0;
			$row->client_id = 0;
			$row->element = $this->_extensionSpecial;
			$row->params = $this->_getParams();

			if ($folder == 'editors')
			{
				$row->published = 1;
			}

			if (!$row->store())
			{
				JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('SQL error').': '.$db->stderr(true));

				// Install failed, rollback any changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a plugin item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->_stepStack[] = array ('type' => 'plugin', 'id' => $row->_db->insertid());

		} else
		{
			JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('Plugin').' "'.$this->_extensionName.'" '.JText::_('already exists!'));

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Get the plugin description
		 */
		$e = & $root->getElementsByPath('description', 1);
		if (!is_null($e))
		{
			$this->description = $this->_extensionName.'<p>'.$e->getText().'</p>';
		} else
		{
			$this->description = $this->_extensionName;
		}

		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		if (!$this->_copyInstallFile(0))
		{
			JError::raiseWarning(1, 'JInstallerPlugin::install: '.JText::_('Could not copy setup file'));

			// Install failed, rollback changes
			$this->_rollback();
			return false;
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$cid	The id of the plugin to uninstall
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall($id )
	{

		/*
		 * Initialize variables
		 */
		$row = null;
		$retval = true;

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First order of business will be to load the plugin object table from the database.
		 * This should give us the necessary information to proceed.
		 */
		$row = & JTable::getInstance('plugin', $db);
		$row->load($id);

		/*
		 * Is the component we are trying to uninstall a core one?
		 * Because that is not a good idea...
		 */
		if ($row->iscore)
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerPlugin::uninstall: '.sprintf(JText::_('WARNCOREPLUGIN'), $row->name)."<br />".JText::_('WARNCOREPLUGIN2'));
			return false;
		}

		/*
		 * Get the plugin folder so we can properly build the plugin path
		 */
		if (trim($row->folder) == '')
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerPlugin::uninstall: '.JText::_('Folder field empty, cannot remove files'));
			return false;
		}

		/*
		 * Use the client id to determine which plugin path to use for the xml install file
		 */
		if (!$row->client_id)
		{
			$basepath = JPATH_SITE.DS.'plugins'.DS.$row->folder.DS;
		} else
		{
			$basepath = JPATH_ADMINISTRATOR.DS.'plugins'.DS.$row->folder.DS;
		}
		$this->_extensionDir = $basepath;
		$xmlfile = $basepath.$row->element.'.xml';
		$folder = $row->folder;

		/*
		 * Now we will no longer need the plugin object, so lets delete it
		 */
		$row->delete($row->id);
		unset ($row);

		/*
		 * Now is time to process the xml install file stuff...
		 */
		if (file_exists($xmlfile))
		{
			$this->_xmldoc = & JFactory::getXMLParser();
			$this->_xmldoc->resolveErrors(true);

			if ($this->_xmldoc->loadXML($xmlfile, false, true))
			{

				/*
				 * Let's remove the files for the plugin
				 */
				if ($this->_removeFiles('files') === false)
				{
					JError::raiseWarning(1, 'JInstallerPlugin::uninstall: '.JText::_('Unable to remove all files'));
					$retval = false;
				}

				/*
				 * Remove other files
				 */
				$this->_removeFiles('images');
				$this->_removeFiles('media');
				$this->_removeFiles('languages');
				$this->_removeFiles('administration/languages');

			} else
			{
				JError::raiseWarning(1, 'JInstallerPlugin::uninstall: '.JText::_('Could not load XML file').' '.$xmlfile);
				$retval = false;
			}
		} else
		{
			JError::raiseWarning(1, 'JInstallerPlugin::uninstall: '.JText::_('File does not exist').' '.$xmlfile);
			$retval = false;
		}

		/*
		 * If the folder is empty, let's delete it
		 */
		$files = JFolder::files($this->_extensionDir);
		if (!count($files))
		{
			JFolder::delete($this->_extensionDir);
		}

		return $retval;
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the plugin item
	 *
	 * @access	private
	 * @param	array	$arg	Installation step to rollback
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _rollback_plugin($arg)
	{

		// Get database connector object
		$db = & $this->_db;

		/*
		 * Remove the entry from the #__plugins table
		 */
		$query = "DELETE " .
				"\nFROM `#__plugins` " .
				"\nWHERE id='".$arg['id']."'";

		$db->setQuery($query);

		return ($db->query() !== false);
	}
}
?>