<?php

/**
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

//  Uncoment this rows for debug
//  ini_set('error_reporting', E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
//  ini_set('display_errors', '1');
//  ini_set("log_errors" , "0");
//  ini_set("error_log" , "/var/log/php-error.log");

require_once JPATH_ROOT
	.DIRECTORY_SEPARATOR.'administrator'
	.DIRECTORY_SEPARATOR.'components'
	.DIRECTORY_SEPARATOR.'com_newsletter'
	.DIRECTORY_SEPARATOR.'bootstrap.php';

// Run autoloader
MigurComNewsletterBootstrap::initAutoloading();

try {

	// Setub toolbar, forms and so on...
	MigurComNewsletterBootstrap::initJoomlaToolsSite();

	// Constants, required J! files, so on...
	MigurComNewsletterBootstrap::initEnvironment();

	// Setup the cache
	MigurComNewsletterBootstrap::initCache();

	// Get an instance of the controller prefixed by Newsletter
	$controller = MigurController::getInstance('Newsletter');

	// ACL
		$resource = JRequest::getString('view','') .'.'. JRequest::getString('layout','default');

		switch($resource){

			case 'subscribe.unsubscribe':

				if(!JFactory::getUser()->id && !JRequest::getString('uid', NULL)) {

					JFactory::getApplication()->redirect(
						JRoute::_('index.php?option=com_users&view=login&returnurl=' . base64_encode(JRoute::_('index.php?option=com_newsletter&view=subscribe&layout=unsubscribe', false)) ),
						JText::_('COM_NEWSLETTER_LOGIN_FIRST '),
						'message');
					}
		}

	// Add translations used in JavaScript
	NewsletterHelperJavascript::requireTranslations();

	NewsletterHelperPlugin::prepare();

	$app = JFactory::getApplication();
	$app->triggerEvent('onMigurStart');

	// Perform the Request task
	$controller->execute(NewsletterHelperNewsletter::getTask());

	$app->triggerEvent('onMigurShoutdown');

	// Redirect if set by the controller
	$controller->redirect();

} catch (Exception $e) {

	NewsletterHelperLog::addDebug(
		'COM_NEWSLETTER_UNCAUGHT_EXCEPTION',
		'common',
		$e);

	throw $e;
}

