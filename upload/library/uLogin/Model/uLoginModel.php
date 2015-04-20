<?php

class uLogin_Model_uLoginModel extends XenForo_Model {
	public function getPanelCode($place = 0)
	{
		/*
		 * Выводит в форму html для генерации виджета
		 */
        $paths = XenForo_Application::get('requestPaths');
        $redirect_uri = urlencode($paths['fullBasePath'].XenForo_Link::buildPublicLink('ulogin').(!preg_match('/\.php\?(login|ulogin|register)/i', $paths['fullUri']) ? '&back='.base64_encode($paths['fullUri']) : ''));

        $ulogin_default_options = array();
		$ulogin_default_options['display'] = XenForo_Application::getOptions()->ulogin_display;
		$ulogin_default_options['providers'] = XenForo_Application::getOptions()->ulogin_shown_providers;
		$ulogin_default_options['fields'] = XenForo_Application::getOptions()->ulogin_fields;
		$ulogin_default_options['optional'] = XenForo_Application::getOptions()->ulogin_optional;
		$ulogin_default_options['hidden'] = XenForo_Application::getOptions()->ulogin_hidden_providers;


		$ulogin_options = array();
		$ulogin_options['ulogin_id1'] = XenForo_Application::getOptions()->uloginID1;
		$ulogin_options['ulogin_id2'] = XenForo_Application::getOptions()->uloginID2;

		$default_panel = false;

		switch ($place)
		{
			case 0:
				$ulogin_id = $ulogin_options['ulogin_id1'];
				break;
			case 1:
				$ulogin_id = $ulogin_options['ulogin_id2'];
				break;
			default:
				$ulogin_id = $ulogin_options['ulogin_id1'];
		}
		if (empty($ulogin_id))
		{
			$ul_options = $ulogin_default_options;
			$default_panel = true;
		}

		XenForo_Helper_File::getExternalDataPath();

		$panel = '';
		$panel .= '<div id="uLoginBar" class="ulogin_panel"';
		if ($default_panel)
		{
			$ul_options['redirect_uri'] = $redirect_uri;
			unset($ul_options['label']);
			$x_ulogin_params = '';
			foreach ($ul_options as $key => $value) $x_ulogin_params .= $key.'='.$value.';';
			if ($ul_options['display'] != 'window') $panel .= ' data-ulogin="'.$x_ulogin_params.'"></div>';
			else
				$panel .= ' data-ulogin="'.$x_ulogin_params.'" href="#"><img src="https://ulogin.ru/img/button.png" width=187 height=30 alt="МультиВход"/></div>';
		}
		else
			$panel .= ' data-uloginid="'.$ulogin_id.'" data-ulogin="redirect_uri='.$redirect_uri.'"></div>';
		$panel = '<div class="ulogin_block place'.$place.'">'.$panel.'</div><div style="clear:both"></div>';

        return $panel;
	}
}

?>