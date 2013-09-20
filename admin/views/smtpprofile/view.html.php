<?php

/**
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
JHtml::_('behavior.formvalidation');
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.helper');
jimport('migur.library.toolbar');
jimport('joomla.html.pagination');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');

// import Joomla view library

/**
 * Newsletter View
 */
class NewsletterViewSmtpprofile extends MigurView
{

	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/admin.css');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/smtpprofile.css');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/core.js');

		$this->ssForm = $this->get('Form', 'smtpprofile');

		$model = MigurModel::getInstance('Smtpprofile', 'NewsletterModelEntity');
		$smtpid = JRequest::getInt('smtp_profile_id', null);

		if ($smtpid !== null) {
			$model->load($smtpid);
		}

		NewsletterHelperJavascript::addStringVar('migurIsJoomlaProfile', $model->isJoomlaProfile());

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();

		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.0
	 */
	protected function addToolbar()
	{
		$bar = MigurToolbar::getInstance('smtp-profile');
		$bar->addButtonPath(COM_NEWSLETTER_PATH_ADMIN . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'toolbar' . DIRECTORY_SEPARATOR . 'button');
		$bar->appendButton('Migurhelp', 'help', 'COM_NEWSLETTER_HELP', NewsletterHelperSupport::getResourceUrl('smtp'));
		$bar->appendButton('Migurstandard', 'publish', 'COM_NEWSLETTER_CHECK', 'smtpprofile.checkconnection', false);
		$bar->appendButton('Migurstandard', 'cancel', 'JTOOLBAR_CANCEL', '', false);
		$bar->appendButton('Migurstandard', 'save', 'JTOOLBAR_SAVE', 'smtpprofile.save', false);
	}


	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = (!JRequest::get('smtp_profile_id', false) );
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_NEWSLETTER_SMTP_CREATING') : JText::_('COM_NEWSLETTER_SMTP_EDITING'));
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/smtpprofile/submitbutton.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/smtpprofile/smtpprofile.js');
		JText::script('COM_NEWSLETTER_MAILBOX_ERROR_UNACCEPTABLE');
	}

}
