<?php
namespace app\home\controller;
https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx505dd6360d392f46&redirect_uri=http://4006719008.net/tp5/public/index.php/home/Wenjuan/shouqiancheck&response_type=code&scope=snsapi_base&state=1#wechat_redirect
class Wenjuan extends Base
{
	public function _initialize()
	{
		// 判断是否是微信浏览器上使用
		// $wxid = check_Weixin();
		// if( !$wxid )
		// {
		// 	$wxid = connect_Weixin();
		// 	if( !$wxid )
		// 	{
		// 		// 关闭页面
		// 	}
		// }
		$code  = input( 'get.code' );
		// echo $member['template2'];die;

	    // die;
	    // 拼接url地址
	    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx505dd6360d392f46&secret=945f3a5f91863a00cbadb69c82ad6096&code='.$code.'&grant_type=authorization_code';
	    // echo $url;
	    // curl请求
	    $jsoncode   = get_contents( $url );
	    // 解析json数据包
	    $returncode = json_decode( $jsoncode, true );
	    // print_r( $returncode);
	    // 从数组中获取openid，并且避免刷新出现空数组的错误
	    $openid = isset( $returncode['openid']) ? trim( $returncode['openid'] ) : '';
	    // echo $openid;die;
	    // 判断获取openid是否成功
	    // if( $openid != '' )
	    // {
	        // 设置session
	    session( 'openid', $openid );
	    return $openid;
		// die;
	}
	// 研习会问卷
	public function index()
	{
		// 获取研习会当前的设置
		$settings = get_yanxihui_settings();
		// 获取问卷是否已经填写
		$wenjuan = db( 'Wenjuan' )->where( array( 'openid'=>session( 'openid' ), 'type'=>1, 'qi'=>$settings['qi'] ) )->find();
		
		if( Request()->isPost() )
		{
			// 获取当前时间
			$nowtime = time();
			if( $nowtime > $settings['end_time'] )
			{
				// 问卷提交时间大于问卷截止提交时间，提示超过了提交时间
				$this->assign( 'title', '调查问卷已经结束' );
				$this->assign( 'message', '很遗憾，您没能按时提交问卷<br/>如果想要了解更多，可以联系管理员');
				return $this->fetch( 'error' );
			}
			// 判断问卷是否已经提交
			if( $wenjuan )
			{
				// 问卷已经提交，请不要重复提交
				// 已经填写了调查问卷，显示调查问卷的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} else {
				// 问卷还没有提交，进行提交数据的操作
				// 获取表单提交的数据
				$data = [
					'q1' => input( 'post.q1' ),
					'q2' => input( 'post.q2' ),
					'q3' => input( 'post.q3' ),
					'q4' => input( 'post.q4' ),
					'q5' => input( 'post.q5' ),
					'q6' => input( 'post.q6' ),
					'q7' => input( 'post.q7' ),
					'q8' => input( 'post.q8' ),
					'type'  => 1,
					'time'  => time(),
					'openid'=> session( 'openid' ),
					'qi'    => $settings('qi'),
				];
				// print_r( $data );
				// 把数据插入到数据库中
				$res = db( 'wenjuan' )->insert( $data );
				if( $res )
				{
					// 问卷信息保存成功，返回抽奖页面
					return $this->success( '问卷提交成功', 'chou');
				} else{
					// 问卷信息保存失败，返回之前页面
					return $this->error( '问卷提交失败，请稍后再试');
				}
			}
		} else {
			
			// 判断是否在问卷的填写时间内
			
			$nowtime = time();
			// echo $nowtime."<br>";
			// echo $settings['start_time'];die;

			if( $nowtime < $settings['start_time'] )
			{
				// 当前时间小于问卷开始时间，显示问卷还没有开始
				$this->assign( 'title', '调查问卷还没有开始' );
				$this->assign( 'message', '如果想要了解更多，可以联系管理员');
				return $this->fetch( 'error' );
			}
			if( $nowtime > $settings['end_time'] )
			{
				// 当前时间大于问卷结束时间
				// 判断时候有填写调查问卷
				if( $wenjuan )
				{
					// 已经填写了调查问卷，显示调查问卷的详细信息
					$this->assign( 'jiang', $wenjuan['jiang'] );
					$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
					return $this->fetch( 'show' );
				} else {
					// 没有填写调查问卷，显示问卷调查已经结束页面
					$this->assign( 'title', '调查问卷已经结束' );
					$this->assign( 'message', '如果想要了解更多，可以联系管理员');
					return $this->fetch( 'error' );
				}
			}
			// 当前时间在填写问卷调查的时间内
			if( $wenjuan )
			{	
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjaun['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show2' );
			} else {
				// 没有填写调查问卷，显示填写调查问卷的页面
				return $this->fetch( 'index' );
			}
		}
	}
	// 售前问卷
	public function shouqian()
	{
		// 获取售前问卷当前设置
		$settings = get_shouqian_settings();
		// 判断售前问卷的状态是否为禁用，如果是，提示当前问卷状态为禁用
		if( $settings['status'] == 0 )
		{
			return "当前问卷状态为禁用，如有需要，请联系管理员";
		}
		// 获取问卷是否已经填写 有待考虑
		$wenjuan = db( 'Wenjuan' )->where( [ 'phone'=>input( 'post.phone'), 'type'=>2 ] )->order( 'id desc' )->find();
		// print_r($wenjuan);exit;
		if( Request()->isPost() )
		{
			// 判断问卷是否已经填写
			if( $wenjuan && $wenjuan['flag'] == 1 )
			{
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} else {
				// 问卷还没有提交，进行提交数据的操作
				// 获取表单提交的数据
				$data = [
					'q1' => input( 'post.q1' ),
					'q2' => input( 'post.q2' ),
					'q3' => input( 'post.q3' ),
					'q4' => input( 'post.q4' ),
					'q5' => input( 'post.q5' ),
					'q6' => input( 'post.q6' ),
					'flag'  => 1,
					'type'  => 2,
					'time'  => time(),
					'openid'=> session( 'openid' ),
					'id'    => $wenjuan['id'],
				];
				// print_r( $data );
				// print_r( input('post.phone'));
				// die;
				// 把数据插入到数据库中
				$res = db( 'wenjuan' )->update( $data );
				if( $res )
				{
					// 问卷信息保存成功，返回抽奖页面
					return $this->success( '问卷提交成功', 'chou');
				} else{
					// 问卷信息保存失败，返回之前页面
					return $this->error( '问卷提交失败，请稍后再试');
				}
			}
		} else {
			if( $wenjuan && $wenjuan['flag'] == 1)
			{
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} else {
				// 没有填写调查问卷，显示填写调查问卷的页面
				return $this->fetch();
			}
		}
	}
	// 售前问卷验证时候是受邀请用户
	public function shouqiancheck()
	{
		// return $this->redirect( 'chou' );
		// return $this->redirect( 'show' );
		if( Request()->isPost() )
		{
			// 获取表单提交的数据
			$data['phone'] = input( 'phone' );
			// 查看是否是受邀请用户
			$wenjuan = db( 'wenjuan' )->where( [ 'type'=>2, 'phone'=>$data['phone'] ] )->order( 'id desc' )->find();
			// print_r($wenjuan);
			if( !$wenjuan )
			{
				// 没有查找到信息，显示不是受邀请用户
				$this->assign( 'title', '售前支持服务满意度调查');
				$this->assign( 'message', '您还没有收到邀请<br>如有需要，请联系工作人员');
				return $this->fetch('error');
			} elseif( $wenjuan['flag'] == 1 ) {
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} elseif( $wenjuan['flag'] == 0 ) {
				// 没有填写调查问卷，显示填写调查问卷的页面
				$this->assign( 'phone', $data['phone'] );
				return $this->fetch( 'shouqian' );
			} else {
				// 未知参数
				$this->assign( 'title', '售前支持服务满意度调查');
				$this->assign( 'message', '未知参数<br>如有需要，请联系工作人员');
				return $this->fetch('error');
			}
		} else {
			return $this->fetch();
		}
	}
	// 售后问卷验证时候是受邀请用户
	public function shouhoucheck()
	{
		if( Request()->isPost() )
		{
			// 获取表单提交的数据
			$data['phone'] = input( 'phone' );
			// print_r( $data['phone'] );
			// 查看是否是受邀请用户
			$wenjuan = db( 'wenjuan' )->where( [ 'type'=>3, 'phone'=>$data['phone'] ] )->order( 'id desc' )->find();
			// print_r($wenjuan);
			if( !$wenjuan )
			{
				// 没有查找到信息，显示不是受邀请用户
				$this->assign( 'title', '售后支持服务满意度调查');
				$this->assign( 'message', '您还没有收到邀请<br>如有需要，请联系工作人员');
				return $this->fetch('error');
			} elseif( $wenjuan['flag'] == 1 ) {
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} elseif( $wenjuan['flag'] == 0 ) {
				// 没有填写调查问卷，显示填写调查问卷的页面
				$this->assign( 'phone', $data['phone'] );
				return $this->fetch( 'shouhou' );
			} else {
				// 未知参数
				$this->assign( 'title', '售后支持服务满意度调查');
				$this->assign( 'message', '未知参数<br>如有需要，请联系工作人员');
				return $this->fetch('error');
			}
		} else {
			return $this->fetch();
		}
	}
	// 售后问卷
	public function shouhou()
	{
		// 获取售后问卷当前设置
		$settings = get_shouhou_settings();
		// 判断售前问卷的状态是否为禁用，如果是，提示当前问卷状态为禁用
		if( $settings['status'] == 0 )
		{
			return "当前问卷状态为禁用，如有需要，请联系管理员";
		}
		// 获取问卷是否已经填写 有待考虑
		$wenjuan = db( 'Wenjuan' )->where( [ 'phone'=>input( 'post.phone' ), 'type'=>3 ] )->order( 'id desc' )->find();

		if( Request()->isPost() )
		{
			// 判断问卷是否已经给您填写
			if( $wenjuan && $wenjuan['flag'] == 1 )
			{
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} else {
				// 问卷还没有提交，进行提交数据的操作
				// 获取表单提交的数据
				$data = [
					'q1' => input( 'post.q1' ),
					'q2' => input( 'post.q2' ),
					'q3' => input( 'post.q3' ),
					'q4' => input( 'post.q4' ),
					'q5' => input( 'post.q5' ),
					'q6' => input( 'post.q6' ),
					'type'  => 3,
					'time'  => time(),
					'openid'=> session( 'openid' ),
				];
				// print_r( $data );
				// die;
				// 把数据插入到数据库中
				$res = db( 'wenjuan' )->insert( $data );
				if( $res )
				{
					// 问卷信息保存成功，返回抽奖页面
					return $this->success( '问卷提交成功', 'chou');
				} else{
					// 问卷信息保存失败，返回之前页面
					return $this->error( '问卷提交失败，请稍后再试');
				}
			}
		} else {
			if( $wenjuan )
			{
				// 已经填写了调查问卷，显示奖品的详细信息
				$this->assign( 'jiang', $wenjuan['jiang'] );
				$this->assign( 'title', '你已经填写问卷，请不要重复填写' );
				return $this->fetch( 'show' );
			} else {
				// 没有填写调查问卷，显示填写调查问卷的页面
				return $this->fetch();
			}
		}
	}

