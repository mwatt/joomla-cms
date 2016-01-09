<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Single Asset item class.
 */
class JHtmlAssetItem
{
	/**
	 * Asset name
	 * @var  string  $name
	 */
	protected $name;

	/**
	 * Asset version
	 * @var  string
	 */
	protected $version;

	/**
	 * Whether attach the version to the scripts/stylesheets
	 * @var bool
	 */
	protected $versionAttach = false;

	/**
	 * Asset data file owner info.
	 * Just for debug, where it come from.
	 * @var array $owner
	 */
	protected $owner;

	/**
	 * Asset JavaScript files
	 * @var  string[]
	 */
	protected $js = array();

	/**
	 * Asset StyleSheet files
	 * @var  string[]
	 */
	protected $css = array();

	/**
	 * Asset dependency
	 * @var  string[]
	 */
	protected $dependency = array();

	/**
	 * Attributes of JavaScript/StyleSheet files
	 *
	 * @var array $attribute
	 */
	protected $attribute = array();

	/**
	 * Item weight
	 * @var float
	 */
	protected $weight = 0;

	/**
	 * Asset state
	 * @var bool $active
	 */
	protected $active = false;

	/**
	 * Deafult defer mode for attached JavaScripts
	 * @var bool $jsDeferMode
	 */
	protected $jsDeferMode = false;

	/**
	 * Class constructor
	 *
	 * @param  string  $name
	 * @param  string  $version
	 * @param  array   $owner
	 */
	public function __construct($name, $version = null, array $owner = array())
	{
		$this->name    = $name;
		$this->version = $version;
		$this->owner   = $owner;
	}

	/**
	 * Return asset name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return asset version
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set JavaScript files
	 * @param array $js
	 * @return JHtmlAssetItem
	 */
	public function setJs(array $js)
	{
		$this->js = $js;

		return $this;
	}

	/**
	 * Return JavaScript files
	 * @return array
	 */
	public function getJs()
	{
		return $this->js;
	}

	/**
	 * Allow to change default defer behaviour forJavaScript files
	 * @param bool $defer
	 * @return JHtmlAssetItem
	 */
	public function deferJavaScript($defer = true)
	{
		$this->jsDeferMode = (bool) $defer;

		return $this;
	}

	/**
	 * Set StyleSheet files
	 * @param array $css
	 * @return JHtmlAssetItem
	 */
	public function setCss(array $css)
	{
		$this->css = $css;

		return $this;
	}

	/**
	 * Return StyleSheet files
	 * @return array
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Set dependency
	 * @param array $dependency
	 * @return JHtmlAssetItem
	 */
	public function setDependency(array $dependency)
	{
		$this->dependency = $dependency;

		return $this;
	}

	/**
	 * Return dependency
	 * @return array
	 */
	public function getDependency()
	{
		return $this->dependency;
	}

	/**
	 * Set Attributes for asset file
	 * @param string $file JavaScript/StyleSheet asset file
	 * @param array $attributes
	 * @return JHtmlAssetItem
	 */
	public function setAttributes($file, array $attributes = array())
	{
		if (empty($this->attribute[$file]))
		{
			$this->attribute[$file] = array();
		}

		$this->attribute[$file] = array_merge($this->attribute[$file], $attributes);

		return $this;
	}

	/**
	 * Return Attributes for asset file
	 * @param string $file JavaScript/StyleSheet asset file
	 * @return array
	 */
	public function getAttributes($file)
	{
		if (!empty($this->attribute[$file]))
		{
			return $this->attribute[$file];
		}

		return array();
	}

	/**
	 * Set asset Weight
	 * @param float $weight
	 * @return JHtmlAssetItem
	 */
	public function setWeight($weight)
	{
		$this->weight = (float) $weight;

		return $this;
	}

	/**
	 * Return asset Weight
	 * @return float
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Set asset State
	 * @param bool $state
	 * @return JHtmlAssetItem
	 */
	public function setActive($state)
	{
		$this->active = (bool) $state;

		return $this;
	}

	/**
	 * Check asset state
	 * @return bool
	 */
	public function isActive()
	{
		return $this->active;
	}

	/**
	 * Set Version Attach property
	 * @param bool $value
	 * @return JHtmlAssetItem
	 */
	public function versionAttach($value)
	{
		$this->versionAttach = (bool) $value;

		return $this;
	}

	/**
	 * Check Version Attach property
	 * @return bool
	 */
	public function isVersionAttach()
	{
		return $this->versionAttach;
	}

	/**
	 * Attach active asset to the Document
	 * @param JDocument $doc
	 * @return JHtmlAssetItem
	 * @throws RuntimeException If try attach inactive asset
	 */
	public function attach(JDocument $doc)
	{
		if (!$this->isActive())
		{
			throw new RuntimeException('Incative Asset cannot be attached');
		}

		$config  = JFactory::getConfig();
		$version = $this->isVersionAttach() ? $this->getVersion() : false;

		// Calculate the version hash based on the asset version,
		// or allow JDocument to attach the default hash. Avoid md5(NULL) version.
		$version = $version ? md5($version . $config->get('secret')) : $version;

		$this->attachCss($doc, $version)->attachJs($doc, $version);

		return $this;
	}

	/**
	 * Attach StyleSheet files to the document
	 * @param JDocument $doc
	 * @param string $version Version to be attached, or false
	 * @return JHtmlAssetItem
	 */
	protected function attachCss(JDocument $doc, $version = false)
	{
		foreach($this->getCss() as $path){
			$file = JHtml::_('stylesheet', $path, array(), true, true);

			if ($file)
			{
				$attribute = $this->getAttributes($path);
				$type      = empty($attribute['type']) ? 'text/css' : $attribute['type'];
				$media     = empty($attribute['media']) ? null : $attribute['media'];

				unset($attribute['type'], $attribute['media']);

				$version === false
					? $doc->addStyleSheet($file, $type, $media, $attribute)
					: $doc->addStyleSheetVersion($file, $version, $type, $media, $attribute);
			}
		}

		return $this;
	}

	/**
	 * Attach JavaScript files to the document
	 * @param JDocument $doc
	 * @param string $version Version to be attached, or false
	 * @return JHtmlAssetItem
	 */
	protected function attachJs(JDocument $doc, $version = false)
	{
		foreach($this->getJs() as $path){
			$file = JHtml::_('script', $path, false, true, true);

			if ($file)
			{
				$attribute = $this->getAttributes($path);
				$type      = empty($attribute['type']) ? 'text/javascript' : $attribute['type'];
				$defer     = empty($attribute['defer']) ? $this->jsDeferMode : (bool) $attribute['defer'];

				unset($attribute['type'], $attribute['defer']);

				// @TODO: Pass $attribute to addScript() when it will support it
				$version === false
					? $doc->addScript($file, $type, $defer)
					: $doc->addScriptVersion($file, $version, $type, $defer);
			}
		}

		return $this;
	}
}
