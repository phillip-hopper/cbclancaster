<?php
/*
 * @package BFStop Component (com_bfstop) for Joomla! >=2.5
 * @author Bernhard Froehler
 * @copyright (C) Bernhard Froehler
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;
?>
<tr class="sortable">
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_ID',
			'l.id',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_IPADDRESS',
			'l.ipaddress',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_DATE',
			'l.logtime',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_USERNAME',
			'l.username',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_ORIGIN',
			'l.origin',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
</tr>
