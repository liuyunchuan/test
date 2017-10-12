<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


// 获取用户OpenId
function get_openid()
{
    if( input( 'get.state' ) == '' )return ;
    // 获取传递的code
    $code  = input( 'get.code' );
    // 获取token
    $token = input( 'get.state' );
    // 根据token获取账号信息
    $member = db( 'member' )->where( 'token', $token )->find();
    // print_r($member);
    // 设置session
    session( 'token', $token );
    session( 'appid', $member['appid'] );
    session( 'web_url', $member['web_url'] );
    session( 'appsecret', $member['appsecret'] );
    session( 'template1', $member['template1'] );
    session( 'template2', $member['template2'] );
    // echo session( 'appid' );
    // print_r($member);die;
    // echo $member['template2'];die;

    // die;
    // 拼接url地址
    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.session('appid').'&secret='.session( 'appsecret' ).'&code='.$code.'&grant_type=authorization_code';
    // echo $url;
    // curl请求
    $jsoncode   = get_contents( $url );
    // 解析json数据包
    $returncode = json_decode( $jsoncode, true );
    // print_r( $returncode);
    // 从数组中获取openid，并且避免刷新出现空数组的错误
    $openid = isset( $returncode['openid']) ? trim( $returncode['openid'] ) : '';
    // 判断获取openid是否成功
    if( $openid != '' )
    {
        // 设置session
        session( 'openid', $openid );
        return $openid;
    }
    return 0;
}
// 获取session中的openid
function get_session_openid()
{
    $openid = trim( session('openid') );
    return ( $openid != '' ) ? $openid : 0;
}
// 设置公众号的appid,appsecret session
function set_session_token( $token='')
{
    // 已经获取了缓存
    if( session( 'appid' ) != '') return true;
    if( $token == '') return false;
    $member = db( 'member' )->where( 'token', $token)->find();
    session( 'token', $token );
    session( 'appid', $member['appid'] );
    session( 'appsecret', $member['appsecret'] );
    return true;
}
// 获取session中的公众号token
// function get_session_token()
// {
//     if( session( 'token') != '') return session( 'token' );
// }

