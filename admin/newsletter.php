<?php

/**
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//  Uncoment this rows for debug
//  ini_set('error_reporting', E_ALL | E_NOTICE);
//  ini_set('display_errors', '1');
//  ini_set("log_errors" , "0");
//  ini_set("error_log" , "/var/log/php-error.log");

require_once 'bootstrap.php';

MigurComNewsletterBootstrap::initAutoloading();

try {

	// Setub toolbar, forms and so on...
	MigurComNewsletterBootstrap::initEnvironment();

	MigurComNewsletterBootstrap::initJoomlaTools();

	// First check if user has access to the component.
	if (
		!NewsletterHelperAcl::canAccessComponent() /*||
		!NewsletterHelperAcl::actionIsAllowed(JRequest::getCmd('task'))*/
	) {
		NewsletterHelperAcl::redirectToAccessDenied();
	}

	// Setup the cache
	MigurComNewsletterBootstrap::initCache();

	// Add translations used in JavaScript
	NewsletterHelperJavascript::requireTranslations();

	// Load 'Migur' group of plugins
	NewsletterHelperPlugin::prepare();

	$app = JFactory::getApplication();
	$app->triggerEvent('onMigurStart');

	// Handle the messages from previous requests
	$sess = JFactory::getSession();
	$msg = $sess->get('migur.queue');
	if ($msg) {
		$sess->set('application.queue', $msg);
		$sess->set('migur.queue', null);
	}

	// Add the site root and user's ACL to JS
	NewsletterHelperJavascript::addStringVar('migurSiteRoot', JUri::root());
	NewsletterHelperJavascript::addObject('migurUserAcl', NewsletterHelperAcl::toArray());

	// Get an instance of the controller
	// Here we get full task and preserve it from exploding
	JFactory::getApplication()->input->set('completetask', JRequest::getCmd('task'));
    JRequest::setVar('completetask', JRequest::getCmd('task'));

	$controller = MigurController::getInstance('Newsletter');

	// Perform the Request task
	// Here we get only tail of a task
	$taskMethod = JFactory::getApplication()->input->get('task');
	$controller->execute($taskMethod);

	// Trigger events after exacution
	$app->triggerEvent('onMigurShoutdown');

	// Redirect if set by the controller
	$controller->redirect();

	//$app = JFactory::getApplication();
	//$results = $app->triggerEvent('onAfterRender', array());

	// If there is no redirection then let's check the license and notify the admin
	// No need to check if this is a redirection
	if ( JRequest::getString('tmpl') != 'component') {

		// Get license data (may be cached data)
		$status = NewsletterHelperNewsletter::getLicenseStatus();

		// If it has no valid license then show the RED message
		if (!$status->isValid) {
			$app->enqueueMessage(JText::_('COM_NEWSLETTER_LICENSE_INVALID'), 'error');
		}
	}

	// Do all things at the end of component lifetime.
	MigurComNewsletterBootstrap::shutDown();

} catch (Exception $e) {

	NewsletterHelperLog::addDebug(
		'COM_NEWSLETTER_UNCAUGHT_EXCEPTION',
		'common',
		$e);

	throw $e;
}
