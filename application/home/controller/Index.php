<?php
namespace app\home\controller;

class Index extends Base
{
	// 预约操作
    public function yuyue()
    {
    	// 获取用户openid
        $openid = session( 'openid' );
        // 查找用户信息
        $user = db( 'user' )->where( 'openid', $openid )->find();
       	// 查找最近一次预约记录 
       	$record = db( 'record' )->where( 'vid', $user['id'] )->order('id desc' )->find();
        if(Request()->isPost())
        {
        	header("Content-Type:text/html;charset=UTF-8");
        	// 获取预约数据
        	$data = [
        		'start_time'  => input('post.start_time'),
        		'ephone'      => input('post.hisphone'),
        		'accompanying'=> input('post.accompanying'),
        		'account' 	  => input('post.account'),
        		'car_num'     => input('post.car_num'),
        		];
                // echo session('token');die;
        	// 根据提交的手机号获取员工信息，并判断该员工是否是可以被预约的状态
        	$employee = db( 'user' )->where( [ 'phone'=>$data['ephone'], 'token'=>session('token') ] )->find() or $this->error( '不存在预约对象' );
        	// 该手机号码已经解绑，不能对其进行预约
            if( $employee['status'] != 1 ) $this->error( '该手机号码已经解绑，不能对其进行预约' );
        	// 普通访客不能是被访问对象
            if( $employee['type'] == 0 ) $this->error( '普通访客不能是被访问对象 ');
        	// 设置公众号的appid,appsecret session
        	// set_session_token( $employee['token'] ) or $this->redirect( 'Index/results', ['message'=>'参数异常，请稍后再试','设置公众号的appid,appsecret的session失败']);
        	// 获取访客信息
        	$visitor = db( 'user' )->where( 'openid', session( 'openid' ) )->find() or $this->redirect( 'Index/results', ['message'=>'参数异常，请稍后再试', 'key'=>'获取访客信息失败']);

        	/*编辑插入记录的信息*/
        	$add_data['vid'] 		  = $visitor['id'];					// 访客id
        	$add_data['evid'] 		  = $employee['id'];				// 员工id
        	$add_data['company']      = $employee['company'];			// 员工所在公司
        	$add_data['visittype']    = 0;								// 预约发起类型
        	$add_data['token']        = $employee['token'];				// 所在公众号token
        	$add_data['accompanying'] = $data['accompanying'];			// 来访人数
        	$add_data['car_num']      = $data['car_num'];				// 车牌号码
        	$add_data['phone']        = $visitor['phone'];				// 访客手机
        	$add_data['ephone'] 	  = $data['ephone'];				// 员工手机
        	$add_data['openid'] 	  = session( 'openid' );			// openid
        	$add_data['account'] 	  = $data['account'];				// 来访事由
        	$add_data['name'] 	  	  = $visitor['name'];				// 访客姓名
        	$add_data['ename'] 	      = $employee['name'];				// 员工姓名
        	$add_data['address']      = $employee['address'];			// 见面地址
        	$add_data['create_time']  = time();							// 创建时间
        	$add_data['start_time']   = strtotime($data['start_time']); // 预约时间
        	$add_data['end_time']     = $add_data['start_time'] + 60*60*4;	// 截止时间

        	// 插入预约记录
        	$res = db( 'record' )->insertGetId( $add_data );
        	// 给员工发送模板消息
        	if(false !== $res )
        	{
                // print_r(session( 'template1' ) );die;
        		$data = [
        			'touser'      => $employee['openid'],
        			'template_id' => session( 'template1' ),
        			'url'         => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".session('appid')."&redirect_uri=".session( 'web_url' )."/public/index.php/home/Index/detail/id/$res.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
        			'data' => [
        				'first' => [
							'value' => '预约申请',
							'color' => '#173177',
						],
						'keyword1' => [
							'value' => $add_data['name'],
							'color' => '#173177',
						],
						'keyword2' => [
							'value' => $add_data['phone'],
							'color' => '#173177',
						],
						'keyword3' => [
							'value' => date('Y-m-d H:i:s',$add_data['start_time']),
							'color' => '#173177',
						],
						'remark' => [
							'value' => "来访人数：".$add_data['accompanying'].
							"\n预约事由：".$add_data['account'].
							"\n车牌号码：".$add_data['car_num'],
							'color' => '#173177',
						],
        			],
        		];
    			// 发送模板消息
    			$res = sendWeixinMessage( $data );
                // print_r($res);die;
    			// 发送成功时调整页面
    			if(!empty( $res )) return $this->fetch( 'yuyued' );
        	}
        	$this->error( '预约失败，请稍后再试' );
        }else {
			// 用户存在，并且状态为正常使用，status 0：禁用 1：正常使用 2：已经解绑 -1：删除状态
			if( $user != null && $user['status'] == 1)
			{
				$this->assign( 'status', 1 );
			    // 应该有更多的处理操作
			} else {
				$this->assign( 'status', -1 );
			}
			$this->assign( 'user', $user );
			$this->assign( 'record', $record );
			// $this->meta_title = "微信预约";
			return $this->fetch();
    	}
    }

