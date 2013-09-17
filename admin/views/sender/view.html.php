<?php

/**
 * The sender view file.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');


JLoader::import('helpers.mail', JPATH_COMPONENT_ADMINISTRATOR, '');
JLoader::import('models.fields.newsletters', JPATH_COMPONENT_ADMINISTRATOR, '');

// import Joomla view library
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
jimport('joomla.application.component.view');
jimport('joomla.html.pagination');
jimport('migur.library.toolbar');
JHtml::_('behavior.framework', true);

/**
 * Class of the  view. Displays the model data.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterViewSender extends MigurView
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
		JHTML::_('behavior.modal');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/admin.css');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/sender.css');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/core.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/iterativeajax.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/sender/sender.js');

		$listsModel = MigurModel::getInstance('lists', 'NewsletterModel');
		$this->setModel($listsModel);

		$newslettersModel = MigurModel::getInstance('newsletters', 'NewsletterModel');
		$this->setModel($newslettersModel);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$modelLists = $this->getModel('lists');

		$lists = (object) array(
				'items' => $modelLists->fetchItems(array('filters' => array('state' => '1'))),
				'pagination' => new JPagination(10, 0, 5), // used to get the pagination layout for JS pagination
				'listOrder' => 'name',
				'listDirn' => 'asc'
		);

		NewsletterHelperJavascript::addStringVar('defaultMailbox', NewsletterHelperMail::getDefaultMailbox('idOnly'));

		$this->assignRef('lists', $lists);

		$this->addToolbar();

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
		$bar = MigurToolbar::getInstance('sender');
		$bar->appendButton('Link', 'export', 'COM_NEWSLETTER_NEWSLETTER_SEND', '#');

		// Load the submenu.
		NewsletterHelperNewsletter::addSubmenu(JRequest::getVar('view'));
	}
}
