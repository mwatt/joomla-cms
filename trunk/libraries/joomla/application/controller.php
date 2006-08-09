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
 * Base class for a Joomla Controller
 *
 * Acts as a Factory class for application specific objects and provides many
 * supporting API functions.
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @author		Andrew Eddie
 * @since		1.5
 */
class JController extends JObject
{
	/**
	 * The Main Application [JApplication]
	 * @var	object
	  */
	var $_app = null;

	/**
	 * Array of class methods
	 * @var	array
	 */
	var $_methods 	= null;

	/**
	 * Array of class methods to call for a given task
	 * @var	array
	 */
	var $_taskMap 	= null;

	/**
	 * Current task name
	 * @var	string
	 */
	var $_task 		= null;

	/**
	 * URL for redirection
	 * @var	string
	 */
	var $_redirect 	= null;

	/**
	 * Redirect message
	 * @var	string
	 */
	var $_message 	= null;

	/**
	 * ACO Section for the controller
	 * @var	string
	 */
	var $_acoSection 		= null;

	/**
	 * Default ACO Section value for the controller
	 * @var	string
	 */
	var $_acoSectionValue 	= null;

	/**
	 * View object
	 * @var	object
	 */
	var $_view = null;

	/**
	 * View file base path
	 * @var	string
	 */
	var $_viewPath = null;

	/**
	 * Name of the current view
	 * @var	string
	 */
	var $_viewName = null;

	/**
	 * Request option
	 * @var	string
	 */
	var $_viewOption = null;

	/**
	 * View name prefix
	 * @var	string
	 */
	var $_viewClassPrefix = null;

	/**
	 * Model file base path
	 * @var	string
	 */
	var $_modelPath = null;

	/**
	 * Internal data to pass to the controller
	 * @var $array
	 */
	var $_data;

	/**
	 * An error message
	 * @var string
	 */
	var $_error;

