<?php
namespace app\index\controller;

class Wall extends Base
{
	public function index()
	{
		return $this->fetch();
	}
	public function index1()
	{
		return $this->fetch();
	}
	public function index2()
	{
		return $this->fetch();
	}

	// 前端获取消息接口
	public function message( )
	{
		// $lastId = (int)input( 'get.lastId' );
		$message = db( 'message' )->where( 'status = 0' )->limit( 1 )->select();
		foreach ( $message as $k ) {
			if( empty($k) ) continue;
			$data = [
				'id' => $k['id'],
				'status' => 1,
			];
			db( 'message' )->update( $data );
		}
		$json_message = json_encode( $message );
		return $json_message;
	}

	// 便捷插入数据
	public function add()
	{
		for($i=0;$i<100;$i++)
		{
			$data['openid']  = 'oDwcNwObUxMiq5M';
			$data['content'] = '消息'.$i;
			db( 'message' )->insert( $data );
		}
	}
}