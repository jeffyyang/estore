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
 * $Id: card.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

$exc = new exchange($ecs->table("wx_media"), $db, 'media_id', 'media_name');

/*------------------------------------------------------ */
//-- 媒体库列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list'){

    assign_query_info();
    $smarty->assign('ur_here',     $_LANG['05_wx_media_list']);
    $smarty->assign('action_link', array('text' => $_LANG['05_wx_media_add'], 'href' => 'wx_media.php?act=add'));
    $smarty->assign('full_page',   1);

    $media_list = media_list();

    // print_r($media_list);
    $smarty->assign('media_list',   $media_list['media_list']);
    $smarty->assign('filter',       $media_list['filter']);
    $smarty->assign('record_count', $media_list['record_count']);
    $smarty->assign('page_count',   $media_list['page_count']);

    $smarty->display('wx_media_list.htm');
}

/*------------------------------------------------------ */
//-- ajax列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $media_list = media_list();
    $smarty->assign('media_list',   $media_list['media_list']);
    $smarty->assign('filter',       $media_list['filter']);
    $smarty->assign('record_count', $media_list['record_count']);
    $smarty->assign('page_count',   $media_list['page_count']);

    $sort_flag  = sort_flag($media_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('wx_media_list.htm'), '', array('filter' => $media_list['filter'], 'page_count' => $media_list['page_count']));
}

/*------------------------------------------------------ */
//-- 删除媒体文件
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    /* 检查权限 */
    check_authz_json('wx_manage');

    $media_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);

    $name = $exc->get_name($media_id);

    $media_url = $exc->get_name($media_id, 'media_url');

    if ($exc->drop($card_id))
    {
        /* 删除图片 */
        if (!empty($media_url))
        {
             @unlink('../' . DATA_DIR . '/wx_media/'.$media_url);
        }
        admin_log(addslashes($name),'remove','wx_media');

        $url = 'wx_media.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

        ecs_header("Location: $url\n");
        exit;
    }
    else
    {
        make_json_error($db->error());
    }
}
/*------------------------------------------------------ */
//-- 添加新媒体库
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    /*初始化显示*/
    $medium['media_type']   = 'image';
    $medium['formats'] = 'JPG';

    $smarty->assign('medium',      $medium);
    $smarty->assign('ur_here',     $_LANG['media_add']);
    $smarty->assign('action_link', array('text' => $_LANG['05_wx_media_list'], 'href' => 'wx_media.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('wx_media_info.htm');

}
elseif ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('wx_manage');

    /*检查包装名是否重复*/
    $is_only = $exc->is_only('media_name', $_POST['media_name']);

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['media_name_exist'], stripslashes($_POST['media_name'])), 1);
    }

     /*处理图片*/
    $media_url = basename($image->upload_image($_FILES['media_file'],"wx_media"));

    /*插入数据*/
    $sql = "INSERT INTO ".$ecs->table('wx_media')."(media_name, media_type, formats, media_desc, media_url, add_time)
            VALUES ('$_POST[media_name]', '$_POST[media_type]', '$_POST[formats]', '$_POST[media_desc]', '$media_url', '" .time(). "')";
    $db->query($sql);

    admin_log($_POST['media_name'],'add','wx_media');

    /*添加链接*/
    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'wx_media.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'wx_media.php?act=list';

    sys_msg($_POST['media_name'].$_LANG['media_add_succeed'],0, $link);
}

/* 删除卡片图片 */
elseif ($_REQUEST['act'] == 'drop_media_file')
{
    /* 权限判断 */
    admin_priv('wx_manage');
    $media_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得logo名称 */
    $sql = "SELECT media_url FROM " .$ecs->table('wx_media'). " WHERE media_id = '$media_id'";
    $media_url = $db->getOne($sql);

    if (!empty($media_url))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/wx_media/' .$media_url);
        $sql = "UPDATE " .$ecs->table('wx_media'). " SET media_url = '' WHERE media_id = '$media_id'";
        $db->query($sql);
    }
    $link= array(array('text' => $_LANG['media_edit_lnk'], 'href'=>'wx_media.php?act=edit&id=' .$media_id), array('text' => $_LANG['media_list_lnk'], 'href'=>'wx_media.php?act=list'));
    sys_msg($_LANG['drop_media_file_success'], 0, $link);
}
/*------------------------------------------------------ */
//-- ajax编辑卡片名字
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_media_name')
{
    check_authz_json('wx_manage');
    $media_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $media_name = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));

    if (!$exc->is_only('media_name', $media_name, $media_id))
    {
        make_json_error(sprintf($_LANG['media_name_exist'], $media_name));
    }
    $old_card_name = $exc->get_name($media_id);
    if ($exc->edit("media_name='$media_name'", $media_id))
    {
        admin_log(addslashes($old_media_name), 'edit', 'wx_media');
        make_json_result(stripcslashes($media_name));
    }
    else
    {
        make_json_error($db->error());
    }
}

function media_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'media_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 分页大小 */
        $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('wx_media');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT media_id, media_name, media_type, formats, media_url, media_desc".
               " FROM ".$GLOBALS['ecs']->table('wx_media').
               " ORDER by " . $filter['sort_by'] . ' ' . $filter['sort_order'] .
               " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $media_list = $GLOBALS['db']->getAll($sql);

    $arr = array('media_list' => $media_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>