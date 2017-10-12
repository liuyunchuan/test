<?php
namespace app\index\controller;

class Reasons extends Base
{
	/*来访事由*/
	public function visitingReason()
	{
		// 判断是否是系统管理员
		if( session('id')==0 )
		{
			// 是系统管理员，查询所有的来访事由
			$reasons = db( 'config' )->where( [ 'name'=>'VISIT_REASONS' ] )->order( 'token desc' )->select();
		} else {
			// 不是系统管理员，查询该用户下的来访事由
			$reasons = db( 'config' )->where( [ 'name'=>'VISIT_REASONS', 'token'=>session('token') ] )->order( 'token desc' )->select();
			// 判断该用户是否已经设置来访事由
			// if( !$reasons )
			// {	
				// 该用户没有设置来访事由，查询默认的来访是由于，token=''
				// 这样会存在一个问题，底层用户可以删除默认的来访事由，还是不要显示了
				// $reasons = db( 'config' )->where( [ 'name'=>'VISIT_REASONS','token'=>'' ] )->order( 'token desc' )->select();
			// }
		}
		$this->assign( 'list', $reasons );
		return $this->fetch();
	}

	// 添加来访事由
	public function reasonsAdd()
	{
		if( Request()->isPost() )
		{
			// 获取表单数据
			$data = [
				'value' => input('post.reason'),
				'name'  => 'VISIT_REASONS',
				'token' => session('token'),
				'title' => '来访事由',
			];
			// 查询该设置是否已经存在
			$reasons = db( 'config' )->where( $data )->find();
			if( $reasons )
			{
				// 已经有了相同的配置
				$this->error( '已经有了相同的配置，请核实后再添加');
			}
			// 添加来访事由到该用户
			$res = db( 'config' )->insert( $data );
			if( $res )
			{
				// 添加来访事由成功
				$this->success( '添加来访事由成功', 'visitingReason' );
			} else {
				// 添加来访事由失败
				$this->error( '添加来访事由失败' );
			}
		} else {
			return $this->fetch();
		}
	}

	// 查看来访事由详细信息
	public function reasonsDetail()
	{
		// 获取要查询数据的id
		$id      = input('id');
		// 根据id查询数据
		$reasons = db( 'config' )->where( ['id' => $id ] )->find();
		$this->assign( 'list', $reasons );
		return $this->fetch();
	}

	// 删除来访事由
	public function reasonsDel()
	{
		// 获取批量查询的id
		$id[] = input('post.id/a');
		// 根据id是否为空判断是否是批量删除操作
		if( $id[0] )
		{
			// 这是通过批量Ajax删除
			for( $i=0 ; $i<count($id[0]) ; $i++ )
			{
				// 把二维数组转化为一维数组
				$data = [ 'id' => $id[0][$i] ];
				// 批量删除数据
				$dels = db( 'config' )->delete( $data );
			}
			// 把删除数据的id输出 这个有待考虑
			print_r( $dels );
		} else {
			// 这是通过单个a标签进行删除
			$id  = input('id');
			$res = db( 'config' )->delete( $id );
			return $this->redirect( 'visitingReason' );
		}
	}

	// 清空所有来访事由
	public function reasonsDelAll()
	{
		// 判断是否是系统管理员
		if( session('id') != 0 )
		{
			// 不是系统管理员
			$map['token'] = session('token');
		}
		$map['id'] = ['>' ,1];
		$map['name'] = 'VISIT_REASONS'; 
		$delid = db( 'config' )->where( $map )->column( 'id' );
		$dels  = db( 'config' )->delete( $delid );
		print_r( $dels );
	}
}
