<?php 
// 预约助手
define( 'APPID', 'wxd9e3d3efc11ace6a');
define( 'APPSECRET', '68ac90a0c58a137aec04c8efd550639f' );

	$menu10 = '{
		"button":
		[
			{
				"name":"关于我们",
				"type":"view",
				"name":"产品介绍",
				"url":"http://www.fangkets.com"
			},
			{
				"type":"view",
				"name":"公司介绍",
				"url":"http://q.eqxiu.com/s/pWfca3jL"
				},
			{
				"name":"微信预约",
				"sub_button":
				[
					{
						"type":"click",
						"name":"我是访客",
						"key":"WOSHIFANGKE"
					},
					{
						"type":"click",
						"name":"我是员工",
						"key":"WOSHIYUANGONG"
					},
				]
			}
		]
	}';

function curl_post($url,$data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
function curl_post1($url, $data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
}

$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".APPSECRET;
$content=file_get_contents($url);
$ret=json_decode($content,true);
// var_dump($ret);
// var_dump($ret['access_token']);

// 创建
$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ret['access_token'];

// 查询
// $url="https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$ret['access_token'];
$content=curl_post($url,$menu10);
var_dump($content);
$ret1=json_decode($content,true);
var_dump($ret1);
// $tokenFile = "access_token.txt";//缓存文件名
// $fp = fopen($tokenFile, "w");
// fwrite($fp, json_encode($ret1));
// fclose($fp);
// $ret = weixin::createMenu($menu);//创建菜单
if($ret1){//创建成功
  echo 'create menu ok';
}else{//创建失败
  echo 'create menu fail';
}
?>
