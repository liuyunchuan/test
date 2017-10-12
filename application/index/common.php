<?php
// index模块公共方法

// 登录时设置session信息 
function set_session_login( $id, $appid, $appsecret, $token, $username='', $nickname = '', $address )
{
        header("Content-type:text/html;charset=UTF-8");
	session( 'id', $id );
	session( 'appid', $appid );
	session( 'token', $token );
	session( 'address', $address );
	session( 'company', $nickname );
	session( 'username', $username );
	session( 'appsecret', $appsecret );
	// echo $address;
	// echo $nickname;
	// die;
	return true;
}