// curl请求
function get_contents( $url, $data='' )
{
    // print_r($url);
    // print_r($data);exit;
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
    if(!empty($data)){
        curl_setopt($curl,1,1);
        // curl_setopt($curl,CURLOOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    }
     curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
     $output = curl_exec($curl);
     curl_close($curl);
     return $output;
}

/**
 * 发送模板消息
 * @param  array  $data 发送数据包
 * @return boolean      true/false
 */
function sendWeixinMessage( $data=array(), $token='' )
{
    if( empty( $data ) ) return false;
    // print_r($data);
    // 获取access_token
    $access_token = get_acccess_token( $token );
    // print_r($access_token);exit;
    // print_r($data);exit;
    if( $access_token == '')
    {
        // 没有获取到access_token
        die('<script language="javascript">alert("access_token不能为空!");window.history.back(-1);</script> ');
    }
    $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='. $access_token;
    $jsondata = json_encode( $data );
    // haha($url,$jsondata);exit;
    $res    = get_contents( $url, $jsondata );
    // print_r($res);
    $result = json_decode( $res, true );
    if( !empty( $result ) ) return $res;
    return false;
}
/**
 * 获取access_token
 * @return [string] [access_token]
 */
function get_acccess_token( $token = '' )
{
    // print_r($data);
    if( session( 'appid' ) == '' )
    {
        $member = db( 'member' )->where( [ 'token'=>$token ] )->find();
        session( 'appid', $member['appid'] );
        session( 'appsecret', $member['appsecret'] );
    }    
    // $appid     = session( 'appid' );
    // $appsecret = session( 'appsecret' );
    $url    = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. session( 'appid' ) .'&secret='.session( 'appsecret' );
    $res    = get_contents( $url );
    // print_r($res);die;
    $result = json_decode( $res, true );
    return trim( $result['access_token'] );
}
/**
 * 生成二维码
 * @param  string $url URL地址
 * @return string      二维码文件名
 */
function createQR( $url )
{
    // 引入库文件
    $res = import('Ercode.Phpqrcode');
    $QRcode   = new \QRcode();
    // 生成文件名
    $fileName = './QRcode/' . md5( $url ) . '.png';
    // 纠错级别： L、M、Q、H
    $errorCorrectionLevel = 'L';
    // 二维码的大小：1到10
    $matrixPointSize = 10;
    $QRcode::png( $url, $fileName, $errorCorrectionLevel, $matrixPointSize, 2);
    return $fileName;
}

/**
 * 向服务器写入数据
 * @param $strIdCertNo          访客身份证
 * @param $strVisitorCompany    访客单位
 * @param $strVisitorName       访客姓名
 * @param $strTel               访客手机
 * @param $strSex               访客性别
 * @param $strQRCode            二维码号
 * @param $strBookName          访客姓名
 * @param $strBookTel           员工手机
 * @param $strValidTimeStart    来访时间
 * @param $strValidTimeEnd      截止时间
 * @param $strReason            来访事由
 * @param $iVisitorType         客户类型
 * @param $iVisitNum            来访人数
 * @param $strLicensePlate      车牌号码
 * @return [type] [description]
 */
function webservice( $strIdCertNo, $strVisitorCompany, $strVisitorName, $strTel, $strSex, $strQRCode, $strBookName, $strBookTel, $strValidTimeStart, $strValidTimeEnd, $strReason, $iVisitorType, $iVisitNum, $strLicensePlate )
{
    // 这里实现webservice的接口
    // 没有传输见面地址
    header( "Content-type:text/html;charset=utf-8" );
    $res = import('webservice.nusoap');
    $client = new SoapClient( "http://211.147.238.120:8083/webserviceAndriod.asmx?WSDL");
    $client->soap_defencoding = 'utf-8';
    $client->decode_utf8      = false;
    $client->xml_encoding     = 'utf-8';
    // $srt
    // echo $strValidTimeStart;die;
    $strValidTimeStart = date( 'Y-m-d H:i:s', $strValidTimeStart );
    $strValidTimeEnd   = date( 'Y-m-d H:i:s', $strValidTimeEnd );

    $param = [
        'key'=>'tecsunPf',
        'strWeiXinAccount'  => session( 'token' ),
        'strIdCertNo'       => $strIdCertNo,                                            /*访客身份证*/
        // 'strVisitorCompany'  => $strVisitorCompany,                                      /*访客单位*/
        'strVisitorCompany' => '',                                      /*访客单位*/
        'strVisitorName'    => $strVisitorName,                                         /*访客姓名*/
        'strTel'            => $strTel,                                                 /*访客手机*/
        'strSex'            => $strSex,                                                 /*访客性别*/
        'strQRCode'         => $strQRCode,                                              /*二维码号*/
        'strBookName'       => $strBookName,                                            /*员工姓名*/
        'strBookTel'        => $strBookTel,                                             /*员工号码*/
        'strValidTimeStart' => $strValidTimeStart,                                      /*生效时间*/
        'strValidTimeEnd'   => $strValidTimeEnd,                                        /*截止时间*/
        'strReason'         => $strReason,                                              /*来访事由*/   
        'iVisitorType'      => $iVisitorType,                                           /*客户类型*/
        'iVisitNum'         => 1,
        // 'iVisitNum'          => $iVisitNum,                                             /*来访人数*/
        'strLicensePlate'   => $strLicensePlate,
    ];
    // print_r($param);die;
    $arryResult = $client->__soapCall('PostABookRecord',array('parameters' => $param)); 
    // print_r( $arryResult );
    // echo $strIdCertNo."<br>"; 
    // echo $strVisitorCompany."<br>"; 
    // echo $strVisitorName."<br>"; 
    // echo $strTel."<br>"; 
    // echo $strSex."<br>"; 
    // echo $strQRCode."<br>"; 
    // echo $strBookName."<br>"; 
    // echo $strBookTel."<br>"; 
    // echo $strValidTimeStart."<br>"; 
    // echo $strValidTimeEnd."<br>"; 
    // echo $strReason."<br>"; 
    // echo $iVisitorType."<br>"; 
    // echo $iVisitNum."<br>"; 
    // echo $strLicensePlate."<br>"; 
    // echo $strLicensePlate."<br>";
    // die;





    // return false;
    return true;
}
function get_code( $id )
{
    $code = '3';
    $rand1 = rand( 10, 99 );
    $rand2 = rand( 10, 99 );
    $code .= $rand1;
    for( $i=strlen($id); $i<5; $i++ )
    {
        $id = '0'.$id;
    }
    $code .= $id;
    $code .= $rand2;

    return $code;
}

// 获取研习会当前的设置
function get_yanxihui_settings()
{
    return db( 'wenjuansettings' )->where( 'type', 1 )->order( 'id desc' )->limit( 1 )->find();
}
// 获取售前当前的设置
function get_shouqian_settings()
{
    return db( 'wenjuansettings' )->where( 'type', 2 )->order( 'id desc' )->limit( 1 )->find();
}
// 获取售后当前的设置
function get_shouhou_settings()
{
    return db( 'wenjuansettings' )->where( 'type', 3 )->order( 'id desc' )->limit( 1 )->find();
}
