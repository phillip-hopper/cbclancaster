<?php

namespace CBCLancaster\Component\Newsletter\Site\View\Latest;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
	public function display($tpl = null)
	{
		$path = JPATH_BASE . '/files/newsletters/*';
		$latest_ctime = 0;
		$latest_filename = '';

		$files = glob($path);
		foreach($files as $file) {

			if (is_file($file) && filectime($file) > $latest_ctime) {
				$latest_ctime = filectime($file);
				$latest_filename = $file;
			}
		}

		if (empty($latest_filename))
			exit();


		header('Content-Type: application/pdf');
		//header('Content-Disposition: attachment; filename=CovenantConnect.pdf');
		header('Content-Disposition: inline; filename=CovenantConnect.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($latest_filename));

		readfile($latest_filename);

		exit();
	}
}
