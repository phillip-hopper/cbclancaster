<?php
/**
* @file
* @brief    sigplus Image Gallery Plus CSS dimension value control
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2023 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see https://www.gnu.org/licenses/gpl-3.0.html
* @see      https://hunyadi.info.hu/projects/sigplus
*/

/*
* sigplus Image Gallery Plus plug-in for Joomla
* Copyright 2009-2023 Levente Hunyadi
*
* sigplus is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* sigplus is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'compat.php';

jimport('joomla.form.fields.text');

use Joomla\CMS\Language\Text;

/**
* Renders a control for specifying a CSS dimension value.
* This class implements a user-defined control in the administration backend.
*/
class JFormFieldDimension extends SigPlusNovoFormTextField {
	protected $type = 'Dimension';

	protected function getInput() {
		// initialize some field attributes
		$size = $this->element['size'] ? ' size="'.(int)$this->element['size'].'"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$placeholder = ' placeholder="'.Text::_($this->element['placeholder'] ? $this->element['placeholder'] : 'JDEFAULT').'"';

		// initialize JavaScript field attributes
		$onchange = $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		return '<input'.
			' type="text"'.
			' name="'.$this->name.'"'.
			' id="'.$this->id.'"'.
			' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"'.
			' class="'.($this->element['class'] ? (string)$this->element['class'] : 'form-control').'"'.
			$size.$placeholder.$disabled.$readonly.$onchange.$maxLength.
		'/>';
	}
}
