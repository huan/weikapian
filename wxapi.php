<?php
mb_internal_encoding('UTF-8');

//$str = "这是第一行\n这，。事第二行哈哈哈哈哈";
//$title = wfxDigest($str,20);
//$desc =  wfxDigest($str,200,mb_strlen($title) );

//die("[$title] , [$desc] \n");

define("TOKEN", "weixin");//修改和乐享平台token值一样即可！并使腾讯的toke值和此一致//
$ac =@$_GET['ac'];
$tid = @$_GET['tid'];
$page = @$_GET['page'];
$k = @$_GET['k'];
$c = @$_GET['c'];
$baseurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];//.'?'.$_SERVER['QUERY_STRING'];

if($tid&&$ac){
		$wfx = new wfxStorage();

		$message = $wfx->load($tid);

        if ( !empty($message) )
		    $html = wfxHtml($message,$tid);
        else
            $html = '';

		exit($html); 
}


$wechatObj = new wechatCallbackapiTest();
if(@$GLOBALS["HTTP_RAW_POST_DATA"]){
		$wechatObj->responseMsg();
}else{
		if(@$_GET["timestamp"]){
				$wechatObj->valid();
		}else{
				echo "php is ok this is new 2013-3-6<br>";
				if(function_exists('curl_init')){
						echo "curl_init is ok<br>";
				}else{
						echo "no curl_init <br>";
				}
				if(function_exists('fsockopen')){
						echo "fsockopen is ok<br>";
				}
				else{
						echo "fsockopen is no<br>>";
				}
				if(function_exists('file_get_contents')){
						echo "file_get_contents is ok <br>";
				}
				else{
						echo "file_get_contents is not ok<br>";
				}
		}
}

class wechatCallbackapiTest
{
		public function valid()
		{
				$echoStr = $_GET["echostr"];
				//valid signature , option
				if($this->checkSignature()){
						echo $echoStr;
						exit;
				}
		}

		public function responseMsg()
		{
				$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
				if (!empty($postStr)){
						$postObj = @simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

//wfxDebug($postObj, "OK");

						$fromUsername = $postObj->FromUserName;
						$toUsername = $postObj->ToUserName;
						$Location_X = $postObj->Location_X;
						$Location_Y = $postObj->Location_Y;
						$Scale = $postObj->Scale;
						$Label = $postObj->Label;
						$PicUrl = $postObj->PicUrl;
						$MsgType = $postObj->MsgType;
						$MsgId  = $postObj->MsgId;
						$Url = $postObj->Url;
						$Event = $postObj->Event;
						$Latitude = $postObj->Latitude;
						$Longitude = $postObj->Longitude;
						$Precision = $postObj->Precision;
						$EventKey = $postObj->EventKey;
						$Message = trim($postObj->Content);
						$token = TOKEN;

						wfxTrace("New WeiXin Message " . time());

						$Message = trim($postStr);
						wfxTrace($Message);

						$resultStr = wfx($postObj);
						wfxTrace($resultStr);

						echo $resultStr;



				}
		}

		private function checkSignature()
		{
				$signature = $_GET["signature"];
				$timestamp = $_GET["timestamp"];
				$nonce = $_GET["nonce"];	
				$token = TOKEN;
				$tmpArr = array($token, $timestamp, $nonce);
				sort($tmpArr);
				$tmpStr = implode( $tmpArr );
				$tmpStr = sha1( $tmpStr );
				if( $tmpStr == $signature ){
						return true;
				}else{
						return false;
				}
		}

}


function vcurll($url, $post = '', $cookie = '', $cookiejar = '', $referer = ''){

		if(function_exists('curl_init')){
				$tmpInfo = '';
				$cookiepath = getcwd().'./'.$cookiejar;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				if($referer) {
						curl_setopt($curl, CURLOPT_REFERER, $referer);
				} else {
						curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
				}
				if($post) {
						curl_setopt($curl, CURLOPT_POST, 1);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
				}
				if($cookie) {
						curl_setopt($curl, CURLOPT_COOKIE, $cookie);
				}
				if($cookiejar) {
						curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiepath);
						curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);
				}
				//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curl, CURLOPT_TIMEOUT, 15);
				curl_setopt($curl, CURLOPT_HEADER, 0);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$tmpInfo = curl_exec($curl);
				if (curl_errno($curl)) {
						return curl_error($curl);
				}
				curl_close($curl);
				return $tmpInfo;
		}else if(function_exists('file_get_contents')){

				return file_get_contents($url); 
		}
} 

