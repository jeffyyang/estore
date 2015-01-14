<?php

/**
 * ECSHOP 微信模块 之 模型（类库）
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: JeffyYang $
 * $Id: cls_wechat.php 17155 2010-05-06 06:29:05Z douqinghua $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

define('APP_ID', 'wxba08a98647000606');
define('APP_SECRET', '5a75902adcedc48fdd9d472152bdb841');
require_once(ROOT_PATH . 'includes/cls_transport.php');
require_once(ROOT_PATH . 'includes/shopex_json.php');

/* 微信模块主类 */
class wechat
{
    /**
     * 存放提供远程服务的URL。
     *
     * @access  private
     * @var     array       $api_urls
     */
    var $api_urls   = array(
                            'access_token'           =>  'https://api.weixin.qq.com/cgi-bin/token',
                            'tpl_send'               =>  'https://api.weixin.qq.com/cgi-bin/message/template/send', 
                            'custom_send'            =>  'https://api.weixin.qq.com/cgi-bin/message/custom/send',
                            'mass_send'              =>  'https://api.weixin.qq.com/cgi-bin/message/mass/send',
                            'mass_send_all'          =>  'https://api.weixin.qq.com/cgi-bin/message/mass/sendall',
                            'upload_media'           =>  'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE',
                            'upload_news'            =>  'https://api.weixin.qq.com/cgi-bin/media/uploadnews',
                            'creat_menu'             =>  'https://api.weixin.qq.com/cgi-bin/menu/create',
                            'get_menu'               =>  'https://api.weixin.qq.com/cgi-bin/menu/get', 
                            'delete_menu'            =>  'https://api.weixin.qq.com/cgi-bin/menu/delete',                             
                            'creat_group'            =>  'https://api.weixin.qq.com/cgi-bin/groups/create',
                            'get_group'              =>  'https://api.weixin.qq.com/cgi-bin/groups/get',
                            'get_group_of_user'      =>  'https://api.weixin.qq.com/cgi-bin/groups/getid',
                            'update_group_name'      =>  'https://api.weixin.qq.com/cgi-bin/groups/update',
                            'move_members_of_group'  =>  'https://api.weixin.qq.com/cgi-bin/groups/members/update',
                            'get_user'               =>  'https://api.weixin.qq.com/cgi-bin/user/get',
                            'get_user_info'          =>  'https://api.weixin.qq.com/cgi-bin/user/info'
    
    );

    /**
     * 存放MYSQL对象
     *
     * @access  private
     * @var     object      $db
     */
    var $db         = null;

    /**
     * 存放ECS对象
     *
     * @access  private
     * @var     object      $ecs
     */
    var $ecs        = null;

    /**
     * 存放transport对象
     *
     * @access  private
     * @var     object      $t
     */
    var $t          = null;

    /**
     * 存放程序执行过程中的错误信息，这样做的一个好处是：程序可以支持多语言。
     * 程序在执行相关的操作时，error_no值将被改变，可能被赋为空或大等0的数字.
     * 为空或0表示动作成功；大于0的数字表示动作失败，该数字代表错误号。
     *
     * @access  public
     * @var     array       $errors
     */
    var $errors  = array('api_errors'       => array('error_no' => -1, 'error_msg' => ''),
                         'server_errors'    => array('error_no' => -1, 'error_msg' => ''));

    /**
     * 构造函数
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->wechat();
    }

    /**
     * 构造函数
     *
     * @access  public
     * @return  void
     */
    function wechat()
    {
        /* 由于要包含init.php，所以这两个对象一定是存在的，因此直接赋值 */
        $this->db = $GLOBALS['db'];
        $this->ecs = $GLOBALS['ecs'];

        /* 此处最好不要从$GLOBALS数组里引用，防止出错 */
        // cls_transport.php
        $this->t = new transport(-1, -1, -1, true);
        // cls_json.php
        $this->json    = new Services_JSON;
    }
   
