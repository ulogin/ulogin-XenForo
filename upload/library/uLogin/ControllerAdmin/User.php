<?php
class uLogin_ControllerAdmin_User extends XFCP_uLogin_ControllerAdmin_User
{
    public function actionDelete()
    {
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
	$user = $this->_getUserOrError($userId);

        $ul_writer = uLogin_DataWriter_User::create('uLogin_DataWriter_User');
        $ul_model = $ul_writer->getModelFromCache('uLogin_Model_User' );
        
        $ul_user = $ul_model->getUserById($userId);
        if($ul_user){
            $ul_writer->setExistingData($ul_user);
            $ul_writer->preDelete();
        }
        
	$this->getHelper('Admin')->checkSuperAdminEdit($user);

	if ($this->isConfirmedPost())
	{
            if ($ul_user){
                $ul_writer->delete();
            }
            
            return $this->_deleteData(
                        'XenForo_DataWriter_User', 'user_id',
			XenForo_Link::buildAdminLink('users')
            );
        }
	else 
        {
            $viewParams = array(
            'user' => $user
            );

            return $this->responseView('XenForo_ViewAdmin_User_Delete',
                                        'user_delete', $viewParams
                    );
        }
    }

}
?>
