<?php

/**
 * ECSHOP 管理中心菜单数组
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: inc_menu.php 17217 2011-01-19 06:29:08Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}


$modules['01_shop']['shopcate_list']                = 'shopcate.php?act=list';         // 商户分类列表
$modules['01_shop']['shop_list'] 					          = 'shops.php?act=list';            // 商户列表
$modules['01_shop']['suppliers_list']         		  = 'suppliers.php?act=list';        // 门店列表

$modules['02_cat_and_goods']['01_goods_list']       = 'goods.php?act=list';         // 商品列表
$modules['02_cat_and_goods']['02_goods_add']        = 'goods.php?act=add';          // 添加商品
$modules['02_cat_and_goods']['03_category_list']    = 'category.php?act=list';
// $modules['02_cat_and_goods']['05_comment_manage']   = 'comment_manage.php?act=list';
// $modules['02_cat_and_goods']['06_goods_brand_list'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type']       = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['11_goods_trash']      = 'goods.php?act=trash';        // 商品回收站
// $modules['02_cat_and_goods']['12_batch_pic']        = 'picture_batch.php';
$modules['02_cat_and_goods']['13_batch_add']        = 'goods_batch.php?act=add';    // 商品批量上传
// $modules['02_cat_and_goods']['14_goods_export']     = 'goods_export.php?act=goods_export';
// $modules['02_cat_and_goods']['15_batch_edit']       = 'goods_batch.php?act=select'; // 商品批量修改
// $modules['02_cat_and_goods']['16_goods_script']     = 'gen_goods_script.php?act=setup';
// $modules['02_cat_and_goods']['17_tag_manage']       = 'tag_manage.php?act=list';
// $modules['02_cat_and_goods']['50_virtual_card_list']   = 'goods.php?act=list&extension_code=virtual_card';
// $modules['02_cat_and_goods']['51_virtual_card_add']    = 'goods.php?act=add&extension_code=virtual_card';
// $modules['02_cat_and_goods']['52_virtual_card_change'] = 'virtual_card.php?act=change';
$modules['02_cat_and_goods']['goods_auto']          = 'goods_auto.php?act=list';
// $modules['02_cat_and_goods']['53_suppliers_goods']  = 'suppliers_goods.php?act=list';  // 商户商品列表

// 促销管理
/*$modules['03_promotion']['02_snatch_list']          = 'snatch.php?act=list';
$modules['03_promotion']['04_bonustype_list']       = 'bonus.php?act=list';
$modules['03_promotion']['06_pack_list']            = 'pack.php?act=list';
$modules['03_promotion']['07_card_list']            = 'card.php?act=list';
$modules['03_promotion']['08_group_buy']            = 'group_buy.php?act=list';
$modules['03_promotion']['09_topic']                = 'topic.php?act=list';
$modules['03_promotion']['10_auction']              = 'auction.php?act=list';
$modules['03_promotion']['12_favourable']           = 'favourable.php?act=list';
$modules['03_promotion']['13_wholesale']            = 'wholesale.php?act=list';   // 批发管理
$modules['03_promotion']['14_package_list']         = 'package.php?act=list';
//$modules['03_promotion']['ebao_commend']            = 'ebao_commend.php?act=list';
$modules['03_promotion']['15_exchange_goods']       = 'exchange_goods.php?act=list';*/

// 订单管理
$modules['04_order']['02_group_order_list']         = 'order.php?act=list';
$modules['04_order']['02_integral_order_list']      = 'order.php?act=list&src_type=2';
$modules['04_order']['03_order_query']              = 'order.php?act=order_query';
// $modules['04_order']['04_merge_order']              = 'order.php?act=merge'; 
$modules['04_order']['05_edit_order_print']         = 'order.php?act=templates';
$modules['04_order']['06_undispose_booking']        = 'goods_booking.php?act=list_all';
// $modules['04_order']['07_repay_application']        = 'repay.php?act=list_all';
$modules['04_order']['08_add_order']                = 'order.php?act=add';
$modules['04_order']['09_delivery_order']           = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order']               = 'order.php?act=back_list';
$modules['04_order']['11_excode_verify']            = 'excode.php?act=list';
$modules['04_order']['11_refund_list']              = 'refund.php?act=list';
$modules['04_order']['12_complaints_list']          = 'complaints.php?act=list';

