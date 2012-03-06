<?php
class uLogin_ControllerPublic_Login extends XenForo_ControllerPublic_Login{
    public function actionLogin(){
        if (isset($_POST['token'])){
            $data = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
            $user_raw = json_decode($data);
            if (!isset($user_raw->error)){
                $uLoginModel = XenForo_Model_User::create('uLogin_Model_User');
                $userId = $uLoginModel->getUserIdByIdentity($user_raw->identity);
                if ($userId['uid']){
                    $userId = $userId['uid'];
                    $userModel = $this->_getUserModel();
                    
                    XenForo_Model_Ip::log($userId, 'user', $userId, 'login');
                    $userModel->deleteSessionActivity(0, $this->_request->getClientIp(false));
                    $session = XenForo_Application::get('session');
                    $session->changeUserId($userId);
                    XenForo_Visitor::setup($userId);
                    $this->_uploadAvatar($user_raw->photo);
                }else{
                    $userModel = $this->_getUserModel();
                    
                    $dob = explode('.', $user_raw->bdate);
                    $UserFields['dob_day'] = $dob[0];
                    $UserFields['dob_month'] = $dob[1];
                    $UserFields['dob_year'] = $dob[2];
                    $UserFields['gender'] = $user_raw->sex == 1 ? 'female' : 'male';
                    $UserFields['username'] = isset($user_raw->nickname) ? substr($user_raw->nickname.' '.  time(), 0,24) : substr($user_raw->first_name.' '.time(),0,24);
                    
                    while($userModel->getUserByName($UserFields['username'])){
                        $UserFields['username'] = isset($user_raw->nickname) ? substr($user_raw->nickname.' '.  time(), 0,24) : substr($user_raw->first_name.' '.time(),0,24);
                    }
                    
                    $UserFields['email'] = strpos($user_raw->manual,'email')===FALSE ? time().'_'.$user_raw->email: time().'_umf_'.$user_raw->email;
                    while($userModel->getUserByEmail($UserFields['email'])){
                        $UserFields['email'] = strpos($user_raw->manual,'email')===FALSE ? time().'_'.$user_raw->email: time().'_umf_'.$user_raw->email;
                    }
                    
                    $Password = md5($user_raw->identity.time().$user_raw->network);
                    
                    $ul_writer = uLogin_DataWriter_User::create('uLogin_DataWriter_User');
                    $writer = XenForo_DataWriter::create('XenForo_DataWriter_User');
                    $writer->bulkSet($UserFields);
                    $writer->setPassword($Password, $Password);
                    $writer->set('user_group_id', XenForo_Model_User::$defaultRegisteredGroupId);
                    $writer->set('language_id', XenForo_Visitor::getInstance()->get('language_id'));
                    $writer->advanceRegistrationUserState(false);
                    $writer->preSave();
                    $writer->save();
                    
                    $user = $writer->getMergedData();
                    $ul_writer->set('uid',$user['user_id']);
                    $ul_writer->set('identity',$user_raw->identity);
                    $ul_writer->save();
                    
                    XenForo_Model_Ip::log($user['user_id'], 'user', $user['user_id'], 'register');
                    XenForo_Application::get('session')->changeUserId($user['user_id']);
                    XenForo_Visitor::setup($user['user_id']);
                    $viewParams = array(
                            'user' => $user
                    );
                    $this->_uploadAvatar($user_raw->photo);
                }
            }
        }
        $redirect =  $this->getDynamicRedirect();
        return $this->responseRedirect(
                        XenForo_ControllerResponse_Redirect::SUCCESS,
			$redirect
        );
    }
    
    private function _uploadAvatar($url){
        if (XenForo_Visitor::getInstance()->canUploadAvatar()){
            $visitor = XenForo_Visitor::getInstance();
            $sc = stream_context_create();
            $imagedump = file_get_contents($url, FILE_BINARY, $sc);
            $tmpfname = tempnam("/tmp", "FOO");
            $fh = fopen($tmpfname, "w");
            fwrite($fh, $imagedump);           
            $avatar = new XenForo_Upload($url, $tmpfname);
            if ($avatar){
                $avatarModel = $this->getModelFromCache('XenForo_Model_Avatar');
                $avatarData = $avatarModel->uploadAvatar($avatar, $visitor['user_id'], $visitor->getPermissions());
            }
            fclose($fh);
        }
    }
}

?>
