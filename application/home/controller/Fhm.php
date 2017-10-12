<?php 
namespace app\home\controller;

class Fhm extends Base
{

	public function _initialize()
	{
		// $openid = session('openid');
		// if( $openid!=0 && $openid!='')
		// {
		// 	return 1;
		// } else {
			$code  = input( 'get.code' );
			// echo $member['template2'];die;

		    // die;
		    // 拼接url地址
		    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxabada668ceb22ead&secret=7fd85e80e40f05af67e32fa01132de5a&code='.$code.'&grant_type=authorization_code';
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
		    // } 
		// }
		
	    // else {
	    // 	session( 'openid', 0 );
	    // }
	    // return 0;
		// echo "ddd";
		// die;
		// echo session( 'openid' );

	}

	public function qiandao()
	{
		$openid = session( 'openid' );
		// echo $openid;die;
		// echo "ddd";
		if( $openid == '' ) return $this->fetch( 'error' );

		$fhm = db( 'fhm' )->where( 'openid', $openid )->find();
		// echo "dd";die;
		if( Request()->isPost() )
		{
			if( !$fhm )
			{
				// 没有签到
				// 获取数据
				$data = [
					'name' => input( 'post.name'),
					'tel' => input( 'post.phone'),
					// 'zhiwu' => input( 'post.zhiwu'),
				];
				// print_r($data);die;
				// 把数据转为json
				$json_data = json_encode( $data );
				// $json_data = '{"name":"'.$data['name'].'","tel":"'.$data['phone'].'"}';
				// 把数据写到二维码
				$url =  'http://4006719008.net/tp5/public/'.createQR( $json_data );
				// 记录二维码的url
				$data['url'] = $url;
				// 加入openid
				$data['openid'] = $openid;
				// 保存数据
				if( db( 'fhm' )->insert( $data ) )
				{
					return $this->redirect( 'show' );
				} else {
					return $this->error( '服务器开小差了' );
				}

				// return $this->redirect( 'show' );
			} else {
				// 已经签到
				return $this->redirect( 'show' );
			}

		} else {
			if( !$fhm )
			{
				// 没有签到
				return $this->fetch();
			} else {
				// 已经签到，显示二维码页面
				return $this->redirect( 'show' );
			}
		}
	}

	public function show()
	{
		$fhm = db( 'fhm' )->where( 'openid', session( 'openid' ) )->find();
		if( $fhm )
		{
			$title = '尊敬的 '.$fhm['name'].' 欢迎您！';
			$this->assign( 'title', $title );
			$this->assign( 'url', $fhm['url'] );
			return $this->fetch();
		} else {
			return $this->fetch( 'error' );
		}
	}

}