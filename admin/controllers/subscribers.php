<?php
/**
 * The controller for subscribers view.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

class NewsletterControllerSubscribers extends JControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0
	 */
	protected $text_prefix = 'COM_NEWSLETTER_SUBSCRIBERS';

	/**
	 * Proxy for getModel.
	 *
	 * @return  void
	 * @since	1.0
	 */
	public function getModel($name = 'Subscriber', $prefix = 'NewsletterModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}


	/**
	 * Check each element and delete deleteable ones
	 */
	public function delete() {

		$cids = JRequest::getVar('cid', array());

		$unset = 0;

		if (!empty($cids)) {

			$model = MigurModel::getInstance('Subscriber', 'NewsletterModelEntity');

			foreach($cids as $idx => $cid) {

				$model->load($cid);

				if ($model->isJoomlaUserType()) {
					unset($cids[$idx]);
					$unset++;
				}
			}

			if ($unset > 0) {
				JFactory::getApplication()->enqueueMessage(sprintf(JText::_('COM_NEWSLETTER_SUBSCRIBERS_ITEMS_CANNOT_DELETED'), $unset), 'message');
			}

			if (empty($cids)) {
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
				return;
			}

			JFactory::getApplication()->input->set('cid', $cids);
			JRequest::setVar('cid', $cids);
		}


		return parent::delete();
	}


	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cids = JRequest::getVar('cid', array(), '', 'array');

		$task = JRequest::getVar('task');

		// If needed create rows in SUBSCRIBERS for J! user
		$model = MigurModel::getInstance('Subscriber', 'NewsletterModelEntity');
		$newCids = array();
		$msg = null;
		foreach($cids as $cid) {
			$model->load($cid);

			if ($task == 'trash' && $model->isJoomlaUserType()) {
				$msg = "COM_NEWSLETTER_SUBSCRIBERS_CANNOT_TRASH_JUSER";
			} else {
				$newCids[] = $model->getId();
			}
		}

		if (!empty($msg)) {
			JFactory::getApplication()->enqueueMessage(JText::_($msg), 'error');
		}

		// Then update CIDs by new subscriber_id
		JFactory::getApplication()->input->set('cid', $newCids);
		JRequest::setVar('cid', $newCids);
		return parent::publish();
	}
}
