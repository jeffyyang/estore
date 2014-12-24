<?php

/**
 * ECSHOP 商品分类管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: category.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("ecs_wx_keyword"), $db, 'keyword_id', 'keywords');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 回复关键字列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取关键字列表 */
    $keyword_list = wx_keyword_list();

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['03_wx_keyword_list']);
    $smarty->assign('action_link',  array('href' => 'wx_keyword.php?act=add', 'text' => $_LANG['03_wx_keyword_add']));
    $smarty->assign('full_page',    1);
    
    $smarty->assign('keyword_list', $keyword_list['wx_keyword_list']);
    $smarty->assign('filter',       $keyword_list['filter']);
    $smarty->assign('record_count', $keyword_list['record_count']);
    $smarty->assign('page_count',   $keyword_list['page_count']);    

    /* 列表页面 */
    assign_query_info();
    $smarty->display('wx_keyword_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $keyword_list = wx_keyword_list();
    $smarty->assign('keyword_list', $keyword_list['wx_keyword_list']);

    make_json_result($smarty->fetch('wx_keyword_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加回复关键字
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限检查 */
    admin_priv('wx_manage');

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['03_wx_keyword_add']);
    $smarty->assign('action_link',  array('href' => 'wx_keyword.php?act=list', 'text' => $_LANG['03_wx_keyword_list']));

    $smarty->assign('form_act',     'insert');
    //$smarty->assign('keyword_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('wx_keyword_info.htm');
}

/*------------------------------------------------------ */
//-- 回复关键字添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限检查 */
    admin_priv('wx_manage');

    /* 初始化变量 */
    $wx_keyword['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $wx_keyword['msg_type']     = !empty($_POST['msg_type'])     ? $_POST['msg_type']           : '';
    $wx_keyword['article_id']   = !empty($_POST['article_id'])   ? intval($_POST['article_id']) : 0;
    $wx_keyword['content']      = !empty($_POST['content'])      ? trim($_POST['content'])      : '';
    $wx_keyword['status']       = !empty($_POST['status'])       ? intval($_POST['status'])     : 1;
    $wx_keyword['create_time']  =  time();

    echo $wx_keyword['create_time'];

    if (wx_keyword_exists($wx_keyword['keywords']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['keywords_exist'], 0, $link);
    }

    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('wx_keyword'), $wx_keyword) !== false)
    {
        $cat_id = $db->insert_id();
        admin_log($_POST['keywords'], 'add', 'wx_keyword');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = 'wx_keyword.php?act=add';

        $link[1]['text'] = $_LANG['back_list'];
        $link[1]['href'] = 'wx_keyword.php?act=list';

        sys_msg($_LANG['add_succed'], 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑关键字回复信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('wx_manage');   // 权限检查
    $keyword_id = intval($_REQUEST['keyword_id']);

    $sql = "SELECT keyword_id, keywords, msg_type, article_id, content, status, create_time FROM " .$ecs->table('wx_keyword'). " WHERE keyword_id='$keyword_id'";

    $keyword = $db->GetRow($sql);

    /* 模板赋值 */
    $smarty->assign('action_link', array('text' => $_LANG['03_wx_keyword_list'], 'href' => 'wx_keyword.php?act=list'));

    $smarty->assign('keyword_info', $keyword);
    $smarty->assign('form_act', 'update');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('wx_keyword_info.htm');
}

/*------------------------------------------------------ */
//-- 回复关键字编辑时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{
    /* 权限检查 */
    admin_priv('wx_manage');

    /* 初始化变量 */
    $keyword_id      = !empty($_POST['keyword_id']) ? intval($_POST['keyword_id'])     : 0;
    $old_keywords    = $_POST['old_keywords'];
    $wx_keyword['keywords']  = !empty($_POST['keywords']) ? trim($_POST['keywords'])     : '';
    $wx_keyword['content']   = !empty($_POST['content']) ? $_POST['content']           : '';
    $wx_keyword['create_time']  =  time();

    /* 判断关键字名是否重复 */
/*    if ($wx_keyword['keywords'] != $old_keyword_keywords)
    {
        if (wx_keyword_exists($wx_keyword['keywords']))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['keywords_exist'], 0, $link);
        }
    }*/

    $dat = $db->getRow("SELECT keywords FROM ". $ecs->table('wx_keyword') . " WHERE keyword_id = '$keyword_id'");

    if ($db->autoExecute($ecs->table('wx_keyword'), $wx_keyword, 'UPDATE', "keyword_id='$keyword_id'"))
    {
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['keywords'], 'edit', 'wx_keyword'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'wx_keyword.php?act=list');
        sys_msg($_LANG['wx_keyword_edit_succed'], 0, $link);
    }
}


/*------------------------------------------------------ */
//-- 删除回复关键字
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('wx_manage');

    /* 初始化分类ID并取得分类名称 */
    $keyword_id   = intval($_GET['id']);
    
    $keywords = $db->getOne('SELECT keywords FROM ' .$ecs->table('wx_keyword'). " WHERE keyword_id='$keyword_id'");

    /* 删除微信关键字 */
    $sql = 'DELETE FROM ' .$ecs->table('wx_keyword'). " WHERE keyword_id = '$keyword_id'";
    if ($db->query($sql))
    {
        clear_cache_files();
        admin_log($keywords, 'remove', 'wx_keyword');
    }

    $url = 'wx_keyword.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 检查关键字是否已经存在
 *
 * @param   string     $keywords        回复关键字
 * @return  boolean
 */
function wx_keyword_exists($keywords)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wx_keyword').
           " WHERE keywords = '$keywords' ";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获得指定微信关键字的所有信息
 *
 * @param   integer   $keyword_id    指定的关键字ID
 *
 * @return  mix
 */
function get_wx_keyword_info($keyword_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('wx_keyword'). " WHERE keyword_id='$keyword_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 更新指定微信关键字
 *
 * @param   integer $keyword_id
 * @param   array   $args
 *
 * @return  mix
 */
function wx_keyword_update($keyword_id, $args)
{
    if (empty($args) || empty($keyword_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wx_keyword'), $args, 'update', "keyword_id='$keyword_id'");
}

/**
 * 获得微信关键字的数组
 * @access  public
 * @return  mix
 */

function wx_keyword_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'keyword_id' : trim($_REQUEST['sort_by']);

        /* 分页大小 */
        $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('wx_keyword');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT c.keyword_id, c.keywords, c.msg_type, c.article_id, c.status, c.content, c.create_time".
               " FROM ".$GLOBALS['ecs']->table('wx_keyword'). " AS c " .
               " ORDER by " . $filter['sort_by'] .
               " LIMIT " . $filter['start'] . ',' . $filter['page_size'];

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $wx_keyword_list = $GLOBALS['db']->getAll($sql);

    $count = count($wx_keyword_list);
    for ($i=0; $i<$count; $i++)
    {
        $wx_keyword_list[$i]['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $wx_keyword_list[$i]['create_time']);
    }

    $arr = array('wx_keyword_list' => $wx_keyword_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>