// 结算管理
$modules['05_settle']['01_batch_bill_import']       = 'bill_batch.php?act=add';
$modules['05_settle']['02_bill_list']               = 'bill.php?act=list';
$modules['05_settle']['03_suppliers_bill_list']     = 'bill.php?act=list';

// 
// 广告管理
// $modules['05_banner']['ad_position']                = 'ad_position.php?act=list';
// $modules['05_banner']['ad_list']                    = 'ads.php?act=list';

// 报表统计
// $modules['06_stats']['flow_stats']                  = 'flow_stats.php?act=view';
// $modules['06_stats']['searchengine_stats']          = 'searchengine_stats.php?act=view';
// $modules['06_stats']['z_clicks_stats']              = 'adsense.php?act=list';
$modules['06_stats']['report_guest']                = 'guest_stats.php?act=list';
$modules['06_stats']['report_order']                = 'order_stats.php?act=list';
$modules['06_stats']['report_sell']                 = 'sale_general.php?act=list';
$modules['06_stats']['sale_list']                   = 'sale_list.php?act=list';
$modules['06_stats']['sell_stats']                  = 'sale_order.php?act=goods_num';
$modules['06_stats']['report_users']                = 'users_order.php?act=order_num';

// $modules['06_stats']['visit_buy_per']               = 'visit_sold.php?act=list';

// 文章管理
// $modules['07_content']['03_article_list']           = 'article.php?act=list';
// $modules['07_content']['02_articlecat_list']        = 'articlecat.php?act=list';
// $modules['07_content']['vote_list']                 = 'vote.php?act=list';
// $modules['07_content']['article_auto']              = 'article_auto.php?act=list';
// $modules['07_content']['shop_help']                 = 'shophelp.php?act=list_cat';
// $modules['07_content']['shop_info']                 = 'shopinfo.php?act=list';

// 会员管理

/*$modules['08_members']['03_users_list']             = 'users.php?act=list';
$modules['08_members']['04_users_add']              = 'users.php?act=add';
$modules['08_members']['05_user_rank_list']         = 'user_rank.php?act=list';
$modules['08_members']['06_list_integrate']         = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg']            = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account']           = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage']    = 'user_account_manage.php?act=list';*/

// 权限管理
$modules['10_priv_admin']['admin_logs']             = 'admin_logs.php?act=list';
$modules['10_priv_admin']['admin_list']             = 'privilege.php?act=list';
$modules['10_priv_admin']['admin_role']             = 'role.php?act=list';
// $modules['10_priv_admin']['bank_agency_list']       = 'bank_agency.php?act=list';    // 银行网点
// $modules['10_priv_admin']['agency_list']            = 'agency.php?act=list';    // 办事处
// $modules['10_priv_admin']['suppliers_list']         = 'suppliers.php?act=list'; // 供货商

