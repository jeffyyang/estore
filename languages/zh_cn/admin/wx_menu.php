<?php

/**
 * ECSHOP 微信菜单管理语言文件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: JeffyYang $
 * $Id: wx_menu.php 17217 2011-01-19 06:29:08Z JeffyYang $
*/

/* 微信菜单字段信息 */
$_LANG['wx_menu_id'] = '编号';
$_LANG['wx_menu_name'] = '菜单名称';
$_LANG['wx_menu_type'] = '菜单类型';
$_LANG['wx_menu_type_event'] = '事件（KEY）';
$_LANG['wx_menu_type_url']   = '跳转（URL）';
$_LANG['wx_menu_view_url']   = '跳转URL地址';
$_LANG['wx_menu_click_key']  = '事件触发KEY值';
$_LANG['wx_menu_desc'] = '描述';
$_LANG['wx_menu_parent_id'] = '上级菜单';
$_LANG['sort_order'] = '排序';
$_LANG['delete_info'] = '删除选中';
$_LANG['wx_menu_edit'] = '编辑菜单';
$_LANG['menu_top'] = '顶级菜单';

$_LANG['notice_url'] = '该Url值表示点击菜单将要跳转到一个网页地址，通常http://为开头。';
$_LANG['notice_key'] = '该Key值表示点击菜单触发的事件值，应在事件定义管理里中预定义，否则无效。';

$_LANG['index_new'] = '最新';
$_LANG['index_best'] = '精品';
$_LANG['index_hot'] = '热门';

$_LANG['back_list'] = '返回菜单列表';
$_LANG['continue_add'] = '继续添加菜单';

/* 操作提示信息 */
$_LANG['wx_menu_name_empty'] = '菜单名称不能为空!';
$_LANG['wx_menu_name_exist'] = '已存在相同的菜单名称!';
$_LANG["parent_isleaf"]  = '所选菜单不能是末级菜单!';
$_LANG["wx_menu_isleaf"] = '不是末级菜单,您不能删除!';
$_LANG["wx_menu_noleaf"] = '底下还有其它子菜单,不能修改为末级菜单!';
$_LANG["is_leaf_error"]  = '所选择的上级菜单不能是当前菜单或者当前菜单的子菜单!';
$_LANG['sort_order_error'] = '顺序号不合法';

$_LANG['wx_menu_add_succed']  = '新微信菜单添加成功!';
$_LANG['wx_menu_edit_succed'] = '微信菜单编辑成功!';
$_LANG['wx_menu_drop_succed'] = '微信菜单删除成功!';

/*JS 语言项*/
$_LANG['js_languages']['wx_menu_name_empty'] = '菜单名称不能为空!';
$_LANG['js_languages']['is_leafmenu'] = '您选定的是一个叶子菜单。\r\n新菜单的上级菜单不能是一个末级菜单';
$_LANG['js_languages']['not_leafmenu'] = '您选定的菜单不是一个末级菜单。\r\n商品的分类转移只能在末级分类之间才可以操作。';

?>