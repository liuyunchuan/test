<?php
namespace app\home\controller;

class Employee extends Base
{

	// 员工绑定信息分流
	public function index()
	{
		// 获取用户openid
		$openid = session( 'openid' );
		// echo $openid;die;
		//查找用户信息
		$employee = db( 'user' )->where( [ 'openid'=>$openid, 'token'=>session( 'token' ) ] )->find();  
		// print_r($employee);die; 
		if( !empty($employee) )
		{
			// 绑定了员工或者访客
			if( $employee['type'] == 0)
			{
				// 绑定了访客
				$this->redirect( 'Tecsun/index' );
			} else {
				// 绑定了员工
				// 这里忽略员工的禁用状态
				// 跳转到员工基本信息的页面
				$this->assign( 'list', $employee );
				return $this->fetch();
			}
		} else {
			// 没有绑定员工也没有绑定访客 跳转到绑定页面
			$this->redirect( 'Employee/bind' );
		}
	}
	// 修改员工信息
	public function edit()
	{
		if( Request()->isPost() )
		{
			$data['id']     = input('post.id');
			$data['sex']    = input('post.sex');
			$data['name']   = input('post.name');
			$data['phone']  = input('post.phone');
			$data['idcard'] = input('post.idcard');
			$data['openid'] = session( 'openid' );
			if( $data['idcard'] != '')
			{
				// 身份证号码不为空
				$info_phone  = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>session( 'token' ), 'id'=>[ 'neq', $data['id'] ] ] )->find();
				$info_idcard = db( 'user' )->where( [ 'idcard'=>$data['idcard'], 'token'=>session( 'token' ), 'id'=>[ 'neq', $data['id'] ] ] )->find();
				if( $info_idcard && $info_phone )
				{
					return "flag1";
					exit();
				}
				if( $info_phone)
				{
					return "flag2";
				}
				if( $info_idcard )
				{
					return "flag3";
					exit();
				}
			} else {
				// 身份证号码为空
				$info_phone  = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>session( 'token' ), 'id'=>[ 'neq', $data['id'] ] ] )->find();
				if( $info_phone )
				{
					return "flag2";
					exit();
				}
			}

			if(  $res = db( 'user' )->update( $data ) ) 
			{
				return "flag0";
				exit();
			} elseif($res == flase ) {
				return "flag4";
				exit;
			}
		} else {
			$openid   = session( 'openid' );
			$employee = db( 'user' )->where( 'openid', $openid )->find();
			$this->assign( 'list', $employee );
			return $this->fetch();
		}
	}
	// 绑定员工信息
	public function bind()
	{
		if( Request()->isPost() )
		{
			header("Content-Type:text/html;charset:utf-8");
			$data =[
				'phone'  => input( 'post.phone' ),
				'openid' => session( 'openid' ),
				'status' => 1,
			];
			// echo session( 'token' );
			// print_r($data);
			// $employee = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>session( 'token') ] )->find();
			$employee = db( 'user' )->where( [ 'phone'=>$data['phone'], ] )->find();
			// echo $employee;die;
			if( !empty( $employee) )
			{
				// 后台能查找到该手机信息
				// 判断时候是员工
				if( $employee['type'] == 1 )
				{
					// 判断为员工
					// 进行员工绑定
					$result = db( 'user' )->where( 'phone', $data['phone'] )->update( $data );
					if( $result !== false )
					{
						// 员工绑定成功
						return $this->fetch( 'Tecsun/bindsuccess' );
					} else {
						// 员工绑定失败
						$this->error( '员工绑定失败，请稍后再试。');
					}
				} else {
					// 判断为访客
					// 跳转到提示页面
					$this->redirect( 'Tecsun/index' );
				}
			} else {
				// 后台查找不到该手机信息
				$this->error( '后台没有改被访者信息，请联系管理员。');
			}
		} else {
			return $this->fetch();
		}
	}
	// 解除绑定
	public function release()
	{
		$openid = session( 'openid' );
		$data['status'] = 2;
		$data['openid'] = '';
		$result = db( 'user' )->where( 'openid', $openid )->update( $data );
		session( null );
		if( $result !== false ) return 1;
	}

	public function duanxin_demo1()
	{
		echo "1234";
	}
	// 发送短信验证码接口
	public function duanxin_demo()
	{
		$flag 	= 0; 
		$params	= '';//要post的数据 
		$verify = rand( 1234, 9999 );//获取随机验证码		

		$mobile = $_POST['mobile'];
		//以下信息自己填以下

		// $mobile	='18814127576';//手机号
		$argv 	= array( 
			'name'	 => 'tecsun',   //必填参数。用户账号
			'pwd'	 => 'C9BAD3187006A98B459099432956',   //必填参数。（web平台：基本资料中的接口密码）
			// 'content'=> 'testmsg',  //必填参数。发送内容（1-500 个汉字）UTF-8编码
			'content'=> '短信验证码为：'.$verify.'，请勿将验证码提供给他人。',   //必填参数。发送内容（1-500 个汉字）UTF-8编码
			'mobile' => $mobile,   	//必填参数。手机号码。多个以英文逗号隔开
			'stime'	 => '',   		//可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
			'sign'	 => 'Tecsun',   //必填参数。用户签名。
			'type'	 => 'pt',  		//必填参数。固定值 pt
			// 'extno'	 => $verify     //可选参数，扩展码，用户定义扩展码，只能为数字
		); 
		foreach ( $argv as $key=>$value )
		{ 
			if ( $flag!=0 )
			{ 
				$params .= "&"; 
				$flag = 1; 
			} 
			$params .= $key."="; 
			$params .= urlencode($value);// urlencode($value); 
			$flag 	= 1; 
		} 
		$url = "http://sms.1xinxi.cn/asmx/smsservice.aspx?".$params; //提交的url地址
		$con = substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态
		
		if($con == '0')
		{
			echo $verify;
		} else {
		}
	}
	// 邀请访客
	public function invite()
	{
		// header("Content-Type:text/html;charset=utf-8;");
		// 获取用户openid
		$openid = session( 'openid' );
		$token  = session( 'token' );
		//查找用户信息
		// $employee = db( 'user' )->where( [ 'openid'=>$openid, ])->find(); 
		$employee = db( 'user' )->where( [ 'openid'=>$openid, 'token'=>$token ] )->find(); 
		// 判断时候已经绑定
		( $employee ) or $this->redirect( 'bind' );
		// 判断该用户的身份是访客还是员工  
		( $employee['type'] == 1 ) or $this->redirect( 'Tecsun/index' );
		// 查找最近一次邀请的消息
		$record = db( 'record' )->where( [ 'openid'=>$openid, 'token'=>$token, 'visittype'=>1 ] )->order( 'id desc' )->find();
		$reasons = db( 'config' )->where( [ 'name'=>'VISIT_REASONS', 'token'=>$token ] )->select();

		if( Request()->isPost() )
		{
			$data['start_time'] = input( 'post.start_time' ); 
			$data['end_time']   = input( 'post.end_time' );
			$data['start_time'] = strtotime( $data['start_time'] ); // 预约时间
			$data['end_time']   = strtotime( $data['end_time'] );   // 截止时间
			$data['name']	    = input( 'post.name' );				// 访客姓名
			$data['phone']	    = input( 'post.phone' );			// 访客手机
			$data['account']	= input( 'post.account' );			// 来访事由			
			$data['evid']	    = $employee['id'];    				// 员工id          
			$data['company']    = $employee['company'];				// 员工所在公司
        	$data['token']      = $employee['token'];				// 所在公众号token
        	$data['ephone'] 	= $employee['phone'];				// 员工手机
        	$data['ename'] 	    = $employee['name'];				// 员工姓名
        	$data['address']    = $employee['address'];				// 见面地址
        	$data['visittype']  = 1;								// 预约发起类型
        	$data['car_num']    = '';								// 车牌号码
        	$data['create_time']  = time();							// 创建时间
        	$data['accompanying'] = 1;								// 来访人数
        	$data['openid'] 	  = $openid;		    			// 发起者openid
        	$data['end_time']     = ( $data['end_time'] != '' ) ? $data['end_time'] : $data['start_time'] + 60*60*4;	// 截止时间

        	// print_r($data);die;
        	if( $data['phone'] == $data['ephone'] ) $this->error( '贵宾的手机号码与你的手机号码相同' );
        	// print_r($data);exit;
        	$id = db( 'record' )->insertGetId( $data );
        	if( $id !== false )
        	{
        		// 生成二维码
		    	$code = get_code( $id );
		    	// $url = $code;
		    	$url     = 'http://'.$_SERVER['SERVER_NAME'] . url( 'Pass/pass', array( 'id'=> $id ) );
		    	$url_img = session( 'web_url' ) . '/public/' . createQR( $url );
		    	$record = db( 'record' )->find( $id );
		        //这个地方还需要判断一下，包括在方法里面也有做好优化，避免程序发生错误
		        $data = [
		    		'id' 		=> $id,
		    		'start_time'=> $record['start_time'],
		    		'end_time'  => $record['end_time'],
		    		'url' 	    => $url,
		    		'url_img'   => $url_img,
		    		'code'      => $code,
    			];
    			// print_r($data); exit;
    			if( db( 'recordlog' )->insert($data) )
		    	{
		    		// 把数据发送到接口 身份证、访客单位、访客姓名、访客手机、访客性别、二维码号、员工姓名、员工手机、见面地址、开始时间、截止时间、来访事由、客户类型、来访人数、车牌号码
		    		// webservice( $record['idcard'], '', $record['name'], $record['phone'], '2', $code, $record['ename'], $record['ephone'], $record['address'], $record['start_time'], $record['end_time'], $record['account'], $record['visittype'], $record['accompanying'], $record['car_num'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"调用webservice接口失败"] );
		    		webservice( $record['idcard'], '', $record['name'], $record['phone'], '2', $code, $record['ename'], $record['ephone'], $record['start_time'], $record['end_time'], $record['account'], $record['visittype'], $record['accompanying'], $record['car_num'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"调用webservice接口失败"] );
		    		// 发送成功，跳转到展示页面
		    		return $this->redirect( 'Tecsun/reservation', array( 'id'=>$id ) );
		    		// 跳转页面
		    	} else {
		    		return $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"插入成功预约记录失败"] );
		    	}
        	}
		} else {
			$this->assign( 'employee', $employee );
			$this->assign( 'record',$record );
			$this->assign( 'status', 1);
			$this->assign( 'reasons', $reasons );
			return $this->fetch();
		}
	}
	public function invite1()
	{
		// 获取用户openid
		$openid = session( 'openid' );
		$token  = session( 'token' );
		//查找用户信息
		// $employee = db( 'user' )->where( [ 'openid'=>$openid, ])->find(); 
		$employee = db( 'user' )->where( [ 'openid'=>$openid, 'token'=>$token ] )->find(); 
		// 判断时候已经绑定
		( $employee ) or $this->redirect( 'bind' );
		// 判断该用户的身份是访客还是员工  
		( $employee['type'] == 1 ) or $this->redirect( 'Tecsun/index' );
		// 查找最近一次邀请的消息
		$record = db( 'record' )->where( [ 'openid'=>$openid, 'token'=>$token, 'visittype'=>1 ] )->order( 'id desc' )->find();
		$reasons = db( 'config' )->where( [ 'name'=>'VISIT_REASONS', 'token'=>$token ] )->select();

		if( Request()->isPost() )
		{

			$data = [
        		'start_time'  => input('post.start_time'),
        		'end_time'    => input('post.end_time'),
        		'phone'       => input('post.phone'),
        		'accompanying'=> input('post.accompanying'),
        		'account' 	  => input('post.account'),
        		];
        	$visitor = db( 'user' )->where( [ 'phone'=>$data['phone'], 'token'=>$token ] )->find() or $this->error( '不存在邀请对象' );
        	if( $employee['status'] != 1 ) $this->error( '该手机号码已经解绑，不能对其进行预约' );
        	$employee = db( 'user' )->where( 'openid', session( 'openid' ) )->find() or $this->redirect( 'Visitor/results', ['message'=>'参数异常，请稍后再试', 'key'=>'获取访客信息失败']);


        	/*编辑插入记录的信息*/
        	$add_data['vid'] 		  = $visitor['id'];					// 访客id
        	$add_data['evid'] 		  = $employee['id'];				// 员工id
        	$add_data['company']      = $employee['company'];			// 员工所在公司
        	$add_data['visittype']    = 1;								// 预约发起类型
        	$add_data['token']        = $employee['token'];				// 所在公众号token
        	$add_data['accompanying'] = $data['accompanying'];			// 来访人数
        	$add_data['car_num']      = '';				// 车牌号码
        	$add_data['phone']        = $visitor['phone'];				// 访客手机
        	$add_data['ephone'] 	  = $employee['phone'];				// 员工手机
        	$add_data['openid'] 	  = session( 'openid' );			// openid
        	$add_data['account'] 	  = $data['account'];				// 来访事由
        	$add_data['name'] 	  	  = $visitor['name'];				// 访客姓名
        	$add_data['ename'] 	      = $employee['name'];				// 员工姓名
        	$add_data['address']      = $employee['address'];			// 见面地址
        	$add_data['create_time']  = time();							// 创建时间
        	$add_data['start_time']   = strtotime($data['start_time']); // 预约时间
        	$add_data['end_time']     = strtotime($data['end_time']); // 预约时间
        	$add_data['end_time']     = ( $add_data['end_time'] != '' ) ? $add_data['end_time'] : $add_data['start_time'] + 60*60*4;	// 截止时间

        	if( $add_data['phone'] == $add_data['ephone'] ) $this->error( '贵宾的手机号码与你的手机号码相同' );
        	// print_r($data);exit;
        	$id = db( 'record' )->insertGetId( $add_data );
        	if( $id !== false )
        	{

        		$data = [
        			'touser'      => $visitor['openid'],
                    'template_id' => trim( session( 'template1' ) ),
                    'url'         => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".session('appid')."&redirect_uri=".session( 'web_url' )."/public/index.php/home/employee/detail/id/$id.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
        			'data' => [
        				'first' => [
							'value' => '员工邀请',
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
                // print_r($data);
    			// 发送模板消息
    			$res = sendWeixinMessage( $data );
                // print_r($res);die;
    			// 发送成功时调整页面
    			if(!empty( $res )) return $this->fetch( 'invite1d' );
    		}
    		$this->error( '预约失败，请稍后再试' );
        }else {
			$this->assign( 'employee', $employee );
			$this->assign( 'record',$record );
			$this->assign( 'status', 1);
			$this->assign( 'reasons', $reasons );
			return $this->fetch();
		}
	}
	public function detail( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Visitor/results', ['message'=>'参数异常', 'key'=>'id不是数字'] );
    	// 根据id获取预约记录
    	$record = db( 'record' )->where( 'id', $id )->find();
    	// 若没有查找到记录，跳转到错误提示页面
        $record or $this->redirect( 'Visitor/results', ['message'=>'参数异常', 'key'=>"没有相关记录"] );
    	// print_r($record);
        // 这里不需要做预约记录是否处理的操作，交给前端页面处理
    	$this->assign( 'list', $record );
    	return $this->fetch();
    }
    // 同意邀请
    public function agree( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Visitor/results', ['message'=>"参数异常，请稍后再试", "key"=>"id不是数字"] );
    	// 根据id获取预约记录
    	$record = db( 'record' )->where( 'id', $id )->find();
    	// 若没有查找到记录，跳转到错误提示页面
        $record or $this->redirect( 'Visitor/results', ['message'=>"没有相关记录"] );
    	// 判断该条预约记录是否已经处理,如果已经处理跳转到详情页面
    	if( $record['status'] != 0 )
    	{
			$this->assign( 'list', $record );
	    	return $this->fetch( 'detail' );
    	}
    	// 根据id查找员工信息
    	$visitor = db( 'user' )->where( 'id', $record['vid'])->find();
    	// 若没有查找到员工，跳转到错误提示页面
    	$visitor or $this->redirect( 'Visitor/results', ['message'=>"参数异常，请稍后再试", "key"=>"没有找到员工信息"] );
    	// 设置公众号的appid,appsecret session
       	// 若设置token的session失败，跳转到错误页面
       	// set_session_token( $employee['token'] ) or $this->redirect( 'Visitor/results', ['message'=>"操作异常，请稍后再试", "key"=>"设置token的session失败"] );
        // 没有获取到session
       	if( session( 'template2' ) == '' )
        {
            $member = db( 'member' )->where( [ 'token'=>$visitor['token'] ] )->find();
            session( 'token', $member['token'] );
            session( 'appid', $member['appid'] );
            session( 'web_url',$member['web_url']);
            session( 'appsecret', $member['appsecret'] );
            session( 'template2', $member['template2'] );
        }
    	// 修改记录的状态为已允许
    	$data = [
    		'id'     => $id,
    		'status' => 1,
    	];
    	// 判断修改记录状态是否成功
    	db( 'record' )->update( $data ) !== false or  $this->redirect( 'Visitor/results', ['message'=>"操作异常，请稍后再试", "key"=>"修改记录的状态失败"] );
    	// 修改来访人数
    	$record['accompanying'] = empty( $record['accompanying'] ) || $record['accompanying'] == 0 ? '若干' : $record['accompanying'];
    	 // 编辑发送模板的数据包
    	// print_r( session( 'template2' ) );die;
        $senddata = [
    	 	'touser' 	  => $record['openid'],
			'template_id' => session( 'template2' ),
			'url'         => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='. session( 'appid' ).'&redirect_uri=' . session( 'web_url' ) .'/public/index.php/home/employee/agreed.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect',
			'topcolor' => '#FF0000',
			'data'     => [
				'first' => [
					'value' => '我发出的邀请确认通知',
					'color' => '#173177',
				],
				'keyword1' => [
					'value' => '员工邀请',
					'color' => '#173177',
				],
				'keyword2' => [
					'value' => date( 'Y-m-d H:i:s', $record['start_time'] ),
					'color' => '#173177',
				],
				'keyword3' => [
					'value' => $record['address'],
					'color' => '#173177',
				],
				'remark' => [
					'value' => "员工姓名：".$record['ename'].
					"\n邀请事由：".$record['account'],
					'color' => '#173177',
				],
			],
    	];
    	// 发送模板消息
        sendWeixinMessage( $senddata ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"发送模板消息失败"] );
        // print_r( $senddata );
        // print_r( sendWeixinMessage( $senddata ) );
        // die;

    	// 生成二维码
    	// $url     = 'http://'. $_SERVER['SERVER_NAME'] . url( 'Pass/pass', array( 'id'=> $id ) );
    	// $url_img = 'http://'. $_SERVER['SERVER_NAME'] . '/' . createQR( $url );
        $code = get_code( $id );
        // $url = $code;
        $url     = 'http://'. $_SERVER['SERVER_NAME'] . url( 'Pass/pass', array( 'id'=> $id ) );
        $url_img = session( 'web_url' ) . '/public/' . createQR( $url );
        //这个地方还需要判断一下，包括在方法里面也有做好优化，避免程序发生错误
    	// 编辑成功记录的数据包
    	$data = array(
    		'id' 		=> $id,
    		'start_time'=> $record['start_time'],
    		'end_time'  => $record['end_time'],
    		'url' 	    => $url,
    		'url_img'   => $url_img,
            'code'      => $code,
    	);
    	if( db( 'recordlog' )->insert($data) )
    	{
    		// 把数据发送到接口 身份证、访客单位、访客姓名、访客手机、访客性别、二维码号、员工姓名、员工手机、见面地址、开始时间、截止时间、来访事由、客户类型、来访人数、车牌号码
    		// webservice( $record['idcard'], '', $record['name'], $record['phone'], '', '', $record['ename'], $record['ephone'], $record['address'], $record['start_time'], $record['end_time'], $record['account'], $record['visittype'], $record['accompanying'], $record['car_num'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"调用webservice接口失败"] );
            webservice( $record['idcard'], '', $record['name'], $record['phone'], $record['sex'], $code, $record['ename'], $record['ephone'], $record['start_time'], $record['end_time'], $record['account'], $record['visittype'], $record['accompanying'], $record['car_num'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"调用webservice接口失败"] );
    		// 发送成功，跳转到展示页面
    		return $this->redirect( 'Tecsun/reservation', array( 'id'=>$id ) );
            // return $this->fetch( 'agreed' );
    		// return $this->redirect( 'agreed' );
    		// 跳转页面
    	} else {
    		return $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", "key"=>"插入成功邀请记录失败"] );
    	}
    }
    public function agreed()
    {
    	return $this->fetch();
    }

    //拒绝邀请
    public function refuse( $id=0 )
    {
    	// 判断参数是否是数字，若不是数字，跳转到错误提示页面
    	is_numeric( $id ) or $this->redirect( 'Index/results', [ 'message'=>"参数异常，请稍后再试", "key"=>"id不是数字" ] );
    	// 根据id获取预约记录，若没有查找到记录，跳转到错误提示页面
    	$record = db( 'record' )->where( 'id', $id )->find() or $this->redirect( 'Index/results', [ 'message'=>"参数异常，请稍后再试", 'key'=>"没有相关记录" ] );
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
				'evid' => $record['vid'],
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
                    $member = db( 'member' )->where( [ 'token'=>$record['token'] ] )->find();
    				$senddata = [
    					'touser'      => $record['openid'],
						'template_id' => trim( $member['template2'] ),
						'url'         => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $member['appid'] . "&redirect_uri=" . $member['web_url'] . "/public/index.php/home/visitor/refusedetail/id/$id.html&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
						'topcolor' => '#FF0000',
						'data' => [
							'first' => [
								'value' => '对方拒绝了您的邀请',
								'color' => '#173177',
							],
							'keyword1' => [
								'value' => $record['name'],
								'color' => '#173177',
							],
							'keyword2' => [
								'value' => $record['phone'],
								'color' => '#173177',
							],
							'keyword3' => [
								'value' => time( 'Y-m-d H:i:s', $record['start_time'] ),
								'color' => '#173177',
							],
							'remark' => [
								'value' => "拒绝原因：".$data['content'],
								'color' => '#173177',
							],
						],
    				];
                    // print_r($senddata);
    				sendWeixinMessage( $senddata, $record['token'] ) or $this->redirect( 'Index/results', ['message'=>"操作异常，请稍后再试", 'key'=>'修改记录状态失败'] );
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
	// 获取员工的邀请凭证·多条
	public function records()
	{
		// 获取用户openid
        $openid = session( 'openid' );
        // echo $openid;
        // 这里要不要判断是否已经是访客
        // 查找访客预约记录
        $record = db( 'record' )->alias( 'r' )->where( [ 'openid'=>session( 'openid' ), 'token'=>session( 'token' ) ] )->order( 'r.id desc' )->limit( 3 )->join( 'recordlog l', 'r.id = l.id')->select();
        // $record = db( 'record' )->where( [ 'openid'=>$openid, 'token'=>session( 'token' ) ] )->order( 'id desc' )->limit( 5 )->select();
        // print_r($record);
        $this->assign( 'list', $record );
        return $this->fetch();
	}

}