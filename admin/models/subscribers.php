<?php

/**
 * The subscribers list model. Implements the standard functional for subscribers list view.
 *
 * @version	   $Id:  $
 * @copyright  Copyright (C) 2011 Migur Ltd. All rights reserved.
 * @license	   GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

JLoader::import('tables.history', JPATH_COMPONENT_ADMINISTRATOR, '');

/**
 * Class of subscribers list model of the component.
 *
 * @since   1.0
 * @package Migur.Newsletter
 */
class NewsletterModelSubscribers extends MigurModelList
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

		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'email', 'a.email',
				'state', 'a.state',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'list', 'sl.list_id',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'confirmed', 'a.confirmed',
				'registerDate', 'a.registerDate'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param string $ordering - name of column
	 * @param string $direction - direction
	 *
	 * @return void
	 * @since  1.0
	 */
	protected function populateState($ordering = null, $direction = null, $options = array())
	{
		$fields = array(
			'filter.list' => array('filter_list', ''),
			'filter.type' => array('filter_type', ''),
			'filter.jusergroup' => array('filter_jusergroup', '')
		);

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout')) {
			$this->context .= '.' . $layout;
		}

		// List state information.
		parent::populateState('a.name', 'asc', array('fields' => $fields));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.0
	 */
	public function setDefaultQuery()
	{
		$lid = (int) $this->getState('filter.list');

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// SQL-query for gettting the users-subscibers list.
		$query->select(
			'a.subscriber_id, a.name, a.email, a.state, a.registerDate, a.user_id, ' . ( empty($lid)? '0 AS confirmed ' : 'sl.confirmed AS confirmed ' )
		);

		$query->from(
			'(SELECT s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, COALESCE(u.registerDate, s.created_on) AS registerDate, u.id AS user_id
			FROM #__newsletter_subscribers AS s
			LEFT JOIN #__users AS u ON (s.user_id = u.id)
			WHERE s.user_id = 0 OR u.id IS NOT NULL OR s.email != ""

			UNION

			SELECT   s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, COALESCE(u.registerDate, s.created_on) AS registerDate, u.id AS user_id
			FROM #__newsletter_subscribers AS s
			RIGHT JOIN #__users AS u ON (s.user_id = u.id)) AS a');

		// Filtering the data
		if (!empty($this->filtering)) {
			foreach ($this->filtering as $field => $val)
				$query->where($field . '=' . $val);
		}
		unset($this->filtering);

		// Filter by list state
		if (!empty($lid)) {
			$query->leftJoin("#__newsletter_sub_list AS sl ON a.subscriber_id=sl.subscriber_id");
			$query->where('sl.list_id = ' . (int) $lid);
		}

		// Filter by list state
		$type = $this->getState('filter.type');

		if ($type == 1) {
			$query->where('a.user_id IS NULL');
		}

		if ($type == 2) {
			$query->where('a.user_id > 0');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (in_array($published, array('0', '1'))) {
			$query->where('a.state = ' . (int) $published);
		} elseif($published != '*') {
			$query->where('a.state >= 0');
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else if (stripos($search, 'name:') === 0) {
				$search = $db->Quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(a.name LIKE ' . $search . ')');
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.name LIKE ' . $search . ' OR a.email LIKE ' . $search . ')');
			}
		}

		// Filter by J! user group
		$jusergroup =(int) $this->getState('filter.jusergroup');
		if (!empty($jusergroup)) {
			$jusergroup = (array) $jusergroup;
			$query->join('', "#__user_usergroup_map AS ug ON ug.user_id = a.user_id");
			$query->where('ug.group_id IN (' . implode(',', $jusergroup) . ')');
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'name');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol == 'a.ordering' || $orderCol == 'a.name') {
			$orderCol = 'name';
		}

		if ($orderCol == 'confirmed') {
			$orderCol = 'sl.confirmed';
		}

		if (!empty($orderCol)) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
		//echo nl2br(str_replace('#__','php53_',$query)); die;
		$this->query = $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.0
	 */
	public function getSubscribersByList($params)
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		// SQL-query for gettting the users-subscibers list.
		$query->select(
			'DISTINCT a.subscriber_id AS id, a.name, a.email');

		$query->from(
			'(SELECT s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, u.id AS user_id, s.confirmed
			FROM #__newsletter_subscribers AS s
			LEFT JOIN #__users AS u ON (s.user_id = u.id)
			WHERE s.user_id = 0 OR u.id IS NOT NULL OR s.email != ""

			UNION

			SELECT s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, u.id AS user_id, s.confirmed
			FROM #__newsletter_subscribers AS s
			RIGHT JOIN #__users AS u ON (s.user_id = u.id)) AS a');

		$query->join('', "#__newsletter_sub_list AS sl ON a.subscriber_id=sl.subscriber_id");

		if (!empty($params['list_id'])) {

			$query->where('sl.list_id=' . intval($params['list_id']));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.name');
		$orderDirn = $this->state->get('list.direction', 'asc');

		if ($orderCol == 'a.ordering' || $orderCol == 'a.name') {
			$orderCol = 'name ' . $orderDirn . ', a.subscriber_id';
		}
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query)); die;
		$db->setQuery($query);
		return $db->loadObjectList();
	}



	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.0
	 */
	public function getUnsubscribedList($params)
	{
		if (!$this->state->get('list.unsubscribed.ordering')) {
			$orderCol = $this->state->set('list.unsubscribed.ordering', 'date');
		}

		if (!$this->state->get('list.unsubscribed.direction')) {
			$orderCol = $this->state->set('list.unsubscribed.direction', 'desc');
		}

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		// Select the required fields from the table.
		$query->select('a.*, h.date, h.text');

		$query->from(
			'(SELECT s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, u.id AS user_id, s.confirmed
			FROM #__newsletter_subscribers AS s
			LEFT JOIN #__users AS u ON (s.user_id = u.id)
			WHERE s.user_id = 0 OR u.id IS NOT NULL OR s.email != ""

			UNION

			SELECT s.subscriber_id, COALESCE(u.name, s.name) AS name, COALESCE(u.email, s.email) AS email, COALESCE(s.state, 1) AS state, u.id AS user_id, s.confirmed
			FROM #__newsletter_subscribers AS s
			RIGHT JOIN #__users AS u ON (s.user_id = u.id)) AS a');

		$query->join('', "#__newsletter_sub_history AS h ON a.subscriber_id=h.subscriber_id AND h.list_id='" . intval($params['list_id']) . "'");
		$query->where("action='" . intval(NewsletterTableHistory::ACTION_UNSUBSCRIBED) . "'");

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.unsubscribed.ordering');
		$orderDirn = $this->state->get('list.unsubscribed.direction');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		//echo nl2br(str_replace('#__','jos_',$query));
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
