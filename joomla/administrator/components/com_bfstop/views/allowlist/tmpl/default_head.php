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
	<th width="20">
		<input
			type="checkbox"
			name="toggle"
			value=""
			onclick="checkAll(<?php echo count($this->items); ?>);"
		/>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_ID',
			'b.id',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_IPADDRESS',
			'b.ipaddress',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
	<th>
		<?php echo JHTML::_('grid.sort',
			'COM_BFSTOP_HEADING_NOTES',
			'b.notes',
			$this->sortDirection,
			$this->sortColumn); ?>
	</th>
</tr>
