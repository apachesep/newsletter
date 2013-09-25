<?php

/**
 * The cron controller file.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
jimport('migur.library.mailer');
JLoader::import('helpers.module', JPATH_COMPONENT_ADMINISTRATOR, '');
JLoader::import('helpers.subscriber', JPATH_COMPONENT_ADMINISTRATOR, '');
JLoader::import('helpers.newsletter', JPATH_COMPONENT_ADMINISTRATOR, '');
JLoader::import('plugins.manager', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::import('helpers.plugin', JPATH_COMPONENT_ADMINISTRATOR);

/**
 * Class of the cron controller. Handles the  request of a "trigger" from remote server.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterControllerNewsletter extends JControllerForm
{

	/**
	 * The constructor of a class
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Renders the newsletter for an subscriber.
	 *
	 * @return void
	 * @since  1.0
	 */
	public function render()
	{

		/*
		 * Get the info about current user.
		 * Check if the user is admin.
		 */

		//TODO: Get the admin session...

		/*
		 *  Let's render the newsletter.
		 *  If subscriber's email is provided then add info about this user
		 *  to environment
		 */

		$newsletterId = JRequest::getVar('newsletter_id', null);
		$type         = JRequest::getVar('type', null);
		$email        = urldecode(JRequest::getVar('email', null));
		$alias        = JRequest::getString('alias', null);

		$model = MigurModel::getInstance('Newsletter', 'NewsletterModel');

		if (!empty($alias)) {
			$newsletter = NewsletterHelperNewsletter::getByAlias($alias);
		}

		if (!empty($newsletterId)) {
			$newsletter = (array) $model->getItem($newsletterId);
		}

		if (empty($newsletter)) {
			echo json_encode(array(
				'state' => '0',
				'error' => JText::_('COM_NEWSLETTER_NEWSLETTER_ID_NOT_FOUND'),
			));
			return;
		}

		$newsletterId = $newsletter['newsletter_id'];

		if (empty($type)) {
			echo json_encode(array(
				'state' => '0',
				'error' => JText::_('COM_NEWSLETTER_TYPE_NOT_FOUND'),
			));
			return;
		}

		// Load newseltter language
		JFactory::getLanguage()->load('com_newsletter_modules', JPATH_ADMINISTRATOR);

		$mailer = new NewsletterClassMailer();

		// emulate user environment
		NewsletterHelperSubscriber::saveRealUser();
		NewsletterHelperSubscriber::emulateUser(array('email' => $email));


		// render the content of letter for each user
		$html = $mailer->render(array(
			'type' => $type,
			'newsletter_id' => $newsletterId,
			'useRawUrls' => NewsletterHelperNewsletter::getParam('rawurls') == '1'
		));

		NewsletterHelperSubscriber::restoreRealUser();

		echo $html; die;
	}


	/**
	 * Render and send the letter to the selected emails
	 * Only for preview!!!! This method dont use the DB data.
	 * It gets data from REQUEST:
	 *	- params,
	 *  - title,
	 *  - showtitle,
	 *  - extension_id(the id of a module in db)
	 *  - native
	 *
	 * @return void
	 * @since  1.0
	 */
	public function rendermodule()
	{
		ob_start();

		$native     = JRequest::getString('native');
		$id         = JRequest::getString('extension_id');
		$params     = JRequest::getVar('params', array(), 'post', 'array');
		$title      = JRequest::getString('title');
		$showTitle  = JRequest::getString('showtitle');

		$modules = NewsletterHelperModule::getSupported(array(
			'extension_id' => $id,
			'native'       => $native
		));

		$module = $modules[0];

		// Override needed data
		foreach ($params as &$item) {
			if (is_array($item) && count($item) == 1 && $item[0] == 'null') {
				$item = array();
			}
		}
		$module->params     = json_encode((object)$params);
		$module->title      = $title;
		$module->showtitle  = $showTitle;

		// Load newseltter language
		JFactory::getLanguage()->load('com_newsletter_modules', JPATH_ADMINISTRATOR);

		$content = NewsletterHelperContent::pathsToAbsolute(
			NewsletterHelperModule::renderModule($modules[0])
		);

		ob_end_clean();

		header("Content-Type: text/html; charset=UTF-8");

		echo $content; die;
	}
	/**
	 * Render and send the letter to the selected emails
	 *
	 * @return void
	 * @since  1.0
	 */
	public function sendPreview()
	{
		NewsletterHelperNewsletter::jsonPrepare();

		$emails = JRequest::getVar('emails', array());
		$newsletterId = JRequest::getVar('newsletter_id');
		$type = JRequest::getVar('type');

		if (empty($type) || empty($newsletterId)) {
			NewsletterHelperNewsletter::jsonError(JText::_('COM_NEWSLETTER_RUQUIRED_MISSING'));
		}

		if (empty($emails)) {
			NewsletterHelperNewsletter::jsonError(JText::_('COM_NEWSLETTER_ADD_EMAILS'));
		}

		$data = array(
			'newsletter_id' => $newsletterId,
			'type' => $type,
			'tracking' => true,
			'useRawUrls' => NewsletterHelperNewsletter::getParam('rawurls') == '1'
		);


		// Process list of emails....
		$subscriber = MigurModel::getInstance('Subscriber', 'NewsletterModelEntity');
		foreach ($emails as $email) {

			// Trying to find subscriber or J!user
			if ($subscriber->load(array('email' => $email[1]))) {

				// If subscriber is allowed to send to then add him to list.
				$data['subscribers'][] = $subscriber->toObject();

			} else {

				// If this is unknown email then add it to list.
				// All we know is email only.
				$data['subscribers'][] = (object)array('email' => $email[1]);
			}
		}

		// If list is empty then finish with it.
		if(empty($data['subscribers'])) {
			NewsletterHelperNewsletter::jsonError(JText::_('COM_NEWSLETTER_NO_EMAILS_TO_SEND'));
		}

		// Send mails.....
		$mailer = new NewsletterClassMailer();
		if(!$mailer->sendToList($data)) {

			$errors = $mailer->getErrors();

			NewsletterHelperLog::addDebug('Sending of preview was failed.',
				NewsletterHelperLog::CAT_MAILER,
				array(
					'Errors' => $errors,
					'Emails' => $emails));

			NewsletterHelperNewsletter::jsonError($errors, $emails);
		}

		// Some debugging
		NewsletterHelperLog::addDebug('Preview was sent successfully.',
			NewsletterHelperLog::CAT_MAILER,
			array('Emails' => $emails));


		NewsletterHelperNewsletter::jsonMessage(JText::_('COM_NEWSLETTER_PREVIEW_SENT_SUCCESSFULLY'), $emails);
	}
}