// 系统管理
// $modules['11_system']['01_shop_config']             = 'shop_config.php?act=list_edit';
// $modules['11_system']['shop_authorized']             = 'license.php?act=list_edit';
// $modules['11_system']['02_payment_list']            = 'payment.php?act=list';
// $modules['11_system']['03_shipping_list']           = 'shipping.php?act=list';
// $modules['11_system']['04_mail_settings']           = 'shop_config.php?act=mail_settings';
// $modules['11_system']['05_area_list']               = 'area_manage.php?act=list';
// $modules['11_system']['06_plugins']                 = 'plugins.php?act=list';
// $modules['11_system']['07_cron_schcron']            = 'cron.php?act=list';   // 定时任务
// $modules['11_system']['08_friendlink_list']         = 'friend_link.php?act=list';
// $modules['11_system']['sitemap']                    = 'sitemap.php';
// $modules['11_system']['check_file_priv']            = 'check_file_priv.php?act=check';
// $modules['11_system']['captcha_manage']             = 'captcha_manage.php?act=main';
// $modules['11_system']['ucenter_setup']              = 'integrate.php?act=setup&code=ucenter';
// $modules['11_system']['flashplay']                  = 'flashplay.php?act=list';
// $modules['11_system']['navigator']                  = 'navigator.php?act=list';
// $modules['11_system']['file_check']                 = 'filecheck.php';
// $modules['11_system']['fckfile_manage']             = 'fckfile_manage.php?act=list';
// $modules['11_system']['021_reg_fields']             = 'reg_fields.php?act=list';
$modules['11_system']['bank_agency_list']              = 'bank_agency.php?act=list';    // 银行网点

// 模板管理
// $modules['12_template']['02_template_select']       = 'template.php?act=list';
// $modules['12_template']['03_template_setup']        = 'template.php?act=setup';
// $modules['12_template']['04_template_library']      = 'template.php?act=library';
// $modules['12_template']['05_edit_languages']        = 'edit_languages.php?act=list';
// $modules['12_template']['06_template_backup']       = 'template.php?act=backup_setting';
// $modules['12_template']['mail_template_manage']     = 'mail_template.php?act=list';

// 数据库管理
// $modules['13_backup']['02_db_manage']               = 'database.php?act=backup';
// $modules['13_backup']['03_db_optimize']             = 'database.php?act=optimize';
// $modules['13_backup']['04_sql_query']               = 'sql.php?act=main';
// $modules['13_backup']['05_synchronous']             = 'integrate.php?act=sync';
// $modules['13_backup']['convert']                    = 'convert.php?act=main';

//短信管理
//$modules['14_sms']['02_sms_my_info']                = 'sms.php?act=display_my_info';
//修改$modules['14_sms']['03_sms_send']               = 'sms.php?act=display_send_ui';
//$modules['14_sms']['04_sms_charge']                 = 'sms.php?act=display_charge_ui';
//$modules['14_sms']['05_sms_send_history']           = 'sms.php?act=display_send_history_ui';
//$modules['14_sms']['06_sms_charge_history']         = 'sms.php?act=display_charge_history_ui';

//推荐管理
// $modules['15_rec']['affiliate']                     = 'affiliate.php?act=list';
// $modules['15_rec']['affiliate_ck']                  = 'affiliate_ck.php?act=list';

//邮件群发管理
//修改 $modules['16_email_manage']['email_list']           = 'email_list.php?act=list';
//修改 $modules['16_email_manage']['magazine_list']        = 'magazine_list.php?act=list';
//修改 $modules['16_email_manage']['attention_list']       = 'attention_list.php?act=list';
//修改 $modules['16_email_manage']['view_sendlist']        = 'view_sendlist.php?act=list';

//微信平台管理
/*
$modules['17_wxmp']['02_wx_menu']                  = 'wx_menu.php?act=list';              // 菜单管理
$modules['17_wxmp']['03_wx_keyword']               = 'wx_keyword.php?act=list';           // 关键字管理
$modules['17_wxmp']['04_wx_event']                 = 'wx_event.php?act=list';             // 事件管理  
$modules['17_wxmp']['05_wx_media']                 = 'wx_media.php?act=list';             // 媒体库管理
$modules['17_wxmp']['051_wx_news']                 = 'wx_news.php?act=list';              // 图文管理
$modules['17_wxmp']['06_wx_msg_send']              = 'wx_message.php?act=display_send_ui';
$modules['17_wxmp']['07_wx_msg_send_history']      = 'wx_message.php?act=display_send_history_ui';

*/

?>
