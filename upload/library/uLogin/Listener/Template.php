<?php
class uLogin_Listener_Template{
    public static function createTemplate($templateName, array &$params, XenForo_Template_Abstract $template)
	{
		if ($templateName == 'ulogin')
		{
                    $paths = XenForo_Application::get('requestPaths');
			$mergedParams = array_merge($template->getParams(), $params);
			$redirect_uri = urlencode($paths['fullBasePath'] . XenForo_Link::buildPublicLink('ulogin') . (!preg_match('/\.php\?(login|ulogin|register)/i', $paths['fullUri']) ? '&back=' . base64_encode($paths['fullUri']) : ''));
			$template->preloadTemplate('ulogin');
		}
	}
	
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if ($hookName == 'ulogin')
		{
			$paths = XenForo_Application::get('requestPaths');
			$mergedParams = array_merge($template->getParams(), $hookParams);
			$redirect_uri = urlencode($paths['fullBasePath'] . XenForo_Link::buildPublicLink('ulogin') . (!preg_match('/\.php\?(login|ulogin|register)/i', $paths['fullUri']) ? '&back=' . base64_encode($paths['fullUri']) : ''));
			$contents .= $template->create('ulogin', array_merge($mergedParams, array('redirect_uri' => $redirect_uri)));
		}
	}
}
?>