	// 抽奖方法
	public function chou()
	{
		// 获取研习会当前的设置
		$settings = get_yanxihui_settings();
		// 获取用户信息
		// session('null');
		// echo "sfsf";
		$wenjuan = db( 'wenjuan' )->where( [ 'openid' =>session( 'openid' ), 'type'=>1, 'qi'=>$settings['qi'] ] )->order( 'id desc' )->find();
		// 判断用户类型 研习会 售前 售后
		if( !$wenjuan )
		{
			// 获取不到问卷记录，不是研习会
			$wenjuan = db( 'wenjuan' )->where( [ 'openid'=>session( 'openid' ), 'type'=>2 ] )->order( 'id desc' )->find();
			if( !$wenjuan )
			{
				// 获取不到售前问卷记录，不是售前
				$wenjuan = db( 'wenjuan' )->where( [ 'openid'=>session( 'openid' ), 'type'=>3 ] )->order( 'id desc' )->find();
				chouapi( 3, $wenjuan['id'] );
			} else {
				// 问卷记录是售前
				chouapi( 2, $wenjuan['id'] );
			}
		} else {
			// 问卷记录是研习会
			// chouapi( 1, $wenjuan['id'] );
		}
		// print_r( session( 'openid') );
		// print_r( $wenjuan );die;
		// 获取中奖信息
		$list = db( 'wenjuan' )->where( [ 'id'=>$wenjuan['id'] ] )->order( 'id desc' )->find();
		$this->assign( 'id', $list['jiangid'] );
		$this->assign( 'jiang', $list['jiang'] );
		return $this->fetch();
	}

