<?php 
namespace app\home\Controller;

define("TOKEN","weixinkaifa");
class WeixinApi
{
	public function __construct()
	{
		// 判断数据请求的类型
		if( !isset($_GET['echostr']) )
		{
			// 数据交互操作
			return $this->responseMsg();
		} else {
			// 验证微信服务器
			return $this->valid();		
		}
	}

	/*验证签名*/
	public function valid()
	{
		$echostr   = $_GET['echostr'];
		$signature = $_GET['signature'];
		$timestamp = $_GET['timestamp'];
		$nonce 	   = $_GET['nonce'];
		$token  = TOKEN;
		$tmpArr = array($token ,$timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if( $tmpStr == $signature )
		{
			// 这里不能使用return，只能使用token
			echo $echostr;
			exit;
		}
	}

	/*响应消息*/
	public function responseMsg()
	{
		// 获取消息源xml数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if( !empty($postStr) )
		{
			// 把请求xml数据写到日志文件里面
			// $this->logger("R".$postStr);
			// 获取消息对象
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			// 获取消息类型
			$RX_TYPE = trim( $postObj->MsgType );

			// 获取提交给哪个公众号
			$token  = trim( $postObj->ToUserName );
			// 查询出给用户的信息
			$member = db( 'member' )->where( 'token' , $token )->find();

			/*消息分离*/
			switch ( $RX_TYPE )
			{
				case 'event':
					$result = $this->receiveEvent( $postObj, $member );
					break;
				case 'text':
					$result = $this->receiveText( $postObj, $member );
					break;
				case 'image':
					$result = $this->receiveImage( $postObj );
					break;
				case 'location':
					$result = $this->receiveLocation( $postObj );
					break;
				case 'voice':
					$result = $this->receiveVoice( $postObj );
					break;
				case 'video':
					$result = $this->receiveVideo( $postObj );
					break;
				case 'link':
					$result = $this->receiveLink( $postObj );
					break;
				default:
					$result = "unknown mag type： ".$RX_TYPE;
					break;
			}
			// 把返回的数据写入到日记文件
			// $this->logger( 'T ' .$result );
			// 把返回信息输出到页面 使用return会不会好一点，经过测试，改为return的时候没有输出
			echo $result;
		} else {
			echo "";
			exit;
		}
	}

	/*接收事件消息*/
	private function receiveEvent( $object, $member )
	{
		$content = "";
		// 判断事件的类型
		switch ( $object->Event )
		{
			case 'subscribe':
				$content = '欢迎关注我们的公众号！';
				break;
			case 'unsubscribe':
				$content = '取消关注';
				break;
			case 'SCAN':
				$content[] = array(
					"Title"=>"扫描对象：".$object->FromUserName,
					);
				break;
			case 'CLICK':
				// // 获取提交给哪个公众号
				$token  = trim( $object->ToUserName );
				// // 查询出给用户的信息
				// $member = db( 'member' )->where( 'token' , $token )->find();
				// return $this->transmitText( $object, 'dd' );
				// break
				// 判断公众号是否是可以使用
				if( !$member )
				// if( true )
				{
					// 根据$token没有找到对应的信息，
					$content = '请联系管理员是否注册微信预约。';
					// $content = trim( $object->ToUserName )."123";
				} else {
					// 该公众号可以使用
					// 这里应该添加判断
					$token   = $member['token'];
					$appid   = $member['appid'];
					$web_url = $member['web_url'];
					// 判断点击的类型
					switch ( $object->EventKey )
					{
						case 'WOSHIFANGKE':
							$content   = array();
							$content[] = array(
								"Description" => "微信预约操作指引",
								"Title"  => "我是访客·操作指引",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/main1.jpg",
								"Url"    => "http://mp.weixin.qq.com/s/KmM9ClW5onE4m6vUFf_3zA",
								);
							$content[] = array(
								"Description" => "预约凭证",
								"Title"  => "预约凭证",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/yuyuepingzheng.jpg",
								"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/visitor/records&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
								);
							$content[] = array(
								"Description" => "我要预约",
								"Title"  => "我要预约",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/woyaoyuyue.jpg",
								"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/visitor/yuyue&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
								);
							break;
						case 'WOSHIYUANGONG':
							$content   = array();
							$content[] = array(
								"Description" => "微信预约操作指引",
								"Title"  => "我是访客·操作指引",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/main1.jpg",
								"Url"    => "http://mp.weixin.qq.com/s/3gioR2JaCfUeI5KJF7tO4Q",
								);
							$content[] = array(
								"Description" => "员工绑定",
								"Title"  => "员工绑定",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/yuangongbangding.jpg",
								"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/employee/index&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
								);
							$content[] = array(
								"Description" => "预约凭证",
								"Title"  => "预约凭证",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/yuangongbangding.jpg",
								"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/employee/records&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
								);
							$content[] = array(
								"Description" => "邀请访客",
								"Title"  => "邀请访客",
								"PicUrl" => $web_url."/public/static/home/images/thumbnail/yaoqingfangke.jpg",
								"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/employee/invite&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
								);
							// $content[] = array(
							// 	"Description" => "普通邀请访客",
							// 	"Title"  => "普通邀请访客",
							// 	"PicUrl" => $web_url."/public/static/home/images/thumbnail/yaoqingfangke.jpg",
							// 	"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/employee/invite1&response_type=code&scope=snsapi_base&state=" . $token . "#wechat_redirect",
							// 	);
							break;
						default:
							$content = "点击菜单： ".$object->EventKey;
							break;
					}
				}
				break;
			case 'LOCATION':
				$content = 'LOCATION';
				break;
			case 'VIEW':
				$content = '跳转链接:'.$object->EventKey;
				break;
			case 'MASSSENDJOBFINISH':
				$content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
				break;
			default:
				$content = 'receive a new event: '.$object->Event;
				break;
		}
		// return $this->transmitText( $object, $content );
		// 判断返回数据的类型是文本消息还是图文消息
		if( is_array( $content ) )
		{
			// 图文消息
			if( isset( $content[0] ) )
			{
				return $this->transmitNews( $object, $content );
			}
		} else {
			// 文本消息
			return $this->transmitText( $object, $content );
		}
	}
	
	/*接收文本消息*/
	private function receiveText( $object, $member )
	{
		// 获取文本内容
		$content = trim( $object->Content );

		$token   = $member['token'];
		$appid   = $member['appid'];
		$web_url = $member['web_url'];

		// 检测关键字自动回复
		if( trim($content) == '研习会' )
		{
			$contentStr[] = array(
				"Description" => "研习会问卷",
				"Title"  => "研习会问卷",
				"PicUrl" => $web_url."/public/static/home/images/thumbnail/woyaoyuyue.jpg",
				"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/wenjuan/index&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
				);
			return $this->transmitNews( $object, $contentStr );
		} elseif ( $content == '售前' ) {
			$contentStr[] = array(
				"Description" => "售前支持服务满意度调查",
				"Title"  => "售前支持服务满意度调查",
				"PicUrl" => $web_url."/public/static/home/images/thumbnail/woyaoyuyue.jpg",
				"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/wenjuan/shouqiancheck&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
				);
			return $this->transmitNews( $object, $contentStr );
		} elseif ( $content == '售后' ) {
			$contentStr[] = array(
				"Description" => "售后支持服务满意度调查",
				"Title"  => "售后支持服务满意度调查",
				"PicUrl" => $web_url."/public/static/home/images/thumbnail/woyaoyuyue.jpg",
				"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/wenjuan/shouhoucheck&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
				);
			return $this->transmitNews( $object, $contentStr );
		} else {		
				// // 微信上墙处理
				// // 获取openid
				// $openid = $object->FromUserName;
				// // 回复文本消息
				// // return $this->transmitText( $object, $content );
				// $data = [
				// 	'openid'  => $openid,
				// 	'content' => $content,	
				// ];
				// $result = db( 'message' )->insert( $data );
				// if( $result )
				// {
				// 	$contentStr = "处理成功，你发送的文字将会显示在微信墙活动大屏幕上";
				// } else {
				// 	$contentStr = "参与微信墙活动失败";
				// }

			// 测试config
			// $object->ToUserName 取出来的值很有可能前面或者后面以后空格，需要对其做相应的处理
			$token  = trim( $object->ToUserName );
			$member = db( 'member' )->where( 'token' , $token )->find();
			$appid  = $member['appid'];
			$web_url= $member['web_url'];
			// $contentStr = $member['address'];
			$content = array();
			$content[] = array(
				"Description" => "预约凭证",
				"Title"  => "预约凭证",
				"PicUrl" => $web_url."/public/static/home/images/thumbnail/yuyuepingzheng.jpg",
				"Url"    => "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $web_url . "/public/index.php/home/visitor/records&response_type=code&scope=snsapi_base&state=1#wechat_redirect",
				);

			// return $this->transmitNews( $object, $content );
			$url = $web_url."/public/static/home/images/thumbnail/yuangongbangding.jpg";

			// return $this->transmitText( $object, $token );
		}
	}

	/*接收图片消息*/
	private function receiveImage( $object )
	{
		// 获取图片ID
		$imageArray['MediaId'] = trim( $object->MediaId );
		// 回复图片消息
		return $this->transmitImage( $object, $imageArray );
	}

	/*回复文本消息*/
	private function transmitText( $object, $content )
	{
		$itemTpl = "<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>";
		$item_str = sprintf( $itemTpl, $content );
		
		return $this->transmitBase( $object, $item_str );
	}

	/*回复图片消息*/
	private function transmitImage( $object, $imageArray )
	{
		$itemTpl = "<MsgType><![CDATA[image]]></MsgType>
					<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>";
		$item_str = sprintf( $itemTpl, $imageArray['MediaId'] );
		
		return $this->transmitBase( $object, $item_str );
	}

	/*回复语音消息*/
	private function transmitVoice( $object, $voiceArray )
	{
		$itemTpl = "<MsgType><![CDATA[voice]]></MsgType>
					<Voice>
					<MediaId><![CDATA[%s]]></MediaId>
					</Voice>";
		$item_str = sprintf( $itemTpl, $voiceArray['MediaId'] );

		return $this->transmitBase( $object, $item_str);
	}

	/*回复视频消息*/
	private function transmitVideo($object, $videoArray)
	{
		$itemTpl = "<MsgType><![CDATA[video]]></MsgType>
					<Video>
						<MediaId><![CDATA[%s]]></MediaId>
						<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
					</Video>";

		$item_str = sprintf( $itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description'] );

		return $this->transmitBase( $object, $item_str);
	}

	/*回复图文消息*/
	private function transmitNews($object, $newsArray)
	{
		if( !is_array($newsArray) )
		{
			return;
		}
		$itemTpl = " <item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
				</item>";

		$item_str = "";
		foreach ( $newsArray as $item )
		{
			$item_str .= sprintf( $itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url'] );
		}
		$xmlTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>
					$item_str</Articles>
					</xml>";

		return sprintf( $xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray) );
	}

	/*回复多客服消息*/
	private function transmitService($object){
		$xmlTpl = "<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[transfer_customer_service]]></MsgType>
				</xml>";
		$result = sprintf( $xmlTpl, $object->FromUserName, $object->ToUserName, time() );
		return $result;
	}

	/*回复消息共同方法*/
	private function transmitBase( $object, $item_str)
	{
		$xmlTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					$item_str
					</xml>";

		$result = sprintf( $xmlTpl, $object->FromUserName, $object->ToUserName, time() );
		return $result;
	}
}