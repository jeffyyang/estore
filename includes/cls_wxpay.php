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
require_once(ROOT_PATH . 'includes/lib_wxpay.php');
require_once(ROOT_PATH . 'includes/cls_transport.php');
require_once(ROOT_PATH . 'includes/shopex_json.php');

/* 微信支付模块主类 */
class wxpay
{
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
        $this->wxpay();
    }

    /**
     * 构造函数
     *
     * @access  public
     * @return  void
     */
    function wxpay()
    {
        /* 由于要包含init.php，所以这两个对象一定是存在的，因此直接赋值 */
        // $this->db = $GLOBALS['db'];
        // $this->ecs = $GLOBALS['ecs'];

        /* 此处最好不要从$GLOBALS数组里引用，防止出错 */
        // cls_transport.php
        // $this->t = new transport(-1, -1, -1, true);
        // cls_json.php
        // $this->json    = new Services_JSON;
    }
   
    /* 
     * 查询订单(支付单)
     */
    function queryWXPayment($order_sn = '')
    {

        //使用订单查询接口
        $orderQuery = new OrderQuery_pub();
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $orderQuery->setParameter("out_trade_no",$order_sn);//商户订单号 
        //非必填参数，商户可根据实际情况选填
        //$orderQuery->setParameter("sub_mch_id","XXXX");//子商户号  
        //$orderQuery->setParameter("transaction_id","XXXX");//微信订单号
        
        //获取订单查询结果
        $orderQueryResult = $orderQuery->getResult();
        
        //商户根据实际情况设置相应的处理流程,此处仅作举例
        if ($orderQueryResult["return_code"] == "FAIL") {
            echo "通信出错：".$orderQueryResult['return_msg']."<br>";

        }else{
            // echo "交易状态：".$orderQueryResult['trade_state']."<br>";
            // echo "设备号：".$orderQueryResult['device_info']."<br>";
            // echo "用户标识：".$orderQueryResult['openid']."<br>";
            // echo "是否关注公众账号：".$orderQueryResult['is_subscribe']."<br>";
            // echo "交易类型：".$orderQueryResult['trade_type']."<br>";
            // echo "付款银行：".$orderQueryResult['bank_type']."<br>";
            // echo "总金额：".$orderQueryResult['total_fee']."<br>";
            // echo "现金券金额：".$orderQueryResult['coupon_fee']."<br>";
            // echo "货币种类：".$orderQueryResult['fee_type']."<br>";
            // echo "微信支付订单号：".$orderQueryResult['transaction_id']."<br>";
            // echo "商户订单号：".$orderQueryResult['out_trade_no']."<br>";
            // echo "商家数据包：".$orderQueryResult['attach']."<br>";
            // echo "支付完成时间：".$orderQueryResult['time_end']."<br>";
        }   
        return $orderQueryResult;
    }

    
    /*    
     * 下载对账单
     */
    function downloadWXBill($bill_date ='',$bill_type = 'ALL'){

        $downloadBill = new DownloadBill_pub();
        //设置对账单接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $downloadBill->setParameter("bill_date",$bill_date);//对账单日期 
        $downloadBill->setParameter("bill_type",$bill_type);//账单类型 
        //非必填参数，商户可根据实际情况选填
        //$downloadBill->setParameter("device_info","XXXX");//设备号  

        //对账单接口结果
        $downloadBillResult = $downloadBill->getResult();
        echo $downloadBillResult['return_code'];
        
        if ($downloadBillResult['return_code'] == "FAIL") {
            echo "通信出错：".$downloadBillResult['return_msg'];
        }else{
            
             return $downloadBill->response;
        }
    }
    /*
     * 申请退款
     *
     */
    function applyWXRefund($order_sn ='', $refund_sn ='', $total_fee =0, $refund_fee =0){

        //使用退款接口
        $refundApply = new Refund_pub();
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $refundApply->setParameter("out_trade_no",$order_sn);//商户订单号
        $refundApply->setParameter("out_refund_no",$out_refund_no);//商户退款单号
        $refundApply->setParameter("total_fee",$total_fee);//总金额
        $refundApply->setParameter("refund_fee",$refund_fee);//退款金额
        $refundApply->setParameter("op_user_id",WxPayConf_pub::MCHID);//操作员
        //非必填参数，商户可根据实际情况选填
        //$refund->setParameter("sub_mch_id","XXXX");//子商户号 
        //$refund->setParameter("device_info","XXXX");//设备号 
        //$refund->setParameter("transaction_id","XXXX");//微信订单号

        $applyResult = $refundApply->getResult();

        if ($applyResult["return_code"] == "FAIL") {
            echo "通信出错：".$refundResult['return_msg']."<br>";
        }
        else{
            // echo "业务结果：".$refundResult['result_code']."<br>";
            // echo "错误代码：".$refundResult['err_code']."<br>";
            // echo "错误代码描述：".$refundResult['err_code_des']."<br>";
            // echo "公众账号ID：".$refundResult['appid']."<br>";
            // echo "商户号：".$refundResult['mch_id']."<br>";
            // echo "子商户号：".$refundResult['sub_mch_id']."<br>";
            // echo "设备号：".$refundResult['device_info']."<br>";
            // echo "签名：".$refundResult['sign']."<br>";
            // echo "微信订单号：".$refundResult['transaction_id']."<br>";
            // echo "商户订单号：".$refundResult['out_trade_no']."<br>";
            // echo "商户退款单号：".$refundResult['out_refund_no']."<br>";
            // echo "微信退款单号：".$refundResult['refund_idrefund_id']."<br>";
            // echo "退款渠道：".$refundResult['refund_channel']."<br>";
            // echo "退款金额：".$refundResult['refund_fee']."<br>";
            // echo "现金券退款金额：".$refundResult['coupon_refund_fee']."<br>";
        }
        return $applyResult;
    }

    // 退款查询
    function queryWXRefund($order_sn =''){

        $refundQuery = new RefundQuery_pub();
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $refundQuery->setParameter("out_trade_no","$out_trade_no");//商户订单号
        // $refundQuery->setParameter("out_refund_no","XXXX");//商户退款单号
        // $refundQuery->setParameter("refund_id","XXXX");//微信退款单号
        // $refundQuery->setParameter("transaction_id","XXXX");//微信退款单号
        //非必填参数，商户可根据实际情况选填
        //$refundQuery->setParameter("sub_mch_id","XXXX");//子商户号 
        //$refundQuery->setParameter("device_info","XXXX");//设备号 
        //退款查询接口结果
        $refundQueryResult = $refundQuery->getResult();

        //商户根据实际情况设置相应的处理流程,此处仅作举例
        if ($refundQueryResult["return_code"] == "FAIL") {
            echo "通信出错：".$refundQueryResult['return_msg']."<br>";
        }
        else{
            // echo "业务结果：".$refundQueryResult['result_code']."<br>";
            // echo "错误代码：".$refundQueryResult['err_code']."<br>";
            // echo "错误代码描述：".$refundQueryResult['err_code_des']."<br>";
            // echo "公众账号ID：".$refundQueryResult['appid']."<br>";
            // echo "商户号：".$refundQueryResult['mch_id']."<br>";
            // echo "子商户号：".$refundQueryResult['sub_mch_id']."<br>";
            // echo "设备号：".$refundQueryResult['device_info']."<br>";
            // echo "签名：".$refundQueryResult['sign']."<br>";
            // echo "微信订单号：".$refundQueryResult['transaction_id']."<br>";
            // echo "商户订单号：".$refundQueryResult['out_trade_no']."<br>";
            // echo "退款笔数：".$refundQueryResult['refund_count']."<br>";
            // echo "商户退款单号：".$refundQueryResult['out_refund_no']."<br>";
            // echo "微信退款单号：".$refundQueryResult['refund_idrefund_id']."<br>";
            // echo "退款渠道：".$refundQueryResult['refund_channel']."<br>";
            // echo "退款金额：".$refundQueryResult['refund_fee']."<br>";
            // echo "现金券退款金额：".$refundQueryResult['coupon_refund_fee']."<br>";
            // echo "退款状态：".$refundQueryResult['refund_status']."<br>";
        }
        return $refundQueryResult;
    }

}

?>