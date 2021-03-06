<?php

/**
 * The controller for mailboxprofile view.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controlleradmin');

class NewsletterControllerMailboxprofiles extends JControllerAdmin
{
	/**
	 * Redirection after standard saving
	 *
	 * @return void
	 * @since 1.0
	 */
	public function delete()
	{
                $jform = JRequest::getVar('jform');
                if ($jform['general_mailbox_default'] > 0) {
					JFactory::getApplication()->input->set('cid', $jform['general_mailbox_default']);
                    JRequest::setVar('cid', $jform['general_mailbox_default']);
                    parent::delete();
                } else {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_NEWSLETTER_CANNOT_DELETE_JPROFILE'), 'error');
                }

                $rurl = JRequest::getString('returnurl');
                if (!empty($rurl)) {
                    $this->setRedirect(base64_decode($rurl));
                }
	}

	/**
	 * Proxy for getModel.
	 *
	 * @return  void
	 * @since	1.0
	 */
	public function getModel($name = 'Mailboxprofile', $prefix = 'NewsletterModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}

