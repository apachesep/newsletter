<?php

/**
 * The statistic view file.
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
jimport('joomla.application.component.view');
JLoader::import('helpers.statistics', JPATH_COMPONENT_ADMINISTRATOR, '');
JLoader::import('tables.history', JPATH_COMPONENT_ADMINISTRATOR, '');

/**
 * Class of the statistic view. Displays the model data.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterViewStatistic extends MigurView
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
		//TODO: Need to move css/js to SetDocument

		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/admin.css');
		NewsletterHelperView::addStyleSheet('media/com_newsletter/css/statistic.css');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/core.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/filterpanel.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/search.js');		
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/raphael-min.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/g.raphael.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/g.line.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/g.pie.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/g.bar.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/raphael-migur-line.js');
		NewsletterHelperView::addScript('media/com_newsletter/js/migur/js/raphael-migur-pie.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/statistic/hlines.js');
		NewsletterHelperView::addScript('administrator/components/com_newsletter/views/statistic/statistic.js');


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->setStatisticsData();
		parent::display($tpl);
	}

	/**
	 * Get statistics data and set it to JS.
	 * 
	 * @return void
	 * @since  1.0
	 */
	protected function setStatisticsData()
	{
		$ids = JRequest::getString('newsletters', '');
		$this->ids = $ids;

		$days = JRequest::getString('days', 30);
		$this->days = $days;

		$ids = (!empty($ids)) ? explode(',', $ids) : null;

		$data = NewsletterHelperStatistics::totalSent($ids);
		NewsletterHelperJavascript::addObject('statTotalSent', $data);


		$data = NewsletterHelperStatistics::openedActionsCount($ids);
		NewsletterHelperJavascript::addObject('statOpenedCount', $data);


		$data = NewsletterHelperStatistics::openedNewslettersCount($ids);
		$res = array(
			'newsletters' => empty($data['newsletters']) ? 0 : $data['newsletters'],
			'subscribers' => empty($data['subscribers']) ? 0 : $data['subscribers'],
		);
		NewsletterHelperJavascript::addObject('statActiveCount', $res);

		$data = NewsletterHelperStatistics::totalClicks($ids);
		NewsletterHelperJavascript::addObject('statTotalClicks', $data);

		$previousDay = date('Y-m-d 00:00:00', strtotime("-1 day", time()));
		$thisDay = date('Y-m-d 00:00:00');
		
		$daysIdentifier = ($days == 1)? "-1 day" : "-" . $days . " Days";
		$fiewDaysBefore = date('Y-m-d 00:00:00', strtotime($daysIdentifier, time()));

		$previousHour =  date('Y-m-d H:00:00', strtotime("-1 hour", time()));
		$thisHour =  date('Y-m-d H:00:00');
		$oneDayBefore = date('Y-m-d H:00:00', strtotime("-1 day", time()));
		
		NewsletterHelperJavascript::addObject('clicksPerDay',
			NewsletterHelperStatistics::activityPerDay(
				$fiewDaysBefore,
				$thisDay,
				$ids,
				NewsletterTableHistory::ACTION_CLICKED
			)
		);

		NewsletterHelperJavascript::addObject('viewsPerDay',
			NewsletterHelperStatistics::activityPerDay(
				$fiewDaysBefore,
				$thisDay,
				$ids,
				NewsletterTableHistory::ACTION_OPENED
			)
		);

		NewsletterHelperJavascript::addObject('clicksPerHour',
			NewsletterHelperStatistics::activityPerHour(
				$oneDayBefore,
				$thisHour,
				$ids,
				NewsletterTableHistory::ACTION_CLICKED
			)
		);

		NewsletterHelperJavascript::addObject('viewsPerHour',
			NewsletterHelperStatistics::activityPerHour(
				$oneDayBefore,
				$thisHour,
				$ids,
				NewsletterTableHistory::ACTION_OPENED
			)
		);
	}

}