	// 展示抽奖结果页面
	public function show()
	{
		// 获取研习会当前的设置
		$settings = get_yanxihui_settings();
		// 获取用户的研习会获奖信息
		$wenjuan = db( 'wenjuan' )->where( array( 'openid'=>session( 'openid' ), 'type'=>1, 'qi'=>$settings['qi'] ) )->order( 'id desc' )->find();
		// 判断用户类型 研习会 售前 售后
		if( !$wenjuan )
		{
			// 获取不到问卷记录，不是研习会
			$wenjuan = db( 'wenjuan' )->where( array( 'openid'=>session( 'openid' ), 'type'=>2 ) )->order( 'id desc' )->find();
			if( !$wenjuan )
			{
				// 获取不到售前问卷记录，不是售前
				$wenjuan = db( 'wenjuan' )->where( array( 'openid'=>session( 'openid' ), 'type'=>3 ) )->order( 'id desc' )->find();
			}
			$title = '请联系工作人员兑换奖品';
		} else {
			$title = '请联系研习会主持人<br>兑换奖品';
		}
		$this->assign( 'jiang', $wenjuan['jiang'] );
		// $this->assign( 'jiang', 'lizhi' );
		$this->assign( 'title', $title);
		return $this->fetch();
	}

	// 抽奖接口
	public function chouapi( $type=0, $id=0 )
	{
		if( $id == '' )
		{
			// echo "参数错误";
			return false;
			die;
		}
		switch ( $type ) {
			case '1':
				chouyanxihui( $id );
				break;			
			case '2':
				choushouqian( $id );
				break;			
			case '3':
				choushuohou( $id );
				break;			
			default:
				echo "参数错误";
				break;
		}
		return;
	}

