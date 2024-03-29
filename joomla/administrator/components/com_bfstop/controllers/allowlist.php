<?php
/*
 * @package BFStop Component (com_bfstop) for Joomla! >=2.5
 * @author Bernhard Froehler
 * @copyright (C) Bernhard Froehler
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

class BFStopControllerAllowList extends JControllerAdmin
{
	public function getModel($name = 'allowlist', $prefix = 'bfstopmodel', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	public function remove()
	{
		$logger = getLogger();
		$input =  JFactory::getApplication()->input;
		$ids = $input->post->get('cid', array(), 'array');
		Joomla\Utilities\ArrayHelper::toInteger($ids);
		$model = $this->getModel('allowlist');
		$message = $model->remove($ids, $logger);
		$this->setRedirect(JRoute::_('index.php?option=com_bfstop&view=allowlist',false),
			$message, 'notice');
	}
}
