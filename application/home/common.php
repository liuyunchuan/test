<?php 

// home控制器公共方法
// 抽奖接口
function chouapi( $type=0, $id=0 )
{
	if( $id == '' )
	{
		// echo "参数错误";
		return false;
		die;
	}
	switch ( $type ) {
		case '1':
			$list = db( 'wenjuansettings' )->where( 'type', 1 )->order( 'id desc' )->limit( 1 )->find();
			chou( $list, $id );
			break;			
		case '2':
			$list = db( 'wenjuansettings' )->where( 'type', 2 )->order( 'id desc' )->limit( 1 )->find();
			chou( $list, $id );
			break;			
		case '3':
			$list = db( 'wenjuansettings' )->where( 'type', 3 )->order( 'id desc' )->limit( 1 )->find();
			chou( $list, $id );
			break;			
		default:
			echo "参数错误";
			break;
	}
	return;
}

function chou( $list=[], $id = 0 )
{
	if( $list == [] || $id == 0 )
	{
		// 参数错误
		return false;
	}
	$data['id'] = $id;
	// 首先要获取奖品1/2的奖品信息
	// $list = db( 'wenjuansettings' )->where( 'type', 1 )->order( 'id desc' )->limit( 1 )->find();
	$jiang0 = [3];
	$jiang1 = [1,5,7];
	$jiang2 = [2,4,6,8];
	// print_r( $list );
	$p = rand( 0, 100 );
	// echo $p."<br>";
	if( $p < $list['jiang_1_winning'] )
	{
		if( $list['jiang_1_rest'] > 0 )
		{
			// 中奖，奖品1
			$num = $list['jiang_1_rest'] % 3;
			$data['jiangid'] = $jiang1[$num];
			$list['jiang_1_rest']--;
			$data['jiang']   = $list['jiang_1_name'];
		} 

	} elseif( $p < $list['jiang_1_winning'] + $list['jiang_2_winning']) {
		if( $list['jiang_2_rest'] > 0 )
		{
			// 中奖，奖品2
			$num = $list['jiang_2_rest'] % 4;
			$data['jiangid'] = $jiang2[$num];
			$list['jiang_2_rest']--;
			$data['jiang']   = $list['jiang_2_name'];
		} 
	} 

	if( !isset($data['jiangid']) )
	{
		$data['jiangid'] = 3;
		$data['jiang']   = '谢谢参与';
	}
	$re = db( 'wenjuansettings' )->update( $list );
	$res = db( 'wenjuan' )-> update( $data );
	if( $re || $res )
	{
		return true;
	} else {
		return false;
	}
}