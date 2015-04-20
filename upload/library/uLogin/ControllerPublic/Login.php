<?php

class uLogin_ControllerPublic_Login extends XenForo_ControllerPublic_Login {

	public function actionLogin()
	{
		if (isset($_POST['identity']))
		{
			$visitor = XenForo_Visitor::getInstance();
			$current_user = $visitor->getUserId();
			if ($current_user > 0) $this->ulogin_deleteaccount_request($_POST['identity']);
		}
		$this->uloginParseRequest();
		$redirect = $this->getDynamicRedirect();
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirect);
	}

	/**
	 * Обработка ответа сервера авторизации
	 */
	public function uloginParseRequest()
	{
		if (!isset($_POST['token'])) return;  // не был получен токен uLogin
		$s = $this->uloginGetUserFromToken($_POST['token']);
		if (!$s)
		{
			return $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin:Не удалось получить данные о пользователе с помощью токена.').$this->_get_back_url());
		}
		$u_user = json_decode($s, true);
		$u_user['nickname'] = isset($u_user['nickname']) ? $u_user['nickname'] : $u_user['nickname'] = '';
		$check = $this->uloginCheckTokenError($u_user);
		if (!$check)
		{
			return false;
		}
		$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
		$user_id = $uLoginModel->getUserIdByIdentity($u_user['identity']);
		if (isset($user_id))
		{
			$xf_user = $uLoginModel->getXenForoUser($user_id);
			if ($user_id > 0 && $xf_user > 0) $this->uloginCheckUserId($user_id);
			else
				$user_id = $this->uloginRegistrationUser($u_user, 1);
		}
		else
			$user_id = $this->uloginRegistrationUser($u_user);
		if ($user_id > 0) $this->loginCustomer($u_user, $user_id);
		return true;
	}

	private function _uploadAvatar($url)
	{
		global $fileDir;
		if (XenForo_Visitor::getInstance()->canUploadAvatar() && $url)
		{
			$visitor = XenForo_Visitor::getInstance();
			$sc = stream_context_create();
			$imagedump = file_get_contents($url, FILE_BINARY, $sc);
			if (!file_exists($fileDir.'/data/avatars/ulogin/'))
			{
				if (!file_exists($fileDir.'/data/avatars/'))
				{
					mkdir($fileDir.'/data/avatars/', 0777);
				}
				mkdir($fileDir.'/data/avatars/ulogin/', 0777);
			}
			$tmpfname = $fileDir.'/data/avatars/ulogin/'.md5(rand()).'.jpg';
			$fh = fopen($tmpfname, "w");
			fwrite($fh, $imagedump);
			if (file_exists($tmpfname))
			{
				$avatarModel = XenForo_Model_User::create('XenForo_Model_Avatar');
				$avatarData = $avatarModel->applyAvatar($visitor['user_id'], $tmpfname);
			}
			fclose($fh);
		}
	}

	/**
	 * Обменивает токен на пользовательские данные
	 *
	 * @param bool $token
	 *
	 * @return bool|mixed|string
	 */
	public function uloginGetUserFromToken($token = false)
	{
		$response = false;
		if ($token)
		{
			$data = array('cms' => 'xenForo', 'version' => XenForo_Application::$version,);
			$request = 'http://ulogin.ru/token.php?token='.$token.'&host='.$_SERVER['HTTP_HOST'].'&data='.base64_encode(json_encode($data));
			if (in_array('curl', get_loaded_extensions()))
			{
				$c = curl_init($request);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($c);
				curl_close($c);
			}
			elseif (function_exists('file_get_contents') && ini_get('allow_url_fopen')) $response = file_get_contents($request);
		}
		return $response;
	}

	/**
	 * Проверка пользовательских данных, полученных по токену
	 *
	 * @param $u_user - пользовательские данные
	 *
	 * @return bool
	 */
	public function uloginCheckTokenError($u_user)
	{
		if (!is_array($u_user))
		{
			throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin: Данные о пользователе содержат неверный
			 формат.').$this->_get_back_url());
		}
		if (isset($u_user['error']))
		{
			$strpos = strpos($u_user['error'], 'host is not');
			if ($strpos)
			{
				throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin: адрес хоста не совпадает с оригиналом').$this->_get_back_url());
			}
			switch ($u_user['error'])
			{
				case 'token expired':
					throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin: время жизни токена истекло').$this->_get_back_url());
				case 'invalid token':
					throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin: неверный токен').$this->_get_back_url());
				default:
					throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin:').$u_user['error'].$this->_get_back_url());
			}
		}
		if (!isset($u_user['identity']))
		{
			throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Ошибка работы uLogin: В возвращаемых данных отсутствует переменная
			 "identity"'.$this->_get_back_url()));
		}
		return true;
	}

	/**
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function uloginCheckUserId($user_id)
	{
		$visitor = XenForo_Visitor::getInstance();
		$current_user = $visitor->getUserId();
		if (($current_user > 0) && ($user_id > 0) && ($current_user != $user_id))
		{
			throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Данный аккаунт привязан к другому пользователю.
			Вы не можете использовать этот аккаунт').$this->_get_back_url());
		}
		return true;
	}

	/**
	 * Регистрация на сайте и в таблице uLogin
	 *
	 * @param Array $u_user - данные о пользователе, полученные от uLogin
	 * @param int $in_db - при значении 1 необходимо переписать данные в таблице uLogin
	 *
	 * @return bool|int|Error
	 */
	public function uloginRegistrationUser($u_user, $in_db = 0)
	{
		if (!isset($u_user['email']))
		{
			throw $this->getErrorOrNoPermissionResponseException(new XenForo_Phrase('Через данную форму выполнить регистрацию невозможно. Сообщите администратору сайта о следующей ошибке:
            Необходимо указать "email" в возвращаемых полях uLogin').$this->_get_back_url());
		}
		$u_user['network'] = isset($u_user['network']) ? $u_user['network'] : '';
		// данные о пользователе есть в ulogin_table, но отсутствуют в WP
		$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
		if ($in_db == 1) $abb = $uLoginModel->deleteUloginUser($u_user['identity']);
		$user_id = $uLoginModel->getUserByEmail($u_user['email']);
		// $check_m_user == true -> есть пользователь с таким email
		$check_m_user = $user_id > 0 ? true : false;
		$visitor = XenForo_Visitor::getInstance();
		$current_user = $visitor->getUserId();
		// $is_logged_in == true -> ползователь онлайн
		$is_logged_in = $current_user > 0 ? true : false;
		if (($check_m_user == false) && !$is_logged_in)
		{
			$userModel = $this->_getUserModel();
			if (isset($u_user['bdate']))
			{
				$dob = explode('.', $u_user['bdate']);
				$UserFields['dob_day'] = $dob[0];
				$UserFields['dob_month'] = $dob[1];
				$UserFields['dob_year'] = $dob[2];
			}
			else $u_user['bdate'] = '';
			if (isset($u_user['sex']))
			{
				$UserFields['gender'] = $u_user['sex'] == 1 ? 'female' : 'male';
			}
			$user_login = $this->ulogin_generateNickname($u_user['first_name'], $u_user['last_name'], $u_user['nickname'], $u_user['bdate']);
			$user_pass = XenForo_Application::generateRandomString(8);
			$UserFields['username'] = $user_login;
			$UserFields['email'] = $u_user['email'];
			$Password = $user_pass;
			$writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
			$writer->bulkSet($UserFields);
			$writer->setPassword($Password, $Password);
			$writer->set('user_group_id', XenForo_Model_User::$defaultRegisteredGroupId);
			$writer->set('language_id', XenForo_Visitor::getInstance()->get('language_id'));
			$writer->advanceRegistrationUserState(false);
			$writer->preSave();
			$writer->save();
			$user = $writer->getMergedData();
			$ul_writer = uLogin_DataWriter_User::create('uLogin_DataWriter_User');
			$ul_writer->set('userid', $user['user_id']);
			$ul_writer->set('identity', $u_user['identity']);
			$ul_writer->set('network', $u_user['network']);
			$ul_writer->save();

			XenForo_Model_Ip::log($user['user_id'], 'user', $user['user_id'], 'register');
			XenForo_Application::get('session')->changeUserId($user['user_id']);
			XenForo_Visitor::setup($user['user_id']);

			if(XenForo_Application::getOptions()->uLoginEmail == 1) {
				$this->sendEmail($user);
			}

			if (isset($u_user['photo'])) $u_user['photo'] = $u_user['photo'] === "https://ulogin.ru/img/photo.png" ? '' : $u_user['photo'];
			if (isset($u_user['photo_big'])) $u_user['photo_big'] = $u_user['photo_big'] === "https://ulogin.ru/img/photo_big.png" ? '' : $u_user['photo_big'];
			$this->_uploadAvatar((isset($u_user['photo_big']) and !empty($u_user['photo_big'])) ? $u_user['photo_big'] : ((isset($u_user['photo']) and !empty($u_user['photo'])) ? $u_user['photo'] : ''));


			return $user['user_id'];
		}
		else
		{ // существует пользователь с таким email или это текущий пользователь
			if (!isset($u_user["verified_email"]) || intval($u_user["verified_email"]) != 1)
			{
				throw $this->getErrorOrNoPermissionResponseException('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'")</script>'.new XenForo_Phrase("Электронный адрес данного аккаунта совпадает с электронным адресом существующего пользователя. Требуется подтверждение на владение указанным email.".$this->_get_back_url()));

			}
			if (intval($u_user["verified_email"]) == 1)
			{
				$user_id = $is_logged_in ? $current_user : $user_id;
				$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
				$other_u = $uLoginModel->getIdentityByUserId($user_id);
				if ($other_u)
				{
					if (!$is_logged_in && !isset($u_user['merge_account']))
					{
						throw $this->getErrorOrNoPermissionResponseException('<script src="//ulogin.ru/js/ulogin.js"  type="text/javascript"></script><script type="text/javascript">uLogin.mergeAccounts("'.$_POST['token'].'","'.$other_u['identity'].'")</script>'.new XenForo_Phrase("С данным аккаунтом уже связаны данные из другой социальной сети. Требуется привязка новой учётной записи социальной сети к этому аккаунту."));
					}
				}

				$ul_writer = uLogin_DataWriter_User::create('uLogin_DataWriter_User');
				$ul_writer->set('userid', $user_id['user_id']);
				$ul_writer->set('identity', $u_user['identity']);
				$ul_writer->set('network', $u_user['network']);
				$ul_writer->save();

				$userModel = $this->_getUserModel();
				XenForo_Model_Ip::log($user_id['user_id'], 'user', $user_id['user_id'], 'login');
				$userModel->deleteSessionActivity(0, $this->_request->getClientIp(false));
				$session = XenForo_Application::get('session');
				$session->changeUserId($user_id['user_id']);
				XenForo_Visitor::setup($user_id['user_id']);
				return $user_id;
			}
		}
		return false;
	}

	/**
	 * Отправка письма на почту при регистрации с паролем и логином
	 */

	protected function sendEmail($user)
	{
		$uLoginModel = XenForo_Model_User::create('XenForo_Model_UserConfirmation');
		$userId = $uLoginModel->resetPassword($user);
	}

	/**
	 * Обновление данных о пользователе и вход
	 *
	 * @param $u_user - данные о пользователе, полученные от uLogin
	 * @param $id_customer - идентификатор пользователя
	 *
	 * @return string
	 */
	public function loginCustomer($u_user, $id_customer)
	{
		$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
		$userId = $uLoginModel->getUserById($id_customer);

		if ($userId['userid'])
		{
			$userModel = $this->_getUserModel();
			XenForo_Model_Ip::log($userId['userid'], 'user', $userId['userid'], 'login');
			$userModel->deleteSessionActivity(0, $this->_request->getClientIp(false));
			$session = XenForo_Application::get('session');
			$session->changeUserId($userId['userid']);
			XenForo_Visitor::setup($userId['userid']);
			return true;
		}
		else return false;
	}

	/**
	 * Гнерация логина пользователя
	 * в случае успешного выполнения возвращает уникальный логин пользователя
	 *
	 * @param $first_name
	 * @param string $last_name
	 * @param string $nickname
	 * @param string $bdate
	 * @param array $delimiters
	 *
	 * @return string
	 */
	public function ulogin_generateNickname($first_name, $last_name = "", $nickname = "", $bdate = "", $delimiters = array('.', '_'))
	{
		$delim = array_shift($delimiters);
		$first_name = $this->ulogin_translitIt($first_name);
		$first_name_s = substr($first_name, 0, 1);
		$variants = array();
		if (!empty($nickname)) $variants[] = $nickname;
		$variants[] = $first_name;
		if (!empty($last_name))
		{
			$last_name = $this->ulogin_translitIt($last_name);
			$variants[] = $first_name.$delim.$last_name;
			$variants[] = $last_name.$delim.$first_name;
			$variants[] = $first_name_s.$delim.$last_name;
			$variants[] = $first_name_s.$last_name;
			$variants[] = $last_name.$delim.$first_name_s;
			$variants[] = $last_name.$first_name_s;
		}
		if (!empty($bdate))
		{
			$date = explode('.', $bdate);
			$variants[] = $first_name.$date[2];
			$variants[] = $first_name.$delim.$date[2];
			$variants[] = $first_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$date[2];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$date[2];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$delim.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$delim.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$delim.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$delim.$first_name_s.$delim.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$date[2];
			$variants[] = $first_name_s.$last_name.$delim.$date[2];
			$variants[] = $first_name_s.$last_name.$date[0].$date[1];
			$variants[] = $first_name_s.$last_name.$delim.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$date[2];
			$variants[] = $last_name.$first_name_s.$delim.$date[2];
			$variants[] = $last_name.$first_name_s.$date[0].$date[1];
			$variants[] = $last_name.$first_name_s.$delim.$date[0].$date[1];
		}
		$i = 0;
		$exist = true;
		while (true)
		{
			if ($exist = $this->ulogin_userExist($variants[$i]))
			{
				foreach ($delimiters as $del)
				{
					$replaced = str_replace($delim, $del, $variants[$i]);
					if ($replaced !== $variants[$i])
					{
						$variants[$i] = $replaced;
						if (!$exist = $this->ulogin_userExist($variants[$i])) break;
					}
				}
			}
			if ($i >= count($variants) - 1 || !$exist) break;
			$i++;
		}
		if ($exist)
		{
			while ($exist)
			{
				$nickname = $first_name.mt_rand(1, 100000);
				$exist = $this->ulogin_userExist($nickname);
			}
			return $nickname;
		}
		else
			return $variants[$i];
	}

	/**
	 * Транслит
	 */
	public function ulogin_translitIt($str)
	{
		$tr = array("А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i", "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya");
		if (preg_match('/[^A-Za-z0-9\_\-]/', $str))
		{
			$str = strtr($str, $tr);
			$str = preg_replace('/[^A-Za-z0-9\_\-\.]/', '', $str);
		}
		return $str;
	}

	/**
	 * Проверка существует ли пользователь с заданным логином
	 */
	function ulogin_userExist($login)
	{
		$uLoginModel = XenForo_Model_User::create('XenForo_Model_User');
		if ($uLoginModel->getUserByNameOrEmail($login) === false)
		{
			return false;
		}
		return true;
	}

	/**
	 * Возвращает Back url в html формате
	 */
	function _get_back_url()
	{
		$back = base64_decode($_GET['back']);
		$back = isset($back) ? $back : $this->getDynamicRedirect();
		$backURL = '<br/><a href="'.$back.'">'.new XenForo_Phrase('Назад').'</a>';
		return $backURL;
	}

	/**
	 * Удаление привязок к аккаунтам социальных сетей
	 */
	function ulogin_deleteaccount_request($identity)
	{
		try
		{
			$uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
			$uLoginModel->deleteUloginUser($identity);
			echo json_encode(array('answerType' => 'ok', 'identity' => $identity));
			exit;
		} catch (Exception $e)
		{
			echo json_encode(array('title' => "Ошибка при удалении аккаунта", 'msg' => "Exception: ".$e->getMessage(), 'answerType' => 'error'));
			exit;
		}
	}
}

?>