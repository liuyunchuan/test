<?php
namespace app\index\controller;

class Log extends Base
{
	// 登录系统
	public function login()
	{
		if( Request()->isPost() )
		{
			// 获取登录信息
			$data = [
				'username' => input( 'post.username' ),
				'password' => input( 'post.password' ),
			];
			// 查询登录信息
			$member = db( 'member' )->where( 'username', $data['username'] )->find();
			// 判断用户名是否正确
			if( $member )
			{
				// 用户名正确，进一步比对密码
				if( $member['password'] == $data['password'] )
				{
					// 密码正确，进一步判断该用户是否是禁用状态
					if( $member['status'] != 1 ){
						// 该用户处于禁用状态
						$this->error( '用户已经被禁用' );
					} else {
						// 验证成功，设置session信息
						set_session_login( $member['id'], $member['appid'], $member['appsecret'], $member['token'], $member['username'], $member['nickname']);
						// 跳转到后台主页面
						$this->success( '登录成功', 'index/index' );
					}
				} else {
					// 密码错误
					$this->error( '用户名或密码错误' );
				}
			} else {
				// 用户名不存在
				$this->error( '用户不存在' );
			}
			
		} else {
			// 显示登录页面
			return $this->fetch();		
		}
	}

	// 退出系统
	public function logout()
	{
		// 设置session值为空
		session(null);
		if( session('id') === null )
		{
			// 退出系统成功，跳转到登录页面
			$this->success( '退出系统成功，期待您的再次使用', 'login' );
		}
	}

}