function wfx($postObj)
{
		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$Location_X = $postObj->Location_X;
		$Location_Y = $postObj->Location_Y;
		$Scale = $postObj->Scale;
		$Label = $postObj->Label;
		$PicUrl = $postObj->PicUrl;
		$MsgType = $postObj->MsgType;
		$Event = $postObj->Event;
		$MsgId  = $postObj->MsgId;
		$Url = $postObj->Url;
		$Event = $postObj->Event;
		$Latitude = $postObj->Latitude;
		$Longitude = $postObj->Longitude;
		$Precision = $postObj->Precision;
		$EventKey = $postObj->EventKey;
		$Message = trim($postObj->Content);
		$token = TOKEN;

		$time = time();

		switch ( $MsgType )
		{
		case 'event':
			if ('unsubscribe'==$Event)
			{
				wfxTrace("UNSUBSCRIBE: $fromUsername");
			}
			elseif ('subscribe'==$Event)
			{
				$xml = "
<xml>
<ToUserName><![CDATA[$fromUsername]]></ToUserName>
<FromUserName><![CDATA[$toUsername]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[微卡片(β)帮助你更高效的发送微信消息。

微卡片可以：
1、接收长文本(1千字以内），返回易读的消息卡片；
2、接收二维码图片，返回电梯连接；
3、接收优酷视频连接，返回视频消息卡片；

马上试试吧！发送一句话给我，我帮你做卡片！]]></Content>
<FuncFlag>1</FuncFlag>
</xml>
";
			
			}
			break;
		case 'image':
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $PicUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
wfxTrace("Before image CURL: $PicUrl");
			$output = curl_exec($ch);
wfxTrace("After image CURL, output length: " . sizeof($output) );

			$tmpfile = "/tmp/wxapi." . posix_getpid();
			$fd = fopen($tmpfile, "w");
			fwrite($fd, $output);
			fclose($fd);

//wfxTrace("write to file $tmpfile\n");

			$fd = popen("/usr/local/bin/zxing --try-harder $tmpfile", "r");
			$result = fread($fd,9999);
wfxTrace("zxing result: $result");
			pclose($fd);

			// unlink($tmpfile);

			$sae_url = file_get_contents("http://weikapian.sinaapp.com/api/add_media.php?key=lizhuohuan"
											. "&openid=$fromUsername"
											. "&url=" . urlencode($PicUrl)
										);
wfxTrace("sae_url: $sae_url");
			$pts = new PicTmpStor();
			$pts->save($fromUsername,$sae_url);

            if (preg_match('#(http://\S+)#i', $result, $matches)) {
                $result = $matches[1];

			    $result_html = "<a href=\"$result\">二维码电梯</a> <- 点击进入
本链接由【鹦哥β】生成
再发送微卡片文字（不超过1000字）给我，就可以得到图文微卡片。
（一分钟内有效）
";
            } else {
				$result_html = "图片收到，再发送微卡片文字（不超过1000字）给我，就可以得到图文微卡片。（一分钟内有效）";
            }


			$xml = "
 <xml>
 <ToUserName><![CDATA[$fromUsername]]></ToUserName>
 <FromUserName><![CDATA[$toUsername]]></FromUserName>
 <CreateTime>$time</CreateTime>
 <MsgType><![CDATA[text]]></MsgType>
 <Content><![CDATA[$result_html]]></Content>
 <FuncFlag>0</FuncFlag>
 </xml>
";
			break;

		case 'news':
		default:
			if(preg_match("#^http://v.youku.com/v_show/id_([^\.]+).html#i",trim($Message),$matches)
				|| preg_match("#^http://m.youku.com/smartphone/detail\?vid=([a-zA-Z0-9]+)#i",trim($Message),$matches) 
				)
// http://m.youku.com/smartphone/detail?vid=XNTAzNDgwODQw
			{
				/* 
				 *  转换视频到微卡片
				 */
				$vid = $matches[1];

				$json = file_get_contents("https://openapi.youku.com/v2/videos/show_basic.json?client_id=906075cd694c0aae&video_id=$vid");
				$yk = json_decode($json);

				$title = htmlspecialchars_decode($yk->title);
				$desc = $yk->description;
				$thumbnail	= $yk->thumbnail;
				$Message = "
<!DOCTYPE html>
<html>

<head>
<title>$title</title>

<script src='//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js'></script>
<script type='text/javascript' src='http://player.youku.com/jsapi'></script>
<script type='text/javascript'>
function loadPlayer(vid){
	player = new YKU.Player('youkuplayer',{
		client_id:'906075cd694c0aae',
		vid:vid
		}
	); 
}
jQuery(document).ready(function(){
	loadPlayer('$vid');	
});
</script>


<meta http-equiv=Content-Type content='text/html;charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
<meta name='apple-mobile-web-app-capable' content='yes'>
<meta name='apple-mobile-web-app-status-bar-style' content='black'>
<meta name='format-detection' content='telephone=no'>
<style>
html{background:#FFF;color:#000;}
body, div, dl, dt, dd, h1, h2, h3, h4, h5, h6, pre, code, form, fieldset, legend, input, textarea, p, blockquote, th, td{margin:0;padding:0;}
table{border-collapse:collapse;border-spacing:0;}
fieldset, img{border:0;}
address, caption, cite, code, dfn,  th, var{font-style:normal;font-weight:normal;}
ol, ul{list-style:none;}
caption, th{text-align:left;}
h1, h2, h3, h4, h5, h6{font-size:100%;font-weight:normal;}
q:before, q:after{content:'';}
abbr, acronym{border:0;font-variant:normal;}
sup{vertical-align:text-top;}
sub{vertical-align:text-bottom;}
input, textarea, select{font-family:inherit;font-size:inherit;font-weight:inherit;}
input, textarea, select{font-size:100%;}
legend{color:#000;}
html{background-color:#f8f7f5;}
body{background:#f8f7f5;color:#222;font-family:Helvetica, STHeiti STXihei, Microsoft JhengHei, Microsoft YaHei, Tohoma, Arial;height:100%;padding:15px 15px 0;position:relative;}
body > .tips{display:none;left:50%;padding:20px;position:fixed;text-align:center;top:50%;width:200px;z-index:100;}
.page{padding:15px;}
.page .page-error, .page .page-loading{line-height:30px;position:relative;text-align:center;}
.btn{background-color:#fcfcfc;border:1px solid #cccccc;border-radius:5px;box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);color:#222;cursor:pointer;display:block;font-size:15px;font-weight:bold;margin:15px 0;moz-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);padding:10px;text-align:center;text-decoration:none;webkit-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);}
.icons{background:url(../mobile/images/icons.png) no-repeat 0 0;border-radius:5px;height:25px;overflow:hidden;position:relative;width:25px;}
.icons.arrow-r{background:url(../mobile/images/brand_profileinweb_arrow@2x.png) no-repeat center center;background-size:100%;height:16px;width:12px;}
.icons.check{background-position:-25px 0;}
#activity-detail .page-bizinfo .header #activity-name{color:#000;font-size:20px;font-weight:bold;word-break:normal;word-wrap:break-word;}
#activity-detail .page-bizinfo .header #post-date{color:#8c8c8c;font-size:11px;margin:0;}
#activity-detail .page-bizinfo #biz-link.btn{background:url(../mobile/images/brand_profileinweb_bg@2x.png) no-repeat center center;background-size:100% 100%;border:none;border-radius:0;box-shadow:none;height:42px;padding:12px;padding-left:62px;position:relative;text-align:left;}
#activity-detail .page-bizinfo #biz-link.btn:hover{background-image:url(../mobile/images/brand_profileinweb_bg_HL@2x.png);}
#activity-detail .page-bizinfo #biz-link.btn .arrow{position:absolute;right:15px;top:25px;}
#activity-detail .page-bizinfo #biz-link.btn .logo{height:42px;left:5px;overflow:hidden;padding:6px;position:absolute;top:6px;width:42px;}
#activity-detail .page-bizinfo #biz-link.btn .logo img{position:relative;width:42px;z-index:10;}
#activity-detail .page-bizinfo #biz-link.btn .logo .circle{background:url(../mobile/images/brand_photo_middleframe@2x.png) no-repeat center center;background-size:100% 100%;height:54px;left:0;position:absolute;top:0;width:54px;z-index:100;}
#activity-detail .page-bizinfo #biz-link.btn #nickname{color:#454545;font-size:15px;text-shadow:0 1px 1px white;}
#activity-detail .page-bizinfo #biz-link.btn #weixinid{color:#a3a3a3;font-size:12px;line-height:20px;text-shadow:0 1px 1px white;}
#activity-detail .page-content{margin:18px 0 0;padding-bottom:18px;}
#activity-detail .page-content .media{margin:18px 0;}
#activity-detail .page-content .media img{width:100%;}
#activity-detail .page-content .text{color:#3e3e3e;font-size:1.5;line-height:1.5; width: 100%; overflow: hidden;zoom:1; }
#activity-detail .page-content .text p{min-height:1.5em;min-height: 1.5em;}
#activity-list .header{font-size:20px;}
#activity-list .page-list{border:1px solid #ccc;border-radius:5px;margin:18px 0;overflow:hidden;}
#activity-list .page-list .line.btn{border-radius:0;margin:0;text-align:left;}
#activity-list .page-list .line.btn .checkbox{height:25px;line-height:25px;padding-left:35px;position:relative;}
#activity-list .page-list .line.btn .checkbox .icons{background-color:#ccc;left:0;position:absolute;top:0;}
#activity-list .page-list .line.btn.off .icons{background-image:none;}
#activity-list #save.btn{background-image:linear-gradient(#22dd22, #009900);background-image:-moz-linear-gradient(#22dd22, #009900);background-image:-ms-linear-gradient(#22dd22, #009900);background-image:-o-linear-gradient(#22dd22, #009900);background-image:-webkit-gradient(linear, left top, left bottom, from(#22dd22), to(#009900));background-image:-webkit-linear-gradient(#22dd22, #009900);}
.vm{vertical-align:middle;}
.tc{text-align:center;}
.db{display:block;}
.dib{display:inline-block;}
.b{font-weight:700;}
.clr{clear:both;}
.text img{max-width:100%;}
.page-url{padding-top:18px;}
.page-url-link{color:#607FA6;font-size:14px;text-decoration:none;text-shadow:0 1px #ffffff;-webkit-text-shadow:0 1px #ffffff;-moz-text-shadow:0 1px #ffffff;}

#nickname{
overflow: hidden;
white-space: nowrap;
text-overflow: ellipsis;
max-width: 90%;
}
ol,ul{
list-style-position:inside;
}
</style>
</head>
<body id='activity-detail'>

<div class='page-bizinfo'>
<div class='header'>
<h1 id='activity-name'>$title</h1>
<span id='post-date'>2013-04-04</span>
</div>

</div>
<div class='page-content'>

<!-- div class='media'>
<img src='http://mmsns.qpic.cn/mmsns/FMlUjLFBQ6Lwklej5LibTvoCO2vTN7wWPOb681yMibWLKfsYiaaXhUTiaw/0' onerror='this.parentNode.removeChild(this)' />
</div -->

<div class='text'>
<p>
$desc
</p>
</div>


<br />
<div id='youkuplayer' style='width:320; height:240px'></div>
<br />

</div>

<script src='http://admin.wechat.com/static/mpaccount/js/jquery-1.7.2.min.js?v='></script>
<script>
(function(){
/**
* @description get a Max length for text, cut the long words
* @author zemzheng
**/
var
_dom = jQuery('.text'),
_html0 = _dom.html(),
_em = jQuery('<p></p>').html('a').css({display:'inline'}),
_init = function(){
_em.appendTo(_dom);
var
_html = _html0,
_max = Math.floor( _dom.width() / _em.width() ),
_reg = new RegExp('[a-z1-9]{' + _max + ',}', 'ig');
_em.remove();
_html = _html.replace(/>[^<]+</g,function(txt){
return txt.replace(_reg, function(str){
var _str = str, result = []
while(_str.length > _max){
result.push(
_str.substr(0, _max)
);
_str = _str.substr(_max);
}
result.push(_str);
return result.join('<br/>');
});
});
_dom.html(_html);
};
jQuery(window).on('resize', _init).trigger('resize');
})();
function getStrFromTxtDom(selector){
return jQuery('#txt-' + selector)
.html()
.replace(/&lt;/g, '<')
.replace(/&gt;/g, '>');
}
function viewSource(){
var UA = navigator.userAgent.toLowerCase();
var isIem = function(){
if(/IEMobile/i.test(UA)) return true;
else return false;
}
if(isIem()){
jQuery('.page-url-link:first').attr('href', getStrFromTxtDom('sourceurl') );
return ;
}
jQuery.ajax({
url: '/mp/appmsg/show-ajax' + location.search,//location.href,
async:false,
type:'POST',
timeout :2000,
data :{url:getStrFromTxtDom('sourceurl')},
complete:function(){location.href = getStrFromTxtDom('sourceurl');}
});
return false;
};
(function(){
var onBridgeReady = function () {
var
appId = '',
imgUrl = 'http://mmsns.qpic.cn/mmsns/FMlUjLFBQ6Lwklej5LibTvoCO2vTN7wWPOb681yMibWLKfsYiaaXhUTiaw/0',
link = 'http://admin.wechat.com/mp/appmsg/show?__biz=MjM5NjAwNTA0MA%3D%3D&appmsgid=10000081&itemidx=1#wechat_redirect',
title = getStrFromTxtDom('title'),
desc = getStrFromTxtDom('desc') || link;
// 发送给好友;
WeixinJSBridge.on('menu:share:appmessage', function(argv){
WeixinJSBridge.invoke('sendAppMessage',{
'appid' : appId,
'img_url' : imgUrl,
'img_width' : '640',
'img_height' : '640',
'link' : link,
'desc' : desc,
'title' : title
}, function(res) {})
});
// 分享到朋友圈;
WeixinJSBridge.on('menu:share:timeline', function(argv){
WeixinJSBridge.invoke('shareTimeline',{
'img_url' : imgUrl,
'img_width' : '640',
'img_height' : '640',
'link' : link,
'desc' : desc,
'title' : title
}, function(res) {
});
});
// 分享到微博;
var weiboContent = '';
WeixinJSBridge.on('menu:share:weibo', function(argv){
WeixinJSBridge.invoke('shareWeibo',{
'content' : title + link,
'url' : link
}, function(res) {
});
});
// 隐藏右上角的选项菜单入口;
//WeixinJSBridge.call('hideOptionMenu');
};
if(document.addEventListener){
document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
} else if(document.attachEvent){
document.attachEvent('WeixinJSBridgeReady' , onBridgeReady);
document.attachEvent('onWeixinJSBridgeReady' , onBridgeReady);
}
})();
</script>
</body>
</html>
";
			}elseif (preg_match ('#^(http://[a-zA-Z0-9-_./]+).{0,1024}?$#si', $Message, $matches))
  {
	/*
	 * 转换 URL 到微卡片
	 */
    $url = $matches[1];

	$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
    $html = file_get_contents ($url,false,$context);

	$charset = 'utf-8';
	if(preg_match('# charset=["\']?(.+?)[\'"]#si',$html,$matches))
	{
		$charset = strtolower($matches[1]);

		if('gb2312'==$charset)	$charset='gbk';
	}

	/*
	 * 确认网页的 charset 是正确的，否则按照 utf8 处理
	 */
    if ('utf-8'!=$charset && mb_check_encoding($html,$charset) )
	{
      $html = mb_convert_encoding ($html, 'utf-8', $charset);
	}


    if (function_exists ('tidy_parse_string'))
    {
      $tidy = tidy_parse_string ($html, array (), 'UTF8');
      $tidy->cleanRepair ();
      $html = $tidy->value;
    }

	require('readability/Readability.php');

    $readability = new Readability ($html, $url);
    $readability->debug = false;
    // convert links to footnotes?
    $readability->convertLinksToFootnotes = true;
    // process it
    $result = $readability->init ();
    // does it look like we found what we wanted?
    if ($result)
      {
	$title = $readability->getTitle ()->textContent;

	$content = $readability->getContent ()->innerHTML;
	// if we've got Tidy, let's clean it up for output
	if (function_exists ('tidy_parse_string'))
	  {
	    $tidy =
	      tidy_parse_string ($content, array ('indent' =>true, 'show-body-only' =>true), 'UTF8');
	    $tidy->cleanRepair ();
	    $content = $tidy->value;
	  }

	/*
	 * 将HTML中相对地址的图片修正为绝对地址
	 */
	$base_url = mb_substr($url,0,mb_strrpos($url,'/')+1);
	$content = preg_replace('#<img\s+[^>]*src=\s*["\'](?!http://)([^"]+)["\']#si',"<img src='$base_url$1'",$content);

	// 根目录的图片
/*
	$base_domain = mb_substr($url,0,mb_strpos($url,'/'));
	$content = preg_replace('#<img\s+[^>]*src=\s*["\'](?!http://)(/[^"]+)["\']#si',"<img src='$base_domain$1'",$content);
*/

	/*
	 * 提取纯文本格式的摘要描述信息
	 */
	$desc = preg_replace('/<[^>]+>/si','',$content);
	$desc = preg_replace('/&nbsp;/si',' ',$desc);
	$desc = preg_replace('/\s+/si',' ',$desc);
	$desc = trim($desc);

	$title 	= wfxDigest($title, 20);
	$desc 	= wfxDigest($desc, 200);

	$Message = "URLTITLE:$title\n$content";
	$thumbnail = "http://akamobi.com/weikapian/image/weifenxiang.jpg";

	if ( preg_match('#<img\s+[^>]*src=\s*["\'](http://[^"]+)["\']#si',$content,$matches) )
	{
		$thumbnail = $matches[1];
	}


      }
    else
      {
	$title = '未能获取URL，请尝试其它URL';
	$desc = 'URL微卡片转换功能正在测试中，敬请期待。';
      }

  }else{
				$title 	= wfxDigest($Message, 20);
				$desc 	= wfxDigest($Message, 200, mb_strlen($title) );

				if (!@$pts) $pts = new PicTmpStor();

				if ( !$thumbnail=$pts->load($fromUsername) )
					$thumbnail = "http://akamobi.com/weikapian/image/weifenxiang.jpg";

				$Message = "TXTIMG:$thumbnail\n" . make_clickable($Message);
			}


			$wfx	= new wfxStorage();

			$tid	= $wfx->save($Message);

			$xml = "
<xml>
<ToUserName><![CDATA[$fromUsername]]></ToUserName>
<FromUserName><![CDATA[$toUsername]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[$desc]]></Content>
<ArticleCount>1</ArticleCount>
<Articles> 
<item>
<Title><![CDATA[$title]]></Title> 
<Description><![CDATA[$desc]]></Description>
<PicUrl><![CDATA[$thumbnail]]></PicUrl>
<Url><![CDATA[http://akamobi.com/weikapian/wxapi.php?ac=news&tid=$tid]]></Url> 
</item>
</Articles>
<FuncFlag>0</FuncFlag>
</xml>
";

			break;
		}

	return $xml;
}

class wfxStorage
{
	var $mysqli;

		public function db_init()
		{
			$this->mysqli = new mysqli('localhost','wfx', '', 'weifenxiang');
		}

		public function save($message)
		{
			if ( !$this->mysqli )
				$this->db_init();

			$message = $this->mysqli->escape_string($message);

			$this->mysqli->query("INSERT INTO wfx (message) values ('$message')");

			return $this->mysqli->insert_id;
		}

		public function load($id)
		{
			if ( !$this->mysqli )
				$this->db_init();

			$id = $this->mysqli->escape_string($id);

			$query = "SELECT message FROM wfx WHERE id=$id";

			$result = $this->mysqli->query($query);

	        $obj = $result->fetch_object();

			return $obj->message;
		}
}

function wfxTrace($info)
{
		$fd = fopen( "/tmp/wfx.log", "a+" );
		fputs($fd, time() . " $info\n");
		fclose($fd);
}

function wfxDebug($postObj, $info)
{
		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$Location_X = $postObj->Location_X;
		$Location_Y = $postObj->Location_Y;
		$Scale = $postObj->Scale;
		$Label = $postObj->Label;
		$PicUrl = $postObj->PicUrl;
		$MsgType = $postObj->MsgType;
		$MsgId  = $postObj->MsgId;
		$Url = $postObj->Url;
		$Event = $postObj->Event;
		$Latitude = $postObj->Latitude;
		$Longitude = $postObj->Longitude;
		$Precision = $postObj->Precision;
		$EventKey = $postObj->EventKey;
		$Message = trim($postObj->Content);
		$token = TOKEN;

		$time = time();

		return "
<xml>
<ToUserName><![CDATA[$fromUsername]]></ToUserName>
<FromUserName><![CDATA[$toUsername]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[$info]]></Content>
<FuncFlag>0</FuncFlag>
</xml>
		";
}

function wfxDigest($message, $len=20, $start=0)
{
	$digest = mb_substr($message,$start,$len);

	$pos1 = mb_strrpos($digest,"\n") ;

	if ( false===$pos1 ){
		$pos2 = mb_strrpos($digest,"。") ;
		$pos3 = mb_strrpos($digest,".") ;
		$pos4 = mb_strrpos($digest,"！") ;
		$pos5 = mb_strrpos($digest," ") ;
		$pos6 = mb_strrpos($digest,"　") ;
	}

	if ($pos1 || $pos2 || $pos3 || $pos4 || $pos5 || $pos6)
	{
		$len = @max($pos1, $pos2, $pos3, $pos4, $pos5, $pos6);
		$digest = mb_substr($digest, $start, $len);
	}

	return $digest;
}

function wfxHtml($message,$tid=0)
{
	//XXX youku
	if (preg_match("#906075cd694c0aae#",$message))
		return $message;

    $image = "http://akamobi.com/weikapian/image/weifenxiang.jpg";

	if (preg_match("/^URLTITLE:(.+?)\n(.*)$/s",$message,$matches) )
	{
		//XXX URL
		$title = $matches[1];
		$desc = $title;
		$message = $matches[2];
		$image = '';
/* double image bug
		if ( preg_match('#<img\s+[^>]*src=\s*["\'](http://[^"]+)["\']#si',$message,$matches) )
		{
			$image = $matches[1];
		}
*/
	}elseif (preg_match("/^TXTIMG:(.+?)\n(.*)$/s",$message,$matches) )
	{
		//XXX PIC&TXT
		$image = $matches[1];
		$message = $matches[2];
		$title = wfxDigest($message,20);
		$desc = wfxDigest($message,120);
		$message = preg_replace('/\n/', '</p><p>', $message);
	}else{
		$title = wfxDigest($message,20);
		$desc = wfxDigest($message,120);
		$message = preg_replace('/\n/', '</p><p>', $message);
	}


	$image_html="";

	if ( strlen($image) )
	{
		$image_html="
<div class='media'>
<img src='$image' onerror='this.parentNode.removeChild(this)' />
</div>
";
	}


	return "
<!DOCTYPE html>
<html>

<head>
<title>$title</title>
<meta http-equiv=Content-Type content='text/html;charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
<meta name='apple-mobile-web-app-capable' content='yes'>
<meta name='apple-mobile-web-app-status-bar-style' content='black'>
<meta name='format-detection' content='telephone=no'>
<style>
html{background:#FFF;color:#000;}
body, div, dl, dt, dd, h1, h2, h3, h4, h5, h6, pre, code, form, fieldset, legend, input, textarea, p, blockquote, th, td{margin:0;padding:0;}
table{border-collapse:collapse;border-spacing:0;}
fieldset, img{border:0;}
address, caption, cite, code, dfn,  th, var{font-style:normal;font-weight:normal;}
ol, ul{list-style:none;}
caption, th{text-align:left;}
h1, h2, h3, h4, h5, h6{font-size:100%;font-weight:normal;}
q:before, q:after{content:'';}
abbr, acronym{border:0;font-variant:normal;}
sup{vertical-align:text-top;}
sub{vertical-align:text-bottom;}
input, textarea, select{font-family:inherit;font-size:inherit;font-weight:inherit;}
input, textarea, select{font-size:100%;}
legend{color:#000;}
html{background-color:#f8f7f5;}
body{background:#f8f7f5;color:#222;font-family:Helvetica, STHeiti STXihei, Microsoft JhengHei, Microsoft YaHei, Tohoma, Arial;height:100%;padding:15px 15px 0;position:relative;}
body > .tips{display:none;left:50%;padding:20px;position:fixed;text-align:center;top:50%;width:200px;z-index:100;}
.page{padding:15px;}
.page .page-error, .page .page-loading{line-height:30px;position:relative;text-align:center;}
.btn{background-color:#fcfcfc;border:1px solid #cccccc;border-radius:5px;box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);color:#222;cursor:pointer;display:block;font-size:15px;font-weight:bold;margin:15px 0;moz-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);padding:10px;text-align:center;text-decoration:none;webkit-box-shadow:0 1px 4px rgba(0, 0, 0, 0.3);}
.icons{background:url(../mobile/images/icons.png) no-repeat 0 0;border-radius:5px;height:25px;overflow:hidden;position:relative;width:25px;}
.icons.arrow-r{background:url(../mobile/images/brand_profileinweb_arrow@2x.png) no-repeat center center;background-size:100%;height:16px;width:12px;}
.icons.check{background-position:-25px 0;}
#activity-detail .page-bizinfo .header #activity-name{color:#000;font-size:20px;font-weight:bold;word-break:normal;word-wrap:break-word;}
#activity-detail .page-bizinfo .header #post-date{color:#8c8c8c;font-size:11px;margin:0;}
#activity-detail .page-bizinfo #biz-link.btn{background:url(../mobile/images/brand_profileinweb_bg@2x.png) no-repeat center center;background-size:100% 100%;border:none;border-radius:0;box-shadow:none;height:42px;padding:12px;padding-left:62px;position:relative;text-align:left;}
#activity-detail .page-bizinfo #biz-link.btn:hover{background-image:url(../mobile/images/brand_profileinweb_bg_HL@2x.png);}
#activity-detail .page-bizinfo #biz-link.btn .arrow{position:absolute;right:15px;top:25px;}
#activity-detail .page-bizinfo #biz-link.btn .logo{height:42px;left:5px;overflow:hidden;padding:6px;position:absolute;top:6px;width:42px;}
#activity-detail .page-bizinfo #biz-link.btn .logo img{position:relative;width:42px;z-index:10;}
#activity-detail .page-bizinfo #biz-link.btn .logo .circle{background:url(../mobile/images/brand_photo_middleframe@2x.png) no-repeat center center;background-size:100% 100%;height:54px;left:0;position:absolute;top:0;width:54px;z-index:100;}
#activity-detail .page-bizinfo #biz-link.btn #nickname{color:#454545;font-size:15px;text-shadow:0 1px 1px white;}
#activity-detail .page-bizinfo #biz-link.btn #weixinid{color:#a3a3a3;font-size:12px;line-height:20px;text-shadow:0 1px 1px white;}
#activity-detail .page-content{margin:18px 0 0;padding-bottom:18px;}
#activity-detail .page-content .media{margin:18px 0;}
#activity-detail .page-content .media img{width:100%;}
#activity-detail .page-content .text{color:#3e3e3e;font-size:1.5;line-height:1.5; width: 100%; overflow: hidden;zoom:1; }
#activity-detail .page-content .text p{min-height:1.5em;min-height: 1.5em;}
#activity-list .header{font-size:20px;}
#activity-list .page-list{border:1px solid #ccc;border-radius:5px;margin:18px 0;overflow:hidden;}
#activity-list .page-list .line.btn{border-radius:0;margin:0;text-align:left;}
#activity-list .page-list .line.btn .checkbox{height:25px;line-height:25px;padding-left:35px;position:relative;}
#activity-list .page-list .line.btn .checkbox .icons{background-color:#ccc;left:0;position:absolute;top:0;}
#activity-list .page-list .line.btn.off .icons{background-image:none;}
#activity-list #save.btn{background-image:linear-gradient(#22dd22, #009900);background-image:-moz-linear-gradient(#22dd22, #009900);background-image:-ms-linear-gradient(#22dd22, #009900);background-image:-o-linear-gradient(#22dd22, #009900);background-image:-webkit-gradient(linear, left top, left bottom, from(#22dd22), to(#009900));background-image:-webkit-linear-gradient(#22dd22, #009900);}
.vm{vertical-align:middle;}
.tc{text-align:center;}
.db{display:block;}
.dib{display:inline-block;}
.b{font-weight:700;}
.clr{clear:both;}
.text img{max-width:100%;}
.page-url{padding-top:18px;}
.page-url-link{color:#607FA6;font-size:14px;text-decoration:none;text-shadow:0 1px #ffffff;-webkit-text-shadow:0 1px #ffffff;-moz-text-shadow:0 1px #ffffff;}

#nickname{
overflow: hidden;
white-space: nowrap;
text-overflow: ellipsis;
max-width: 90%;
}
ol,ul{
list-style-position:inside;
}
</style>
</head>
<body id='activity-detail'>

<div class='page-bizinfo'>
<div class='header'>
<h1 id='activity-name'>$title</h1>
<span id='post-date'>2013-04-04</span>
</div>

</div>
<div class='page-content'>
$image_html
<div class='text'>
$message
</div>

</div>

<script src='http://admin.wechat.com/static/mpaccount/js/jquery-1.7.2.min.js?v='></script>
<script>
(function(){
/**
* @description get a Max length for text, cut the long words
* @author zemzheng
**/
var
_dom = jQuery('.text'),
_html0 = _dom.html(),
_em = jQuery('<p></p>').html('a').css({display:'inline'}),
_init = function(){
_em.appendTo(_dom);
var
_html = _html0,
_max = Math.floor( _dom.width() / _em.width() ),
_reg = new RegExp('[a-z1-9]{' + _max + ',}', 'ig');
_em.remove();
_html = _html.replace(/>[^<]+</g,function(txt){
return txt.replace(_reg, function(str){
var _str = str, result = []
while(_str.length > _max){
result.push(
_str.substr(0, _max)
);
_str = _str.substr(_max);
}
result.push(_str);
return result.join('<br/>');
});
});
_dom.html(_html);
};
jQuery(window).on('resize', _init).trigger('resize');
})();
function getStrFromTxtDom(selector){
return jQuery('#txt-' + selector)
.html()
.replace(/&lt;/g, '<')
.replace(/&gt;/g, '>');
}
function viewSource(){
var UA = navigator.userAgent.toLowerCase();
var isIem = function(){
if(/IEMobile/i.test(UA)) return true;
else return false;
}
if(isIem()){
jQuery('.page-url-link:first').attr('href', getStrFromTxtDom('sourceurl') );
return ;
}
jQuery.ajax({
url: '/mp/appmsg/show-ajax' + location.search,//location.href,
async:false,
type:'POST',
timeout :2000,
data :{url:getStrFromTxtDom('sourceurl')},
complete:function(){location.href = getStrFromTxtDom('sourceurl');}
});
return false;
};
(function(){
var onBridgeReady = function () {
var
appId = '',
imgUrl = '$image',
link = 'http://akamobi.com/weikapian/wxapi.php?ac=news&tid=$tid',
title = getStrFromTxtDom('title'),
desc = getStrFromTxtDom('desc') || link;
// 发送给好友;
WeixinJSBridge.on('menu:share:appmessage', function(argv){
WeixinJSBridge.invoke('sendAppMessage',{
'appid' : appId,
'img_url' : imgUrl,
'img_width' : '640',
'img_height' : '640',
'link' : link,
'desc' : desc,
'title' : title
}, function(res) {})
});
// 分享到朋友圈;
WeixinJSBridge.on('menu:share:timeline', function(argv){
WeixinJSBridge.invoke('shareTimeline',{
'img_url' : imgUrl,
'img_width' : '640',
'img_height' : '640',
'link' : link,
'desc' : desc,
'title' : title
}, function(res) {
});
});
// 分享到微博;
var weiboContent = '';
WeixinJSBridge.on('menu:share:weibo', function(argv){
WeixinJSBridge.invoke('shareWeibo',{
'content' : title + link,
'url' : link
}, function(res) {
});
});
// 隐藏右上角的选项菜单入口;
//WeixinJSBridge.call('hideOptionMenu');
};
if(document.addEventListener){
document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
} else if(document.attachEvent){
document.attachEvent('WeixinJSBridgeReady' , onBridgeReady);
document.attachEvent('onWeixinJSBridgeReady' , onBridgeReady);
}
})();
</script>
<script id='txt-desc' type='txt/text'>$desc</script><script id='txt-title' type='txt/text'>$title</script><script id='txt-sourceurl' type='txt/text'></script>
</body>
</html>
";


}


class PicTmpStor{
	var $memcache;
	
	public function init()
	{
		$this->memcache = new Memcache;
		$this->memcache->connect('localhost', 11211) or die ("Could not connect");
	}

	public function save($openid, $imgurl)
	{
		if (!$this->memcache)
			$this->init();

		$memkey = "img:$openid";
wfxTrace("save($openid): $memkey , $imgurl");
		$this->memcache->set($memkey, $imgurl, false, 60+10) or die ("Failed to save data at the server");
	}

	public function load($openid)
	{
		if (!$this->memcache)
			$this->init();

		$memkey = "img:$openid";
		$imgurl = $this->memcache->get($memkey);
		$this->memcache->delete($memkey);
wfxTrace("load($openid): $memkey , $imgurl");

		return $imgurl;
	}
}


function _make_url_clickable_cb($matches) {
    $ret = '';
    $url = $matches[2];
    if ( empty($url) )
        return $matches[0];
    // removed trailing [.,;:] from URL
    if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
        $ret = substr($url, -1);
        $url = substr($url, 0, strlen($url)-1);
    }
    return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $ret;
}
function _make_web_ftp_clickable_cb($matches) {
    $ret = '';
    $dest = $matches[2];
    $dest = 'http://' . $dest;
    if ( empty($dest) )
        return $matches[0];
    // removed trailing [,;:] from URL
    if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
        $ret = substr($dest, -1);
        $dest = substr($dest, 0, strlen($dest)-1);
    }
    return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
}
function _make_email_clickable_cb($matches) {
    $email = $matches[2] . '@' . $matches[3];
    return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}
function make_clickable($ret) {
    $ret = ' ' . $ret;
    // in testing, using arrays here was found to be faster
    $ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
    $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
    $ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
    // this one is not in an array because we need it to run last, for cleanup of accidental links within links
    $ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
    $ret = trim($ret);
    return $ret;
}


?>
