<?php
namespace app\index\controller;

// 公司账号信息
class Member extends Base
{

	public function memberDetail()
	{
		$member = db( 'member' )->where( [ 'token'=>session('token') ] )->find();
		if( Request()->isPost() )
		{
			$data = [
				'id'       => $member['id'],
				'username' => input('username'),
				'password' => input('password'),
				'nickname' => input('nickname'),
				'address'  => input('address'),
			];
			$res = db( 'member' )->update( $data );
			if( $res !== null )
			{
				session( 'username', $data['username'] );
				session( 'nickname', $data['nickname'] );
				session( 'address' , $data['address'] );
				$this->redirect('memberDetail');
			} else {
				$this->error('修改失败');				
			}
		} else {
			// print_r($member);
			// $this->assign( 'username', session( 'username') );
			$this->assign( 'list', $member );
			return $this->fetch();
		}
			
	}
}