    // 预约详情
    public function detail( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Index/results', ['message'=>'参数异常', 'key'=>'id不是数字'] );
    	// 根据id获取预约记录
    	$record = db( 'record' )->where( 'id', $id )->find();
    	// 若没有查找到记录，跳转到错误提示页面
        $record or $this->redirect( 'Index/results', ['message'=>'参数异常', 'key'=>"没有相关记录"] );
    	// 这里不需要做预约记录是否处理的操作，交给前端页面处理
    	$this->assign( 'list', $record );
    	return $this->fetch();
    }

    // 同意预约
    public function agree( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Index/results', ['message'=>"参数异常，请稍后再试", "key"=>"id不是数字"] );
    	// 根据id获取预约记录
    	$record = db( 'record' )->where( 'id', $id )->find();
    	// 若没有查找到记录，跳转到错误提示页面
        $record or $this->redirect( 'Index/results', ['message'=>"没有相关记录"] );
    	// 判断该条预约记录是否已经处理,如果已经处理跳转到详情页面
    	if( $record['status'] != 0 )
    	{
			$this->assign( 'list', $record );
	    	return $this->fetch( 'detail' );
    	}
    	// 根据id查找员工信息
    	$employee = db( 'user' )->where( 'id', $record['evid'])->find();
    	// 若没有查找到员工，跳转到错误提示页面
    	$employee or $this->redirect( 'Index/results', ['message'=>"参数异常，请稍后再试", "key"=>"没有找到员工信息"] );
    	// 设置公众号的appid,appsecret session
       	// 若设置token的session失败，跳转到错误页面
       	set_session_token( $employee['token'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"设置token的session失败"] );
       	
    	// 修改记录的状态为已允许
    	$data = [
    		'id' => $id,
    		'status' => 1,
    	];
    	// 判断修改记录状态是否成功
    	db( 'record' )->update( $data ) !== false or  $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"修改记录的状态失败"] );
    	// 修改来访人数
    	$record['accompanying'] = empty( $record['accompanying'] ) || $record['accompanying'] == 0 ? '若干' : $record['accompanying'];
    	 // 编辑发送模板的数据包
    	$senddata = array(
    	 	'touser' 	  => $record['openid'],
			'template_id' => 'gBrGIJ4x9sW3FNb_R6Vb6Fshzwjj5_OSqoWg-mYf7yQ',
			'url'         => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.session( 'appid' ).'&redirect_uri=http://139.199.181.219/public/index.php/home/index/my_current/id/'.$id.'.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect',
			'topcolor' => '#FF0000',
			'data'     => array(
				'first' => array(
					'value' => '我发出的预约确认通知',
					'color' => '#173177',
				),
				'keyword1' => array(
					'value' => '访客预约',
					'color' => '#173177',
				),
				'keyword2' => array(
					'value' => date( 'Y-m-d H:i:s', $record['start_time'] ),
					'color' => '#173177',
				),
				'keyword3' => array(
					'value' => $record['address'],
					'color' => '#173177',
				),
				'remark' => array(
					'value' => "访客姓名：".$record['name'].
					"\n来访事由：".$record['account'],
					'color' => '#173177',
				),
			),
    	);
    	// 发送模板消息
    	sendWeixinMessage( $senddata ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"发送模板消息失败"] );

    	// 生成二维码
    	$url     = 'http://'.$_SERVER['SERVER_NAME'] . url( 'Pass/pass', array( 'id'=> $id ) );
    	$url_img = 'http://'.$_SERVER['SERVER_NAME'] . '/' . createQR( $url );
        //这个地方还需要判断一下，包括在方法里面也有做好优化，避免程序发生错误
    	// 编辑成功记录的数据包
    	$data = array(
    		'id' 		=> $id,
    		'start_time'=> $record['start_time'],
    		'end_time'  => $record['end_time'],
    		'url' 	    => $url,
    		'url_img'   => $url_img,
    	);
    	if( db( 'recordlog' )->insert($data) )
    	{
    		// 把数据发送到接口 身份证、访客单位、访客姓名、访客手机、访客性别、二维码号、员工姓名、员工手机、见面地址、开始时间、截止时间、来访事由、客户类型、来访人数、车牌号码
    		webservice( $record['idcard'], '', $record['name'], $record['phone'], '', '', $record['ename'], $record['ephone'], $record['address'], $record['start_time'], $record['end_time'], $record['account'], $record['visittype'], $record['accompanying'], $record['car_num'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"调用webservice接口失败"] );
    		// 发送成功，跳转到展示页面
    		return $this->redirect( 'agreed' );
    		// 跳转页面
    	} else {
    		return $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"插入成功预约记录失败"] );
    	}
    }

    //拒绝预约
    public function refuse( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Index/results', ['message'=>"参数异常，请稍后再试", "key"=>"id不是数字"] );
    	// 根据id获取预约记录，若没有查找到记录，跳转到错误提示页面
    	$record = db( 'record' )->where( 'id', $id )->find() or $this->redirect( 'Index/results', ['message'=>"参数异常，请稍后再试", 'key'=>"没有相关记录"] );
    	// 判断该条预约记录是否已经处理,如果已经处理跳转到详情页面
    	if( $record['status'] != 0 )
    	{
			$this->assign( 'list', $record );
	    	return $this->fetch( 'detail' );
    	}

    	if( Request()->isPost() )
    	{
    		// 判断拒绝理由是否为空
    		if( trim( input( 'post.content' ) ) == '' ) $this->error( '拒绝理由不能为空' );
    		// 编辑修改记录的状态为已拒绝
    		$recorddata = [
    			'id'     => $id,
    			'status' => 2,
    		];
    		// 编辑拒绝的详细信息
    		$data = [
				'id'   => $id,
				'evid' => $record['evid'],
				'time' => time(),
				'content' => input( 'post.content' ),
    		];
    		// 修改记录状态
    		$res = db( 'record' )->update( $recorddata ) or $this->redirect( 'Index/results', ['message'=>'操作异常，请稍后再试', 'key'=>'修改记录状态失败'] );
    		if( $res !== false )
    		{
    			// 添加拒绝记录
    			if( db( 'refuse' )->insert( $data ) )
    			{
    				$senddata = array(
    					'touser'      => $record['openid'],
						'template_id' => 'gBrGIJ4x9sW3FNb_R6Vb6Fshzwjj5_OSqoWg-mYf7yQ',
						'url'         => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.session( 'appid' ).'&redirect_uri=http://139.199.181.219/public/index.php/home/index/refusedetail/id/'.$id.'.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect',
						'topcolor' => '#FF0000',
						'data' => array(
							'first' => array(
								'value' => '对方拒绝了您的邀请',
								'color' => '#173177',
							),
							'keyword1' => array(
								'value' => $record['name'],
								'color' => '#173177',
							),
							'keyword2' => array(
								'value' => $record['phone'],
								'color' => '#173177',
							),
							'keyword3' => array(
								'value' => time( 'Y-m-d H:i:s', $record['start_time'] ),
								'color' => '#173177',
							),
							'remark' => array(
								'value' => "拒绝原因：".$data['content'],
								'color' => '#173177',
							),
						),
    				);
    				sendWeixinMessage( $senddata ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", 'key'=>'修改记录状态失败'] );
    				// 操作成功，跳转到拒绝成功展示页面
    				return $this->fetch( 'refused' );
    			} 
    		}
    		$this->error( '操作失败' );
    		die;
    	} else {
    		$this->assign( 'id', $id );
    		return $this->fetch();
    	}
    }

    // 异常展示页面
    public function results( $message, $key )
    {
    	// 把key值写到log文件
    	
    	$this->assign( 'message', $key );
    	return $this->fetch();
    }

    // 获取访客的预约记录
    public function records()
    {
    	// 获取用户openid
        $openid = session( 'openid' );
        // 这里要不要判断是否已经是访客
        // 查找访客预约记录
        $record = db( 'record' )->where( 'openid', $openid )->order('id desc')->limit(5)->select();
        // print_r($record);
        $this->assign( 'list', $record );
        return $this->fetch();
    }

    // 添加访客信息·访客首次预约的时候会使用到
    public function add()
    {
    	if(Request()->isPost())
    	{
            // 获取表单数据
    		$data = [
    			'name'   => input( 'yourname' ),
    			'phone'  => input( 'yourphone' ),
    			'idcard' => input( 'idcard' ),
    			'sex'    => input( 'sex' ),
    			'openid' => session( 'openid' ),
    			'status' => 1,
    			'type'   => 0,
    			'company'=> input( 'yourunit' ),
                'token'  => session( 'token' ),
    			'create_time' => time(),
    		];
            // print_r( session( 'token' ));die;
            // 返回标志，0：添加成功，1：身份证号码和手机号码都已经存在，2：手机号码已经存在，3：身份证号码已经存在
            // 判断身份证号码是否为空
    		if( $data['idcard'] != '')
    		{
                // 身份证号码不为空，比对手机号码和身份证号码时候已经存在
    			$info_phone  = db( 'user' )->where( [ 'phone' => $data['phone'],   'token' => $data['token'] ] )->find();
    			$info_idcard = db( 'user' )->where( [ 'idcard' => $data['idcard'], 'token' => $data['token'] ] )->find();
    			if( $info_phone && $info_idcard )
    			{
    				return "flag1";
    				exit();
    			}
    			if( $info_phone )
    			{
    				return "flag2";
    				exit();
    			}
    			if( $info_idcard )
    			{
    				return "flag3";
    				exit();
    			}
    		} else {
                // 身份证号码为空，比对手机号码
    			$info_phone = db( 'user' )->where( [ 'phone' => $data['phone'], 'token' => $data['token'] ] )->find();
    			if( $info_phone )
    			{
    				return "flag2";
    				exit();
    			}
    		}
            // 手机号码和身份证号码没有在该公众号上没有出现重复的信息，添加访客信息
    		if( db( 'user' )->insert( $data ) ) return "flag0";
    	}
    }

    // 修改访客信息
    public function edits()
    {
    	if( Request()->isPost() )
    	{
    		// 获取表单数据
            $data = [
    			'name'   => input('yourname'),
    			'phone'  => input('yourphone'),
    			'idcard' => input('idcard'),
    			'sex'    => input('sex'),
                'company'=> input( 'yourunit' ),
                'openid' => session( 'openid' ),
    		];
            // 返回标志，0：修改成功，1：身份证号码和手机号码都已经存在，2：手机号码已经存在，3：身份证号码已经存在，4：信息没有修改
            // 判断身份证号码是否为空
            if( $data['idcard'] != '' )
            {
               $info_phone = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>session( 'token' ), 'openid'=>[ 'neq', $data['openid'] ] ] )->find();
               $info_idcard = db( 'user' )->where( [ 'idcard'=>$data['idcard'], 'token'=>session( 'token' ), 'openid'=>[ 'neq', $data['openid'] ] ] )->find();
               if( $info_phone && $info_idcard )
                {
                    return "flag1";
                    exit();
                }
                if( $info_phone )
                {
                    return "flag2";
                    exit();
                }
                if( $info_idcard )
                {
                    return "flag3";
                    exit();
                }
            } else {
                $info_phone = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>session( 'token' ), 'openid'=>[ 'neq', $data['openid'] ] ] )->find();
                if( $info_phone )
                {
                    return "flag2";
                    exit();
                }
            }
            // 更新访客信息
            if( $res = db( 'user' )->where( [ 'openid'=>$data['openid'] ] )->update( $data ) )
            {
                // 更新访客信息成功
                return "flag3";
            } else if( $res == false ){
                // 更新访客信息失败，原因是访客信息没有修改
                return "flag4";
            } 
            
    	}
    }
}
