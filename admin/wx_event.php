<?php

/**
 * ECSHOP 贺卡管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: wx_event.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

$exc = new exchange($ecs->table("wx_event"), $db, 'event_id', 'event_name');

/*------------------------------------------------------ */
//-- 包装列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list'){

    assign_query_info();
    $smarty->assign('ur_here',     $_LANG['04_wx_event_list']);
    $smarty->assign('action_link', array('text' => $_LANG['04_wx_event_add'], 'href' => 'wx_event.php?act=add'));
    $smarty->assign('full_page',   1);

    $event_list = event_list();

    $smarty->assign('event_list',    $event_list['event_list']);
    $smarty->assign('filter',       $event_list['filter']);
    $smarty->assign('record_count', $event_list['record_count']);
    $smarty->assign('page_count',   $event_list['page_count']);

    $smarty->display('wx_event_list.htm');
}

/*------------------------------------------------------ */
//-- ajax列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $event_list = event_list();
    
    $smarty->assign('event_list',   $event_list['event_list']);
    $smarty->assign('filter',       $event_list['filter']);
    $smarty->assign('record_count', $event_list['record_count']);
    $smarty->assign('page_count',   $event_list['page_count']);

    $sort_flag  = sort_flag($event_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('wx_event_list.htm'), '', array('filter' => $event_list['filter'], 'page_count' => $event_list['page_count']));
}
/*------------------------------------------------------ */
//-- 删除事件定义
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    /* 检查权限 */
    check_authz_json('wx_manage');

    $event_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

    $name = $exc->get_name($event_id);

    if ($exc->drop($event_id))
    {
        // /* 删除图片 */
        // if (!empty($img))
        // {
        //      @unlink('../' . DATA_DIR . '/cardimg/'.$img);
        // }
        admin_log(addslashes($name),'remove','wx_event');

        $url = 'wx_event.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

        ecs_header("Location: $url\n");
        exit;
    }
    else
    {
        make_json_error($db->error());
    }
}
/*------------------------------------------------------ */
//-- 添加新事件
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    /*初始化显示*/
    $event['event_name']   = '';
    $event['event_key']    = '';

    $smarty->assign('event',        $event);
    $smarty->assign('ur_here',     $_LANG['04_wx_event_add']);
    $smarty->assign('action_link', array('text' => $_LANG['04_wx_event_list'], 'href' => 'wx_event.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('wx_event_info.htm');

}
elseif ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    /*检查包装名是否重复*/
    $is_only = $exc->is_only('event_name', $_POST['event_name']);

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['event_name_exist'], stripslashes($_POST['event_name'])), 1);
    }

    /*插入数据*/
    $sql = "INSERT INTO ".$ecs->table('wx_event')."(event_name, event_key, add_time, event_desc)
            VALUES ('$_POST[event_name]', '$_POST[event_key]', ". time() .", '$_POST[event_desc]')";
    $db->query($sql);

    admin_log($_POST['event_name'],'add','wx_event');

    /*添加链接*/
    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'wx_event.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'wx_event.php?act=list';

    sys_msg($_POST['event_name'].$_LANG['event_add_succeed'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑事件
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    $sql = "SELECT event_id, event_name, event_key, event_desc FROM " .$ecs->table('wx_event'). " WHERE event_id='$_REQUEST[id]'";
    $event = $db->GetRow($sql);

    $smarty->assign('ur_here',     $_LANG['event_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['04_wx_event_list'], 'href' => 'wx_event.php?act=list&' . list_link_postfix()));
    $smarty->assign('event',        $event);
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('wx_event_info.htm');
}
elseif ($_REQUEST['act'] == 'update')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    if ($_POST['event_name'] != $_POST['old_event_name'])
    {
        /*检查品牌名是否相同*/
        $is_only = $exc->is_only('event_name', $_POST['event_name'], $_POST['id']);

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['event_name_exist'], stripslashes($_POST['event_name'])), 1);
        }
    }
    $param = "event_name = '$_POST[event_name]', event_key = '$_POST[event_key]', event_desc = '$_POST[event_desc]'";


    if ($exc->edit($param,  $_POST['id']))
    {
        admin_log($_POST['event_name'], 'edit', 'wx_event');

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'wx_event.php?act=list&' . list_link_postfix();

        $note = sprintf($_LANG['event_edit_succeed'], $_POST['event_name']);
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}

/*------------------------------------------------------ */
//-- ajax编辑卡片名字
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_event_name')
{
    check_authz_json('card_manage');
    $card_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $card_name = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));

    if (!$exc->is_only('card_name', $card_name, $card_id))
    {
        make_json_error(sprintf($_LANG['cardname_exist'], $card_name));
    }
    $old_card_name = $exc->get_name($card_id);
    if ($exc->edit("card_name='$card_name'", $card_id))
    {
        admin_log(addslashes($old_card_name), 'edit', 'card');
        make_json_result(stripcslashes($card_name));
    }
    else
    {
        make_json_error($db->error());
    }
}
/*------------------------------------------------------ */
//-- ajax编辑卡片费用
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_event_key')
{
    check_authz_json('card_manage');
    $card_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $card_fee = empty($_REQUEST['val']) ? 0.00 : floatval($_REQUEST['val']);

    $card_name = $exc->get_name($card_id);
    if ($exc->edit("card_fee ='$card_fee'", $card_id))
    {
        admin_log(addslashes($card_name), 'edit', 'card');
        make_json_result($card_fee);
    }
    else
    {
        make_json_error($db->error());
    }
}


function event_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'event_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 分页大小 */
        $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('wx_event');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT event_id, event_name, event_key, status, event_desc, add_time".
               " FROM ".$GLOBALS['ecs']->table('wx_event').
               " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
               " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $event_list = $GLOBALS['db']->getAll($sql);

    $count = count($event_list);

    for ($i=0; $i<$count; $i++)
    {
        $event_list[$i]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $event_list[$i]['add_time']);
    }    

    $arr = array('event_list' => $event_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>