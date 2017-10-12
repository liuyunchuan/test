<?php
namespace app\index\controller;

use think\Controller;

class Base extends Controller
{
	/*前台基类默认执行方法*/
	public function _initialize()
	{
		$request= \think\Request::instance();
		if( $request->controller() != 'Log')
		{
			//判断是否有登录
			if( session('id') === null )
			{
				// 还没有登录系统，跳转到登录页面
				$this->redirect( 'log/login' );
			}
		}
		
	}

}
