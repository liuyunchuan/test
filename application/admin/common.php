<?php
// index模块公共方法

// 登录时设置session信息 
function set_session_login( $id, $appid, $appsecret, $token, $username='', $nickname = '' )
{
	session( 'id', $id );
	session( 'appid', $appid );
	session( 'token', $token );
	session( 'nickname',$nickname );
	session( 'username', $username );
	session( 'appsecret', $appsecret );
	return true;
}