	/**
	 * Constructor for PHP 4.x compatibility
	 */
	function JController( &$application, $default = '' )
	{
		$this->__construct( $application, $default );
	}

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$app	The main application
	 * @param	string	$default	The default task [optional]
	 * @since	1.5
	 */
	function __construct( &$application, $default='' )
	{
		/*
		 * Initialize private variables
		 */
		$this->_redirect	= null;
		$this->_message		= null;
		$this->_taskMap		= array();
		$this->_methods		= array();
		$this->_data		= array();
		$this->_app			= &$application;

		// Get the methods only for the final controller class
		$thisMethods	= get_class_methods( get_class( $this ) );
		$baseMethods	= get_class_methods( 'JController' );
		$methods		= array_diff( $thisMethods, $baseMethods );

		// Add default display method
		$methods[] = 'display';

		// Iterate through methods and map tasks
		foreach ($methods as $method)
		{
			if (substr( $method, 0, 1 ) != '_')
			{
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}
		// If the default task is set, register it as such
		if ($default)
		{
			$this->registerDefaultTask( $default );
		}
	}

	/**
	 * String representation
	 * @return string
	 */
	function __toString()
	{
		$result = get_class( $this );
		return $result;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string	$modelName	The name of the view
	 * @return	mixed	Model object or boolean false if failed
	 * @since	1.5
	 */
	function &_loadModel( $modelName, $prefix )
	{

		$false = false;

		// Clean the model name
		$modelName = preg_replace( '#\W#', '', $modelName );
		$prefix = preg_replace( '#\W#', '', $prefix );

		// Build the model class name
		$modelClass = $prefix.$modelName;

		if (!class_exists( $modelClass ))
		{
			// Build the path to the model based upon a supplied base path
			$path = $this->getModelPath().strtolower($modelName).'.php';

			// If the model file exists include it and try to instantiate the object
			if (file_exists( $path ))
			{
				require( $path );
				if (!class_exists( $modelClass ))
				{
					JError::raiseWarning( 0, 'Model class ' . $modelClass . ' not found in file.' );
					return $false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Model ' . $modelName . ' not supported. File not found.' );
				return $false;
			}
		}

		$model = & new $modelClass();
		$model->setController( $this );
		return $model;
	}

	/**
	 * Method to load and return a view object.  This method first looks in the current template directory for a match, and
	 * failing that uses a default set path to load the view class file.
	 *
	 * @access	private
	 * @param	string	The name of the view
	 * @param	string	The component folder name
	 * @param	string	Optional prefix for the view class name
	 * @return	mixed	View object or boolean false if failed
	 * @since	1.5
	 */
	function &_loadView( $viewName, $option='', $classPrefix='' )
	{
		// Clean the view name
		$viewName	= preg_replace( '#\W#', '', $viewName );
		$option		= preg_replace( '#\W#', '', $option );
		$classPrefix= preg_replace( '#\W#', '', $classPrefix );

		$result		= false;
		if ($option)
		{
			// Get the current template name and path
			$tName = $this->_app->getTemplate();
			$tPath = JPATH_BASE.DS.'templates'.DS.$tName.DS.$option.DS.strtolower($viewName).'.php';
		}
		else
		{
			$tPath = null;
		}

		// If a matching view exists in the current template folder we use that, otherwise we look for the default one
		if (file_exists( $tPath ))
		{
			require( $tPath );
			// Build the view class name
			// Alternate view classes must be postfixed with '_alt'
			$viewClass = $classPrefix.$viewName.'_alt';
			if (!class_exists( $viewClass ))
			{
				JError::raiseNotice( 0, 'View class '.$viewClass.' not found' );
			}
			else
			{
				$result = & new $viewClass( $this );
				return $result;
			}
		}
		else
		{
			// Build the path to the default view based upon a supplied base path
			$path = $this->getViewPath().strtolower($viewName.DS.$viewName).'.php';

			// If the default view file exists include it and try to instantiate the object
			if (file_exists( $path ))
			{
				require_once( $path );
				// Build the view class name
				$viewClass = $classPrefix.$viewName;
				if (!class_exists( $viewClass ))
				{
					JError::raiseNotice( 0, 'View class ' . $viewClass . ' not found in file.' );
				}
				else
				{
					$result = & new $viewClass( $this );
					return $result;
				}
			}
			else
			{
				JError::raiseNotice( 0, 'View ' . $viewName . ' not supported. File not found.' );
			}
		}
		return $result;
	}

	/**
	 * Authorization check
	 *
	 * @access	public
	 * @param	string	$task	The ACO Section Value to check access on
	 * @return	boolean	True if authorized
	 * @since	1.5
	 */
	function authorize( $task )
	{
		// Only do access check if the aco section is set
		if ($this->_acoSection)
		{
			// If we have a section value set that trumps the passed task ???
			if ($this->_acoSectionValue)
			{
				// We have one, so set it and lets do the check
				$task = $this->_acoSectionValue;
			}
			// Get the JUser object for the current user and return the authorization boolean
			$user = & JFactory::getUser();
			return $user->authorize( $this->_acoSection, $task );
		}
		else
		{
			// Nothing set, nothing to check... so obviously its ok :)
			return true;
		}
	}

	/**
	 * Typical view method for MVC based architecture
	 */
	function display()
	{
		$view = &$this->getView();
		$view->display();
	}

	/**
	 * Execute a task by triggering a method in the derived class
	 *
	 * @access	public
	 * @param	string	$task	The task to perform
	 * @return	mixed	The value returned by the function
	 * @since	1.5
	 */
	function execute( $task )
	{
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] ))
		{
			// We have a method in the map to this task
			$doTask = $this->_taskMap[$task];
		}
		else if (isset( $this->_taskMap['__default'] ))
		{
			// Didn't find the method, but we do have a default method
			$doTask = $this->_taskMap['__default'];
		}
		else
		{
			// Don't have a default method either...
			JError::raiseError( 404, JText::_('Task ['.$task.'] not found') );
			return false;
		}
		// Time to make sure we have access to do what we want to do...
		if ($this->authorize( $doTask ))
		{
			// Yep, lets do it already
			return call_user_func( array( &$this, $doTask ) );
		}
		else
		{
			// No access... better luck next time
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return false;
		}
	}

	/**
	 * Get the application
	 *
	 * @access	public
	 * @return object
	 * @since	1.5
	 */
	function &getApplication()
	{
		return $this->_app;
	}

	/**
	 * Get the system database object from the application
	 *
	 * @access	public
	 * @return object
	 * @since	1.5
	 */
	function &getDBO()
	{
		return JFactory::getDBO();
	}

	/**
	 * Get the error message
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}

	/**
	 * Method to get a model object, load it if necessary..
	 *
	 * @access	public
	 * @param	string The model name
	 * @param	string The class prefix
	 * @return	object	The model
	 * @since	1.5
	 */
	function &getModel($name, $prefix='')
	{
		$model = & $this->_loadModel( $name, $prefix );
		return $model;
	}

	/**
	 * Method to get the current view path
	 *
	 * @access	public
	 * @param	string	Model class file base directory
	 * @return	string	The path
	 * @since	1.5
	 */
	function getModelPath()
	{
		return $this->_modelPath;
	}

	/**
	 * Get the last task that was to be performed
	 *
	 * @access	public
	 * @return	string	The task that was or is being performed
	 * @since	1.5
	 */
	function getTask()
	{
		return $this->_task;
	}

	/**
	 * Data getter
	 * @param string The name of the data variable
	 * @return mixed The value of the data variable
	 */
	function &getVar( $name ) {
		if (isset( $this->_vardata[$name] )) {
			return $this->_vardata[$name];
		} else {
			$null = null;
			return $null;
		}
	}

