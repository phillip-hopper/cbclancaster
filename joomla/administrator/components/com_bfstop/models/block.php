<?php
/*
 * @package BFStop Component (com_bfstop) for Joomla! >=2.5
 * @author Bernhard Froehler
 * @copyright (C) Bernhard Froehler
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class BFStopModelBlock extends JModelAdmin
{
	public function getTable($type = 'Block', $prefix = 'BFStopTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_bfstop.block', 'block',
			array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_bfstop.edit.block.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}
}
