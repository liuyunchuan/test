<?php
namespace app\home\controller;

class Tecsun extends Base
{
	// 访客点击员工通道时展示的页面
	public function index()
	{
		return $this->fetch();
	}

	// 邀请访客成功时展示的页面，访客不同登录
	public function reservation()
	{
		header("Content-Type:text/html;charset=utf-8");
		$id = input('id');
		$record = db( 'record' )->where( 'id', $id )->find();
		$recordlog = db( 'recordlog' )->where( 'id', $id )->find();
		// print_r( $recordlog );
		// die;
		// $record = db( 'record' )->alias( 'r' )->where( [ 'id'=>$id ] )->join( 'recordlog l', 'r.id = l.id' )->find();
		// $record = db( 'record' )->alias( 'r' )->where( [ 'id'=>input('id') ] )->join( 'recordlog l', 'r.id=$id' )->find();
		// $record = db( 'record' )->alias( 'r' )->where( [ 'openid'=>session( 'openid' ), 'token'=>session( 'token' ) ] )->order( 'r.id desc' )->limit( 3 )->join( 'recordlog l', 'r.id = l.id')->select();

		// print_r($record);die;
		$this->assign( 'record', $record );
		$this->assign( 'recordlog', $recordlog );
		return $this->fetch();
		// die;
	}
}