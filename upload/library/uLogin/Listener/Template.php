<?php
class uLogin_Listener_Template{
    public static function createTemplate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'ulogin')
		{
			$template->preloadTemplate('ulogin');
		}
	}
}
?>
