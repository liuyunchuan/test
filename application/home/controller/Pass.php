<?php 
namespace app\home\controller;

class Pass extends Base
{
	public function pass( $id )
	{
		// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Visitor/results', [ 'message'=>"参数异常，请稍后再试", "key"=>"id不是数字" ] );
    	$record = db( 'record' )->find( $id );

    	$record['sex'] = ( $record['sex'] == 0 ) ? '女' : ( ( $record['sex'] == 1 ) ? '男' : '未选择' );
    	$record['visittype'] = ( $record['visittype'] == 0 ) ? '访客预约' : '员工邀请' ;
		$record['accompanying'] = ( $record['accompanying'] == 0 ) ? '若干人' : $record['accompanying'];

 		$tr = '<!doctype html><html><head><meta charset="utf-8"></head><body><div style="font-size:50px;margin:10px 0 0 10px;">';
		$tr .= '<ul style="list-style-type:none">';
		$tr .= '<li id="cardId">二维码单号：' . $id . '</li>';
		$tr .= '<li id="bvisitor">被访人：' . $record['ename'] . '</li>';
		$tr .= '<li id="visitor">来访人：' . $record['name'] . '</li>';
		$tr .= '<li id="visitorSex">来访人性别：' . $record['sex'] . '</li>';
		$tr .= '<li id="address">见面地址：' . $record['address'] . '</li>';
		// $tr .= '<li id="idcard">来访人身份证号：' . $record['idcard'] . '</li>';
		$tr .= '<li id="bphone">被访人手机：' . $record['ephone'] . '</li>';
		$tr .= '<li id="phone">来访人手机：' . $record['phone'] . '</li>';
		// $tr .= '<li id="danwei">来访人单位：' . $record['company'] . '</li>';
		$tr .= '<li id="account">事由：' . $record['account'] . '</li>';
		$tr .= '<li id="starttime">生效时间：' . date('Y-m-d H:i:s', $record['start_time']) . '</li>';
		$tr .= '<li id="endtime">过期时间：' . date('Y-m-d H:i:s', $record['end_time']) . '</li>';
		$tr .= '<li id="type">客户类型：' . $record['visittype'] . '</li>';
		$tr .= '</ul></div></body></html>';
    	
    	echo $tr;
    	
	}
}