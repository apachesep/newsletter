<?php

/**
 * The automailings list view file.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');
JHTML::_('behavior.modal');
jimport('joomla.application.component.view');
jimport('migur.library.toolbar');

/**
 * Class of the automailings list view. Displays the model data.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterViewAutomailings extends MigurView
{

	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Displays the view.
	 *
	 * @param  string $tpl the automailing name
	 *
	 * @return void
	 * @since  1.0
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		$model = $this->getModel('automailings');

		$amList = (object) array(
				'items' => $model->getItems(),
				'state' => $model->getState(),
				'listOrder' => $model->getState('list.ordering'),
				'listDirn' => $model->getState('list.direction')
		);
		$this->assignRef('automailings', $amList);

		$pagination = $model->getPagination();
		$this->assignRef('pagination', $pagination);

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
		JToolBarHelper::title(JText::_('COM_NEWSLETTER_AUTOMAILINGS_TITLE'), 'article.png');

		$bar = MigurToolbar::getInstance('automailings');
		$bar->appendButton('Popup', 'new', 'JTOOLBAR_NEW', 'index.php?option=com_newsletter&view=automailing&tmpl=component', 370, 190, 0, 0);
		$bar->appendButton('Link', 'edit', 'JTOOLBAR_EDIT', 'automailing.edit', false);

		$state	= $this->get('State');

		if ($state->get('filter.published') == MigurModelList::STATE_TRASHED) {
			$bar->appendButton('Migurstandard', 'publish', 'COM_NEWSLETTER_UNTRASH', 'automailings.publish', true);
			$bar->appendButton('Migurstandard', 'delete', 'JTOOLBAR_EMPTY_TRASH', 'automailings.delete');
		} else {
			$bar->appendButton('Migurstandard', 'trash', 'JTOOLBAR_TRASH', 'automailings.trash');
		}

		// Load the submenu.
		NewsletterHelperNewsletter::addSubmenu(JRequest::getVar('view'));
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 * @since  1.0
	 */
	protected function setDocument()
	{
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/admin.css');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/automailings.css');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/core.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/automailings/automailings.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/filterpanel.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/search.js');
	}
}
