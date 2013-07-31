<?php

/**
 * The subscriber view file.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
jimport('joomla.html.pagination');
jimport('joomla.form.helper');
jimport('migur.library.toolbar');

/**
 * Class of the subscriber view. Displays the model data.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterViewSubscriber extends MigurView
{

	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Displays the view.
	 *
	 * @param  string $tpl the template name
	 *
	 * @return void
	 * @since  1.0
	 */
	public function display($tpl = null)
	{
		$script = $this->get('Script');
		$this->script = $script;

		$this->ssForm = $this->get('Form', 'subscriber');

		// call getItems from model 'lists' via MigurView->get()
		$this->subscriberId = $this->ssForm->getValue('subscriber_id');


		if ($this->getLayout() == 'edit') {

			$model = MigurModel::getInstance('Subscriber', 'NewsletterModelEntity');
			$model->load($this->subscriberId);
			$this->subscriber = $model;
			
			$listsModel = MigurModel::getInstance('lists', 'NewsletterModel');
			$model = $this->setModel($listsModel);
			
			$model->filtering = array('subscriber_id' => JRequest::getInt('subscriber_id', null));
			$model->setSubscriberQuery();

			$sentsModel = MigurModel::getInstance('sents', 'NewsletterModel');
			$model = $this->setModel($sentsModel);
			
			$model->filtering = array('subscriber_id' => JRequest::getInt('subscriber_id', null));

			$historyModel = MigurModel::getInstance('history', 'NewsletterModel');
			$model = $this->setModel($historyModel);
			
			$model->filtering = array('subscriber_id' => JRequest::getInt('subscriber_id', null));

			$this->listItems = $this->get('Items', 'lists');

			$this->newsletterItems = $this->get('Items', 'sents');
			$this->historyItems = $this->get('Items', 'history');

			// used to get the pagination layout in any cases
			$this->historyPagination = new JPagination(10, 0, 5);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		
		// Set the document
		$this->setDocument();

		parent::display($tpl);

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 * @since	1.0
	 */
	protected function addToolbar()
	{
		$bar = JToolBar::getInstance();
		$bar->appendButton('Standard', 'cancel', 'JTOOLBAR_CANCEL', 'subscriber.cancel', false);
		$bar->appendButton('Standard', 'save', 'JTOOLBAR_SAVE', 'subscriber.save', false);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 * @since  1.0
	 */
	protected function setDocument()
	{
		$isNew = (!JRequest::get('subscriber_id', false) );
		JToolbarHelper::title($isNew ? JText::_('COM_NEWSLETTER_NEW_SUBSCRIBER') : JText::_('COM_NEWSLETTER_SUBSCRIBER_EDITING'));
		$document = JFactory::getDocument();
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/core.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/message.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/subscriber/submitbutton.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/subscriber/subscriber.js');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/admin.css');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/subscriber.css');
		
		JText::script('COM_NEWSLETTER_SUBSCRIBER_ERROR_UNACCEPTABLE');
	}

}