	/**
	 * Method to get the current view and load it if necessary..
	 *
	 * @access	public
	 * @return	object	The view
	 * @since	1.5
	 */
	function &getView($name='', $option='', $prefix='')
	{
		if (is_null( $this->_view ))
		{
			if ($name == '')
			{
				$name = $this->_viewName;
			}
			if ($option == '')
			{
				$option = $this->_viewOption;
			}
			if ($prefix == '')
			{
				$prefix = $this->_viewClassPrefix;
			}
			$view = $this->_loadView( $name, $option, $prefix );
			$this->setView( $view );
		}
		return $this->_view;
	}

	/**
	 * Method to get the current view path
	 *
	 * @access	public
	 * @param	string	View class file base directory
	 * @return	string	The path
	 * @since	1.5
	 */
	function getViewPath()
	{
		return $this->_viewPath;
	}

	/**
	 * Alias for execute
	 * @deprecated Use execute method instead
	 */
	function performTask( $task )
	{
		return $this->execute( $task );
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @access	public
	 * @return	boolean	False if no redirect exists
	 * @since	1.5
	 */
	function redirect()
	{
		if ($this->_redirect) {
			josRedirect( $this->_redirect, $this->_message );
		}
	}

	/**
	 * Register the default task to perfrom if a mapping is not found
	 *
	 * @access	public
	 * @param	string	$method	The name of the method in the derived class to perform if the task is not found
	 * @return	void
	 * @since	1.5
	 */
	function registerDefaultTask( $method )
	{
		$this->registerTask( '__default', $method );
	}

	/**
	 * Register (map) a task to a method in the class
	 *
	 * @access	public
	 * @param	string	$task		The task
	 * @param	string	$method	The name of the method in the derived class to perform for this task
	 * @return	void
	 * @since	1.5
	 */
	function registerTask( $task, $method )
	{
		if (in_array( strtolower( $method ), $this->_methods ))
		{
			$this->_taskMap[strtolower( $task )] = $method;
		}
		else
		{
			JError::raiseError( 404, JText::_('Method '.$method.' not found') );
		}
	}

	/**
	 * Sets the access control levels
	 *
	 * @access	public
	 * @param string The ACO section (eg, the component)
	 * @param string The ACO section value (if using a constant value)
	 * @return	void
	 * @since	1.5
	 */
	function setAccessControl( $section, $value=null )
	{
		$this->_acoSection = $section;
		$this->_acoSectionValue = $value;
	}

	/**
	 * Sets the error message
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value ) {
		$this->_error = $value;
		return $this->_error;
	}

	/**
	 * Method to get the current model path
	 *
	 * @access	public
	 * @return	string	Model class file base directory
	 * @since	1.5
	 */
	function setModelPath( $path )
	{
		$this->_modelPath = $path.DS;
		return $this->_modelPath;
	}

	/**
	 * Set a URL to redirect the browser to
	 *
	 * @access	public
	 * @param	string	$url	URL to redirect to
	 * @param	string	$msg	Message to display on redirect
	 * @return	void
	 * @since	1.5
	 */
	function setRedirect( $url, $msg = null )
	{
		$this->_redirect = $url;
		if ($msg !== null) {
			$this->_message = $msg;
		}
	}

	/**
	 * Method to get the current view path
	 *
	 * @access	public
	 * @return	string	View class file base directory
	 * @since	1.5
	 */
	function setViewPath( $path )
	{
		$this->_viewPath = $path.DS;
		return $this->_viewPath;
	}

	/**
	 * Data setter
	 * @param string The name of the data variable
	 * @param mixed The value of the data variable
	 */
	function setVar( $name, &$value )
	{
		$this->_vardata[$name] = &$value;
	}

	/**
	 * Method to set the current view.  Normally this would be done automatically, but this method is provided
	 * for maximum flexibility
	 *
	 * @access	public
	 * @param	object	The view object to set
	 * @return	object	The view
	 * @since	1.5
	 */
	function &setView( &$view )
	{
		$this->_view = &$view;
		return $view;
	}

	/**
	 * Method to set the view name and options for loading the view class.
	 *
	 * @access	public
	 * @param	string	$viewName	The name of the view
	 * @param	string	$option		The component subdirectory of the template folder to look in for an alternate
	 * @param	string	$prefix		Optional prefix for the view class name
	 * @return	void
	 * @since	1.5
	 */
	function setViewName( $viewName, $option=null, $prefix=null )
	{
		$this->_viewName = $viewName;
		if ($option !== null)
		{
			$this->_viewOption = $option;
		}
		if ($prefix !== null)
		{
			$this->_viewClassPrefix = $prefix;
		}
	}
}
?>