    /* 群发发送微信消息
     *
     * @access  public
     * @param   arry    $group_ids       要发送到哪些个手机号码，传的值是一个数组
     * @param   string  $msg             发送的消息内容
     */
    function sendMessage($group_id, $msg_type='text', $msg, $media_id = '-1')
    {

        $send_str = array();

        $send_str['filter'] = array('group_id' => $group_id);

        $send_str['text'] = array('content' => $msg);

        $send_str['msgtype'] = 'text';

        $post_json_body = $this->json->encode($send_str);

        $result = $this->mass_send_all($post_json_body);

        return $result;
    }

    /* 发送发货提示消息
     *
     * @access  public
     * @param   arry    $open_id      
     * @param   string  $msg             发送的消息内容
     */
    function sendDeliverMessage($open_id, $deliver_name, $deliver_order_sn)
    {
        $tpl_id = 'S-EIiYAIcqEjCx4a0g8CAhEtHwANopQzm6LYUqz3tMs';
        $send_str = array();
        $send_str['touser'] = $open_id;
        $send_str['template_id'] = $tpl_id;
        $send_str['url'] = 'http://weixin.qq.com/download';
        $send_str['topcolor'] = '#FF0000';

        $send_str['data']['first'] = array('value' => '亲，宝贝已经启程了，好想快点来到你身边', 'color' => '#173177');
        $send_str['data']['delivername'] = array('value' => $deliver_name, 'color' => '#173177');
        $send_str['data']['ordername'] = array('value' => $deliver_order_sn, 'color' => '#173177');
        $send_str['data']['remark'] = array('value' => '如果疑问，请在微信服务号中输入“KF”，**将在第一时间为您服务！', 'color' => '#173177');

        $post_json_body = $this->json->encode($send_str);
        $result = $this->tpl_send($post_json_body);
        return $result;
    }

    /* 发送退款完成消息
     *
     * @access  public
     * @param   arry    $open_id      
     * @param   string  $msg       发送的消息内容
     */
    function sendRefundEndMessage($open_id, $reason, $refund_amount)
    {
        $tpl_id = 'c-jyJ4vx3KOr60SllhWh09rYkGSpp2nR9ZMDEUVVEZE';
        $send_str = array();
        $send_str['touser'] = $open_id;
        $send_str['template_id'] = $tpl_id;
        $send_str['url'] = 'http://weixin.qq.com/download';
        $send_str['topcolor'] = '#FF0000';

        $send_str['data']['first'] = array('value' => '您好，您对微信影城影票的抢购未成功，已退款。', 'color' => '#173177');
        $send_str['data']['reason'] = array('value' => $reason, 'color' => '#173177');
        $send_str['data']['refund'] = array('value' => $refund_amount, 'color' => '#173177');
        $send_str['data']['remark'] = array('value' => '备注：如有疑问，请致电13912345678联系我们，或回复M来了解详情', 'color' => '#173177');

        $post_json_body = $this->json->encode($send_str);
        $result = $this->tpl_send($post_json_body);
        return $result;
    }

    
    // 群组发消息
    function mass_send_all($json_body){

        $access_token = $this->get_cached_access_token();
        $api_url = $this->get_url('mass_send_all').'?access_token='.$access_token;
        $response = $this->t->request($api_url, $json_body, 'POST');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }

    // OPenID列表发消息
    function mass_send($json_body){

        $access_token = $this->get_cached_access_token();
        $api_url = $this->get_url('mass_send').'?access_token='.$access_token;
        $response = $this->t->request($api_url, $json_body,'POST');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }

    // OPenID列表发消息
    function tpl_send($json_body){
        $access_token = $this->get_cached_access_token();
        $api_url = $this->get_url('tpl_send').'?access_token='.$access_token;
        $response = $this->t->request($api_url, $json_body,'POST');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }

    // 创建菜单
    function create_menu($json_body){

        $access_token = $this->get_cached_access_token();
        $api_url = $this->get_url('creat_menu').'?access_token='.$access_token;;
        $response = $this->t->request($api_url, $json_body,'POST');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }


    // 获得菜单
    function get_menu(){

        $Tsend_str['access_token'] = $this->get_cached_access_token();
        $api_url = $this->get_url('get_menu');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        print_r($result);
        return $result;
    }

    // 删除菜单
    function delete_menu(){

        $Tsend_str['access_token'] = $this->get_cached_access_token();
        $api_url = $this->get_url('delete_menu');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }

