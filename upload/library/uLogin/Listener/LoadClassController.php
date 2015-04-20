<?php

class uLogin_Listener_LoadClassController {
	public static function loadClassListener($class, &$extend)
	{
		if ($class == 'XenForo_ControllerAdmin_User')
		{
			$extend[] = 'uLogin_ControllerAdmin_User';
		}
	}
}

?>
