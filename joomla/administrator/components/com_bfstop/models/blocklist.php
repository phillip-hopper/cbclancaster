<?php
/*
 * @package BFStop Component (com_bfstop) for Joomla! >=2.5
 * @author Bernhard Froehler
 * @copyright (C) Bernhard Froehler
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

require_once(JPATH_ADMINISTRATOR.'/components/com_bfstop/helpers/unblock.php');

class BFStopModelBlockList extends JModelList
{
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'b.id',
			'b.ipaddress',
			'b.crdate',
			'b.duration',
			'unblocked'
		);
		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('b.id, b.ipaddress, b.crdate, b.duration, u.crdate as unblocked');
		$query->from('#__bfstop_bannedip b left join #__bfstop_unblock u on b.id=u.block_id');
		$ordering  = $this->getState('list.ordering', 'b.id');
		$ordering  = (strcmp($ordering, '') == 0) ? 'b.id' : $ordering;
		$direction = $this->getState('list.direction', 'ASC');
		$direction = (strcmp($direction, '') == 0) ? 'ASC' : $direction;
		$query->order($db->escape($ordering).' '.$db->escape($direction));
		return $query;
	}

	public function getItems()
	{
		$result = parent::getItems();
		return $result;
	}

	protected function populateState($ordering = null, $direction = null) {
		parent::populateState('b.crdate', 'DESC');
	}

	public function unblock($ids, $logger)
	{
		if (BFStopUnblockHelper::unblock(JFactory::getDBO(), $ids, 0, $logger)) {
			return JText::_("UNBLOCK_SUCCESS");
		} else {
			return JText::_("UNBLOCK_FAILED");
		}
	}

	public function getTotal()
	{
		return parent::getTotal();
	}
}