    // 获得公众号的分组
    function get_group(){

        $Tsend_str['access_token'] = $this->get_cached_access_token();
        $api_url = $this->get_url('get_group');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        return $result['groups'];
    }

    // 获得关注用户
    function get_user($next_openid = ''){

        $Tsend_str['access_token'] = $this->get_cached_access_token();
        if($next_openid != ''){
            $Tsend_str['next_openid'] = $next_openid;
        }
        $api_url = $this->get_url('get_user');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }

    // 获得粉丝用户信息
    function get_user_info($openid = ''){

        $Tsend_str['access_token'] = $this->get_cached_access_token();
        $Tsend_str['openid'] = $openid;
        $Tsend_str['lang'] = 'zh_CN';
        $api_url = $this->get_url('get_user_info');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        return $result;
    }      

    //获得微信公众号access_token
    function get_cached_access_token(){
        static $res = NULL;
        if ($res === NULL)
        {
            $data = read_static_cache('wx_access_token_pid_releate');
            if ($data === false)
            {
                echo "no cached ! \n";
                $res = $this->get_access_token();
                $res['create_time'] = time();
                write_static_cache('wx_access_token_pid_releate', $res);

            }
            else
            {
                $res = $data;
                $access_token_create_time = $res['create_time'];
                $access_token_expires_in  = $res['expires_in'];
                // 过期
                if(abs(time() - $access_token_create_time) > $access_token_expires_in ){
                    echo "access_token is expired ! \n ";
                    $res = $this->get_access_token();
                    $res['create_time'] = time();
                    write_static_cache('wx_access_token_pid_releate', $res);
                }
            }
        }
        return $res['access_token'];
    }

   function get_access_token(){

        $Tsend_str['grant_type'] = 'client_credential';
        $Tsend_str['appid'] = APP_ID;
        $Tsend_str['secret'] = APP_SECRET ;
        $api_url = $this->get_url('access_token');
        $response = $this->t->request($api_url, $Tsend_str,'GET');
        $result = $this->json->decode($response['body'], true);
        return $result;
   }

     /**
     * 返回指定键名的URL
     *
     * @access  public
     * @param   string      $key        URL的名字，即数组的键名
     * @return  string or boolean       如果由形参指定的键名对应的URL值存在就返回该URL，否则返回false。
     */
    function get_url($key)
    {
        $url = $this->api_urls[$key];

        if (empty($url))
        {
            return false;
        }
        return $url;
    }
    /**
     * 检测手机号码是否正确
     *
     */
    // function is_moblie($moblie)
    // {
    //    return  preg_match("/^0?1((3|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
    // }
   
    //加密算法
    // function make_shopex_ac($temp_arr,$token)
    // {
    //    ksort($temp_arr);
    //    $str = '';
    //    foreach($temp_arr as $key=>$value)
    //    {
    //         if($key!='certi_ac') 
    //         {
    //            $str.= $value;
    //         }
    //     }
    //    return strtolower(md5($str.strtolower(md5($token))));
    //  }

    // function base_encode($str)
    // {
    //     $str = base64_encode($str);
    //     return strtr($str, $this->pattern());
    // }
    // function pattern()
    // {
    //     return array(
    //     '+'=>'_1_',
    //     '/'=>'_2_',
    //     '='=>'_3_',
    //     );
    // }
    
    //检查手机号和发送的内容并生成生成短信队列
     // function get_contents($phones,$msg)
     // {
     //    if (empty($phones) || empty($msg))
     //    {
     //        return false;
     //    }
     //    $msg.='【'. $GLOBALS['_CFG']['shop_name'].'】';
     //    $phone_key=0;
     //    $i=0;
     //    $phones=explode(',',$phones);
     //    foreach($phones as $key => $value)
     //    {
     //         if($i<200)
     //         {
     //            $i++;
     //         }
     //         else
     //         {
     //           $i=0;
     //           $phone_key++;
     //         }
     //         if($this->is_moblie($value))
     //         {
     //            $phone[$phone_key][]=$value;
     //         }
     //         else
     //         {
     //             $i--;
     //         }
     //     }
     //     if(!empty($phone))
     //     {
     //         foreach($phone as $phone_key => $val)
     //         {
     //               if (EC_CHARSET != 'utf-8')
     //                {
     //                    $phone_array[$phone_key]['phones']=implode(',',$val);
     //                    $phone_array[$phone_key]['content']=iconv('gb2312','utf-8',$msg);
     //                }
     //              else
     //               {
     //                    $phone_array[$phone_key]['phones']=implode(',',$val);
     //                    $phone_array[$phone_key]['content']=$msg;
     //               }
                  
