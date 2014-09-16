<?php

/**
 * ECSHOP 贺卡管理语言项
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: card.php 17217 2011-01-19 06:29:08Z liubo $
*/

$_LANG['event_name'] = '事件名称';
$_LANG['event_key']  = '事件键值[KEY]';
$_LANG['add_time']   = '创建时间';
$_LANG['event_desc'] = '事件描述';

$_LANG['event_edit'] = '编辑时间';

$_LANG['event_edit_lnk'] = '重新编辑该贺卡';
$_LANG['evnet_list_lnk'] = '返回列表页面';

/*帮助信息*/
$_LANG['notice_event_key'] = '微信事件KEY值，全部为大写英文字母或中间“_”组成，长度不超过10个字符';
$_LANG['notice_cardfreemoney'] = '当用户消费金额超过这个值时，将免费使用这个贺卡<br />设置为0表明必须支付贺卡费用';

$_LANG['warn_cardimg'] = '你已经上传过图片。再次上传时将覆盖原图片';

/*提示信息*/
$_LANG['event_name_exist'] ='事件名 %s 已经存在';
$_LANG['event_add_succeed'] ='已成功添加';
$_LANG['event_drop_fail'] ='删除失败';
$_LANG['event_drop_succeed'] ='删除成功';
$_LANG['event_edit_succeed'] ='事件 %s 修改成功';
$_LANG['event_edit_fail'] ='事件 %s 修改失败';
$_LANG['drop_confirm'] ='你确认要删除这条记录吗？';

$_LANG['no_event_name'] ='你输入的事件名称为空！';

$_LANG['back_list'] ='返回事件列表';
$_LANG['continue_add'] ='继续添加新事件';


/*JS 语言项*/
$_LANG['js_languages']['no_event_name'] = '没有输入事件名';
$_LANG['js_languages']['cardfee_un_num'] = '贺卡费用为空或不是数字';
$_LANG['js_languages']['cardmoney_un_num'] = '贺卡免费额度为空或不是数字';

?>