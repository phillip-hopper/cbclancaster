<?php
/**
* @file
* @brief    sigplus Image Gallery Plus definitions for Joomla 3/4/5 compatibility
* @author   Levente Hunyadi
* @version  1.5.0
* @remarks  Copyright (C) 2009-2023 Levente Hunyadi
* @remarks  Licensed under GNU/GPLv3, see https://www.gnu.org/licenses/gpl-3.0.html
* @see      https://hunyadi.info.hu/projects/sigplus
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.fields.text');

if (version_compare(JVERSION, '4.0') >= 0) {
	class SigPlusNovoFormField extends Joomla\CMS\Form\FormField { }
	class SigPlusNovoFormTextField extends Joomla\CMS\Form\Field\TextField { }
} else {
	class SigPlusNovoFormField extends JFormField { }
	class SigPlusNovoFormTextField extends JFormFieldText { }
}