     //         }
     //         return $phone_array;
     //     }
     //     else
     //     {
     //        return false; 
     //     }
         
     // }
    
 /* 发送微信消息
     *
     * @access  public
     * @param   string  $phone          要发送到哪些个手机号码，传的值是一个数组
     * @param   string  $msg            发送的消息内容
     */
    function send($phones,$msg, $msg_type='text',$version='1.0')
    {
       
        // /* 检查发送信息的合法性 */
        // $contents=$this->get_contents($phones, $msg);  
        // if(!$contents)
        // {
        //     $this->errors['server_errors']['error_no'] = 3;//发送的信息有误
        //     return false;
        // }

        // }
        //  /* 获取API URL */
        // $sms_url = $this->get_url('send');

        // if (!$sms_url)
        // {
        //     $this->errors['server_errors']['error_no'] = 6;//URL不对

        //     return false;
        // }

        // $t_contents=array();
        // if(count($contents)>1)
        // {
        //     foreach ($contents as $key=>$val)
        //     {
        //         $t_contents['0']['phones']=$val['phones'];
        //         $t_contents['0']['content']=$val['content'];
        //         $send_str['contents']= $this->json->encode($t_contents);
        //         $send_str['certi_app']='sms.send';
        //         $send_str['entId']=$GLOBALS['_CFG']['ent_id'];
        //         $send_str['entPwd']=$GLOBALS['_CFG']['ent_ac'];
        //         $send_str['license']=$GLOBALS['_CFG']['certificate_id'];
        //         $send_str['source']=SOURCE_ID;

        //         $send_str['sendType'] = 'fan-out';
        //         $send_str['use_backlist'] = '1';
        //         $send_str['version'] = $version;
        //         $send_str['format']='json'; 
        //         $send_str['timestamp'] = $this->getTime(); 
        //         $send_str['certi_ac']=$this->make_shopex_ac($send_str,SOURCE_TOKEN);
        //         $sms_url= $this->get_url('send');
        //         $arr = json_decode($send_str['contents'],true);
        //         /* 发送HTTP请求 */
        //         $response = $this->t->request($sms_url, $send_str,'POST');
        //         $result = $this->json->decode($response['body'], true);
        //         sleep(1);
        //     }
        // }
        // else
        // {
        //     if(strlen($contents['0']['phones'])>20)
        //     {
        //         $send_str['sendType'] = 'fan-out';
        //     }
        //     else
        //     {
        //          $send_str['sendType'] = 'notice';
        //     }
        //     $send_str['contents']= $this->json->encode($contents);
        //     $send_str['certi_app']='sms.send';
        //     $send_str['entId']=$GLOBALS['_CFG']['ent_id'];
        //     $send_str['entPwd']=$GLOBALS['_CFG']['ent_ac'];
        //     $send_str['license']=$GLOBALS['_CFG']['certificate_id'];
        //     $send_str['source']=SOURCE_ID;

        //     $send_str['use_backlist'] = '1';
        //     $send_str['version'] = $version;
        //     $send_str['format']='json'; 
        //     $send_str['timestamp'] = $this->getTime(); 
        //     $send_str['certi_ac']=$this->make_shopex_ac($send_str,SOURCE_TOKEN);
        //     $sms_url= $this->get_url('send');
        //     $arr = json_decode($send_str['contents'],true);
        //     /* 发送HTTP请求 */
        //     $response = $this->t->request($sms_url, $send_str,'POST');
        //     $result = $this->json->decode($response['body'], true);
        // }

        // if($result['res'] == 'succ')
        // {
        //     return true;
        // }
        // elseif($result['res'] == 'fail')
        // {
        //     return false;
        // }
       
    }
   

}

?>