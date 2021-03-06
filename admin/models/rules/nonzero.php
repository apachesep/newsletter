<?php

/**
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

jimport('joomla.form.formrule');

/**
 * Form Rule class for the form.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class JFormRuleNonzero extends JFormRule
{

	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		return (int)$value > 0;
	}
}