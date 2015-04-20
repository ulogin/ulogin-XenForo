<?php

class uLogin_Listener_Profile {

	public static function template_profile($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if ($hookName == 'ulogin')
		{
			$paths = XenForo_Application::get('requestPaths');
			$params = $template->getParams();
			$panel = XenForo_Model::create('uLogin_Model_uloginModel')->getPanelCode(1);
			$params += array('panel' => $panel);
			$mergedParams = array_merge($params, $hookParams);
			$uLoginTemplate = $template->create('ulogin', $mergedParams);
			$rendered = $uLoginTemplate->render();
			$contents .= $rendered;
		}
		if ($hookName == 'page_container_head')
		{
			$contents .= '<script src="http://ulogin.ru/js/ulogin.js"></script>';
			$contents .= '<style>.ulogin_network { display: inline-block;margin-right: 5px; }</style>';
		}
		if ($hookName == 'account_personal_details_status')
		{
			global $fileDir;
			$visitor = XenForo_Visitor::getInstance();
			$current_user = $visitor['user_id'];
			$panel = XenForo_Model::create('uLogin_Model_uloginModel')->getPanelCode();
			if ($current_user > 0)
			{
				$syncpanel = uLogin_Listener_Profile::getuloginUserAccountsPanel();
				$oldcontents = $contents;
				$contents = '
<link type="text/css" rel="stylesheet" href="https://ulogin.ru/css/providers.css">
<script src="js/uLogin/ajax.js"></script>
<dl class="ctrlUnit">
			<dt><label for="ctrl_status">Синхронизация аккаунтов:</label></dt>
			<dd>
                '.$panel.'
				<div class="explain"><h3 class="statusHeader">Привяжите ваши аккаунты соц. сетей к личному кабинету для быстрой авторизации через любой из них</h3>
			</dd>
		</dl>
		<dl class="ctrlUnit">
			<dt><label for="ctrl_status">Привязанные аккаунты:</label></dt>
			<dd>
                <div id="ulogin_synchronisation">'.$syncpanel.'</div>
				<div class="explain"><h3 class="statusHeader">Вы можете удалить привязку к аккаунту, кликнув по значку</h3>
			</dd>
		</dl>';
				$contents .= '<fieldset>'.$oldcontents.'</fieldset>';
			}
		}
	}

	/**
	 * Вывод списка аккаунтов пользователя
	 *
	 * @param int $user_id - ID пользователя (если не задан - текущий пользователь)
	 *
	 * @return string
	 */
	static function getuloginUserAccountsPanel($user_id = 0)
	{
		$visitor = XenForo_Visitor::getInstance();
		$current_user = $visitor['user_id'];
		$user_id = empty($user_id) ? $current_user : $user_id;
		if (empty($user_id)) return '';
		$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
		$networks = array();
		$networks = $uLoginModel->getAllUsersById($user_id);
		$output = '';
		if ($networks)
		{
			$output .= '<div id="ulogin_accounts">';
			foreach ($networks as $network)
			{
				$network['identity'] = urlencode($network['identity']);
				if ($network['userid'] = $user_id) $output .= "<div data-ulogin-network='{$network['network']}'  data-ulogin-identity='{$network['identity']}' class='ulogin_network big_provider {$network['network']}_big'></div>";
			}
			$output .= '</div>';
			return $output;
		}
		return '';
	}
}

?>