<?php

/**
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class MigurComNewsletterBootstrap
{
	static public $displayErrors;

	static public $errorReporting;

	/**
	 * Load all constants needed in component
	 */
	static public function initEnvironment()
	{
		require_once 'constants.php';

		// import joomla controller library
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.view');
		jimport('joomla.form.helper');
		jimport('migur.migur');

		// Add the helper's
		JLoader::import('helpers.acl', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.plugin', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.javascript', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.rssfeed', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.newsletter', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.log', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.support', COM_NEWSLETTER_PATH_ADMIN);
		JLoader::import('helpers.module', COM_NEWSLETTER_PATH_ADMIN);

		// Set needed php error levels
		$params = JComponentHelper::getParams('com_newsletter');

		// Save current settings for restoring it on shutdown
		self::$displayErrors  = ini_get('display_errors');
		self::$errorReporting = ini_get('error_reporting');

		if ((defined('COM_NEWSLETTER_ENV') && COM_NEWSLETTER_ENV == 'development') || getenv('COM_NEWSLETTER_ENV') == 'development' || $params->get('debug') == '1') {
			ini_set('display_errors', 'on');
			ini_set('error_reporting', E_ALL);
		} else {
			ini_set('display_errors', 'off');
			ini_set('error_reporting', E_NONE);
		}
	}


	/**
	 * Init autoloading of a component
	 */
	static public function initAutoloading()
	{
		// Run autoloader
		JLoader::import('helpers.autoload', dirname(__FILE__));
		NewsletterHelperAutoload::setup();
	}


	/**
	 * Init the cacahe for the component
	 */
	static public function initCache()
	{
		// Setup the cache
		$cache = JFactory::getCache('com_newsletter');
		$cache->setCaching(true);
		$cache->setLifeTime(900); // cache to 5 min
	}


	/**
	 * Setup J! tools for usage with component's addons
	 */
	static public function initJoomlaTools()
	{
		// Add the paths to component's addons for J! tools.
		JHtml::addIncludePath(
			COM_NEWSLETTER_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');

		MigurToolbar::addGlobalButtonPath(
			COM_NEWSLETTER_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'toolbar' . DIRECTORY_SEPARATOR . 'button'
		);

		JFormHelper::addRulePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'rules');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'tables');
		MigurModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models', 'NewsletterModel');
		MigurModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'entities', 'NewsletterModelEntity');
	}

	/**
	 * Setup J! tools for usage with component's addons
	 */
	static public function initJoomlaToolsSite()
	{
		// Add the paths to component's addons for J! tools.
		JHtml::addIncludePath(
			COM_NEWSLETTER_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html'
		);

		JFormHelper::addRulePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'rules');
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'tables');
		MigurModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models', 'NewsletterModel');
		MigurModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'entities', 'NewsletterModelEntity');
	}

	/**
	 * Actions that need to be executed on finishing component's lifetime
	 */
	static public function shutDown()
	{
		// Restore error reporting settings
		ini_set('display_errors', self::$displayErrors);
		ini_set('error_reporting', self::$errorReporting);
	}

}
