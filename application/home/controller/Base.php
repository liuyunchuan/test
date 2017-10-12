<?php
namespace app\home\controller;

use think\Controller;

class Base extends Controller
{
	/*微信基类默认执行方法*/
    public function _initialize()
    {
        if( input('flag') == 1) return;
        // echo "ed";die;
    	// 获取session中的openid
    	// $openid = get_session_openid();
        // echo $openid;exit;
    	// if( !$openid )
    	// {
    		// 获取用户OpenId，并且设置session
        	$openid = get_openid();
    		// if( !$openid ) ;// 获取不到微信id调整到错误页面
    	// }
        // echo session( 'openid' );
        // echo "dd";
        // die;
    	// echo $openid;die;
    	// session(null);
    }
}