	public function chouyanxihui1( $id = 0 )
	{
		if( $id == 0)
		{
			// 参数错误
			return false;
		}
		$data['id'] = $id;
		// 首先要获取奖品1/2的奖品信息
		$list = db( 'wenjuansettings' )->where( 'type', 1 )->order( 'id desc' )->limit( 1 )->find();
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
				$data['jiang']   = $list['jiang_1_name'];
				// echo "中奖，奖品1";
			} 
			// else {
			// 	//奖品1已经没有了
			// 	echo "奖品1已经没有了";
			// } 
		} elseif( $p < $list['jiang_1_winning'] + $list['jiang_2_winning']) {
			if( $list['jiang_1_rest'] > 0 )
			{
				// 中奖，奖品2
				$num = $list['jiang_1_rest'] % 4;
				$data['jiangid'] = $jiang2[$num];
				$data['jiang']   = $list['jiang_2_name'];
				// echo "中奖，奖品2";
			} 
			// else {
			// 	// 奖品2已经没有了
			// 	echo "奖品2已经没有了";
			// }
		} 
		// else {
		// 	// 没有中奖
		// 	echo "没有中奖";
		// }
		if( !isset($data['jiangid']) )
		{
			$data['jiangid'] = 3;
			$data['jiang']   = '谢谢参与';
		}
		$res = db( 'wenjuan' )-> update( $data );
		if( $res )
		{
			return true;
		} else {
			return false;
		}
	}
	
}