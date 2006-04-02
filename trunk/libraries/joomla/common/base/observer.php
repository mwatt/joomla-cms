<?php
/**
* @version $Id: $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
 * Abstract observer class to implement the observer design pattern
 *
 * @abstract
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package	Joomla.Framework
 * @since	1.1
 */
class JObserver extends JObject {

	/**
	 * Event object to observe
	 *
	 * @access private
	 * @var object
	 */
	var $_subject = null;

	/**
	 * Constructor
	 */
	function __construct(& $subject) 
	{
		// Register the observer ($this) so we can be notified
		$subject->attach($this);

		// Set the subject to observe
		$this->_subject = & $subject;
	}

	/**
	 * Method to update the state of observable objects
	 *
	 * @abstract Implement in child classes
	 * @access public
	 * @return mixed
	 */
	function update() {
		return JError::raiseError('9', 'JObserver::update: Method not implemented', 'This method should be implemented in a child class');
	}
}

/**
 * Abstract observable class to implement the observer design pattern
 *
 * @abstract
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package	Joomla.Framework
 * @since	1.1
 */

class JObservable extends JObject {

	/**
	 * An array of Observer objects to notify
	 *
	 * @access private
	 * @var array
	 */
	var $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @access private
	 * @var mixed
	 */
	var $_state = null;


	/**
	 * Constructor
	 */
	function __construct() {
		$this->_observers = array();
	}

	/**
	 * Get the state of the JObservable object
	 *
	 * @access public
	 * @return mixed The state of the object
	 * @since 1.1
	 */
	function getState() {
		return $this->_state;
	}

	/**
	 * Update each attached observer object and return an array of their return values
	 *
	 * @access public
	 * @return array Array of return values from the observers
	 * @since 1.1
	 */
	function notify() {
		// Iterate through the _observers array
		foreach ($this->_observers as $observer) {
			$return[] = $observer->update();
		}
		return $return;
	}

	/**
	 * Attach an observer object
	 *
	 * @access public
	 * @param object $observer An observer object to attach
	 * @return void
	 * @since 1.1
	 */
	function attach( $observer) {
		$this->_observers[] = $observer;
	}

	/**
	 * Detach an observer object
	 *
	 * @access public
	 * @param object $observer An observer object to detach
	 * @return boolean True if the observer object was detached
	 * @since 1.1
	 */
	function detach( $observer) {
		// Initialize variables
		$retval = false;

		if ($k = array_search($observer, $this->_observers)) {
			unset($this->_observers[$k]);
			$retval = true;
		}
		return $retval;
	}
}
?>