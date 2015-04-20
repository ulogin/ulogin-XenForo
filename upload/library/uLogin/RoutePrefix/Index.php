<?php
class uLogin_RoutePrefix_Index implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('uLogin_ControllerPublic_Login', 'login', $routePath);
	}
}

?>
