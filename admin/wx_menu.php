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
 * $Author: jeffyyang $
 * $Id: wx_menus.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("wx_menu"), $db, 'menu_id', 'name');

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
//-- 微信公共号菜单列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取菜单列表 */
    $wx_menu_list = wx_menu_list(0);

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['02_wx_menu_list']);
    $smarty->assign('action_link',  array('href' => 'wx_menu.php?act=add', 'text' => $_LANG['02_wx_menu_add']));
    $smarty->assign('full_page',    1);
    $smarty->assign('menu_list', $wx_menu_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('wx_menu_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $wx_menu_list = wx_menu_list(0);
    $smarty->assign('menu_list',     $wx_menu_list);

    make_json_result($smarty->fetch('wx_menu_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加微信菜单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限检查 */
    admin_priv('wx_manage');
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['02_wx_menu_add']);
    $smarty->assign('action_link',  array('href' => 'wx_menu.php?act=list', 'text' => $_LANG['02_wx_menu_list']));

    $smarty->assign('parent_select',   parent_wx_menu_list(0,-1));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('menu_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('wx_menu_info.htm');
}

/*------------------------------------------------------ */
//-- 菜单添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限检查 */
    admin_priv('wx_manage');

    /* 初始化变量 */
    $wx_menu['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $wx_menu['menu_name']    = !empty($_POST['menu_name'])    ? trim($_POST['menu_name'])    : '';
    $wx_menu['menu_type']    = !empty($_POST['menu_type'])    ? trim($_POST['menu_type'])    : '';
    $wx_menu['event_key']    = !empty($_POST['event_key'])    ? trim($_POST['event_key'])    : '';
    $wx_menu['web_url']      = !empty($_POST['web_url'])      ? trim($_POST['web_url'])      : '';
    $wx_menu['menu_sort']    = !empty($_POST['menu_sort'])    ? intval($_POST['menu_sort'])  : 0;
    $wx_menu['menu_desc']    = !empty($_POST['menu_desc'])    ? trim($_POST['menu_desc'])    : '';
    $wx_menu['is_leaf']      = !empty($_POST['is_leaf'])      ? intval($_POST['is_leaf'])    : 1;
    $wx_menu['create_time']  = time();
    if($wx_menu['parent_id'] == 0){
    /* 顶级菜单不能超过3个 */
        $menu_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wx_menu'). " WHERE parent_id=0 ");
        if($menu_count >= 3){
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['wx_top_menu_exceed'], 0, $link);
        }
    }

    if($wx_menu['parent_id'] > 0){
        /* 子菜单不能超过10个 */
        $parent_id = $wx_menu['parent_id'];
        $menu_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wx_menu'). " WHERE parent_id='$parent_id' ");
        if($menu_count >= 10){
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['wx_leaf_menu_exceed'], 0, $link);
        }
    }

    if (wx_menu_exists($wx_menu['menu_name'], $wx_menu['parent_id']))
    {
        /* 同级别下不能有重复的菜单名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['wx_menu_name_exist'], 0, $link);
    }

    if($wx_menu['menu_sort'] > 10)
    {
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['sort_order_error'], 0, $link);
    }
    // 修改父菜单的is_leaf属性
    if($wx_menu['parent_id'] > 0){
        wx_menu_update($wx_menu['parent_id'], array('is_leaf' => 0));
    } 
    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('wx_menu'), $wx_menu) !== false)
    {
        $wx_menu_id = $db->insert_id();
        admin_log($_POST['menu_name'], 'add', 'wx_menu');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = 'wx_menu.php?act=add';

        $link[1]['text'] = $_LANG['back_list'];
        $link[1]['href'] = 'wx_menu.php?act=list';

        sys_msg($_LANG['wx_menu_add_succed'], 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑微信菜单信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('wx_manage');   // 权限检查
    $wx_menu_id = intval($_REQUEST['wx_menu_id']);
    $wx_menu_info = get_wx_menu_info($wx_menu_id);  // 查询菜单数据

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['wx_menu_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['02_wx_menu_list'], 'href' => 'wx_menu.php?act=list'));

    $smarty->assign('wx_menu_info',    $wx_menu_info);
    $smarty->assign('form_act',    'update');
    $smarty->assign('parent_select',  parent_wx_menu_list(0, $wx_menu_info['parent_id']));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('wx_menu_info.htm');
}

elseif($_REQUEST['act'] == 'add_wx_menu')
{
    $parent_id  = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $menu_name  = empty($_REQUEST['menu_name']) ? '' : json_str_iconv(trim($_REQUEST['menu_name']));

    if(menu_exists($menu_name, $parent_id))
    {
        make_json_error($_LANG['wx_menu_name_exist']);
    }
    else
    {
        $sql = "INSERT INTO " . $ecs->table('wx_menu') . "(menu_name, parent_id, is_show)" .
               "VALUES ( '$menu_name', '$parent_id', 1)";

        $db->query($sql);
        $menu_id = $db->insert_id();
        $arr = array("parent_id"=>$parent_id, "id"=>$menu_id, "cat"=>$wx_menu);
        clear_cache_files();    // 清除缓存
        make_json_result($arr);
    }
}

/*------------------------------------------------------ */
//-- 编辑微信菜单信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{
    /* 权限检查 */
    admin_priv('wx_manage');

    /* 初始化变量 */
    $wx_menu_id              = !empty($_POST['menu_id'])      ? intval($_POST['menu_id']) : 0;
    $old_wx_menu_name        = $_POST['old_menu_name']; 
    $wx_menu['menu_name']    = !empty($_POST['menu_name'])    ? trim($_POST['menu_name'])    : '';
    $wx_menu['menu_type']    = !empty($_POST['menu_type'])    ? trim($_POST['menu_type'])    : '';
    $wx_menu['event_key']    = !empty($_POST['event_key'])    ? trim($_POST['event_key'])    : '';
    $wx_menu['web_url']      = !empty($_POST['web_url'])      ? trim($_POST['web_url'])      : '';
    $wx_menu['menu_sort']    = !empty($_POST['menu_sort'])    ? intval($_POST['menu_sort'])  : 0;
    $wx_menu['menu_desc']    = !empty($_POST['menu_desc'])    ? trim($_POST['menu_desc'])    : '';
    $wx_menu['is_leaf']      = !empty($_POST['is_leaf'])      ? intval($_POST['is_leaf'])    : 1;

    if($wx_menu['parent_id'] == 0){
    /* 顶级菜单不能超过3个 */
        $menu_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wx_menu'). " WHERE parent_id=0 ");
        if($menu_count > 3){
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['wx_top_menu_exceed'], 0, $link);
        }
    }

    if($wx_menu['parent_id'] > 0){
        /* 子菜单不能超过10个 */
        $parent_id = $wx_menu['parent_id'];
        $menu_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wx_menu'). " WHERE parent_id='$parent_id' ");
        if($menu_count >= 10){
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['wx_leaf_menu_exceed'], 0, $link);
        }
    }

    /* 判断菜单名是否重复 */
    if ($wx_menu['menu_name'] != $old_wx_menu_name)
    {
        if (wx_menu_exists($wx_menu['wx_menu_name'],$wx_menu['parent_id'], $wx_menu_id))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['wx_menu_name_exist'], 0, $link);
        }
    }

    if($wx_menu['menu_sort']  > 10 || $wx_menu['menu_sort']  < 0)
    {
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['sort_order_error'], 0, $link);
    }

    if ($db->autoExecute($ecs->table('wx_menu'), $wx_menu, 'UPDATE', "menu_id='$wx_menu_id'"))
    {
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['wx_menu_name'], 'edit', 'wx_menu'); // 记录管理员操作
        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'wx_menu.php?act=list');
        sys_msg($_LANG['wx_menu_edit_succed'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 编辑菜单排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('wx_manage');

    $id  = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (wx_menu_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}


/*------------------------------------------------------ */
//-- 删除微信菜单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('wx_manage');

    /* 初始化分类ID并取得菜单名称 */
    $menu_id   = intval($_GET['id']);
    $menu_name = $db->getOne('SELECT menu_name FROM ' .$ecs->table('wx_menu'). " WHERE menu_id='$menu_id'");

    /* 当前分类下是否有子菜单 */
    $menu_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('wx_menu'). " WHERE parent_id='$menu_id'");

    /* 如果不存在下级子分类和商品，则删除之 */
    if ($menu_count == 0)
    {
        /* 删除菜单 */
        $sql = 'DELETE FROM ' .$ecs->table('wx_menu'). " WHERE menu_id = '$menu_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($menu_name, 'remove', 'wx_menu');
        }
    }
    else
    {
        make_json_error($menu_name .' '. $_LANG['cat_isleaf']);
    }

    $url = 'wx_menu.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
//

/**
 * 检查分类是否已经存在
 *
 * @param   string      $wx_menu_name    分类名称
 * @param   integer     $parent_menu     上级分类
 * @param   integer     $exclude         排除的分类ID
 * @return  boolean
 */
function wx_menu_exists($wx_menu_name, $parent_menu, $exclude = 0)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('wx_menu').
           " WHERE parent_id = '$parent_menu' AND menu_name = '$wx_menu_name' AND menu_id<>'$exclude'";    
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获得微信菜单的所有信息
 *
 * @param   integer     $wx_menu_id     指定微信菜单ID
 *
 * @return  mix
 */
function get_wx_menu_info($wx_menu_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('wx_menu'). " WHERE menu_id='$wx_menu_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 更新微信菜单
 *
 * @param   integer $wx_menu_id
 * @param   array   $args
 *
 * @return  mix
 */
function wx_menu_update($wx_menu_id, $args)
{
    if (empty($args) || empty($wx_menu_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('wx_menu'), $args, 'update', "menu_id='$wx_menu_id'");
}


/**
 * 获得指定微信菜单的子菜单的数组
 *
 * @access  public
 * @param   int     $menu_id    微信菜单的ID
 * @return  mix
 */
function wx_menu_list($menu_id = 0)
{
    $sql = "SELECT m.menu_id, m.menu_name, m.parent_id, m.menu_type, m.event_key, m.web_url, m.menu_sort, m.menu_desc, COUNT(s.menu_id) AS has_children ".
        'FROM ' . $GLOBALS['ecs']->table('wx_menu') . " AS m ".
        "LEFT JOIN " . $GLOBALS['ecs']->table('wx_menu') . " AS s ON s.parent_id=m.menu_id ".
        "GROUP BY m.menu_id ".
        'ORDER BY m.parent_id, m.menu_sort ASC';

    $res = $GLOBALS['db']->getAll($sql);

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }
    return $res;
}

/**
 * 没有叶子菜单的父微信菜单
 * @access  private
 * @param   int     $parent_id   一级级菜单ID
 * @param   int     $selected    选中的父菜单
 * @return  mix
 */

function parent_wx_menu_list($parent_id = 0, $selected = -1){

    static $res = NULL;
    if ($res === NULL)
    {
        $data = read_static_cache('wx_menu_pid_releate');
        if ($data === false)
        {
            $sql = 'SELECT m.menu_id, m.menu_name, m.parent_id'.
                ' FROM ' . $GLOBALS['ecs']->table('wx_menu') . ' AS m '.
                ' WHERE m.parent_id='. $parent_id .
                ' ORDER BY m.parent_id ASC';

            $res = $GLOBALS['db']->getAll($sql);
            //如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000)
            {
                write_static_cache('wx_menu_pid_releate', $res);
            }
        }
        else
        {
            $res = $data;
        }
    }

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }

    $options = wx_menu_options($parent_id, $res); // 获得指定菜单数组

    // 返回下拉列表
     $select = '';

     foreach ($options AS $var)
     {
         $select .= '<option value="' . $var['menu_id'] . '" ';
         $select .= ($selected == $var['menu_id']) ? "selected='ture'" : '';
         $select .= '>';
         $select .= htmlspecialchars(addslashes($var['menu_name']), ENT_QUOTES) . '</option>';
     }
    return $select;
    
    // }
    // else
    // {
    //      foreach ($options AS $key => $value)
    //      {
    //          $options[$key]['url'] = build_uri('wx_menu', array('pid' => $value['menu_id']), $value['name']);
    //      }
    //     return $options;
    // }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 * @access  private
 * @param   int     $menu_id    上级菜单ID
 * @param   array   $arr        含有所有菜单的数组
 * @return  void
 */
function wx_menu_options($menu_id, $arr)
{
    static $menu_options = array();
    foreach ($arr AS $key => $value)
    {
        $wx_menu_id = $value['menu_id'];
        $menu_options[$wx_menu_id]['menu_id']    = $wx_menu_id;
        $menu_options[$wx_menu_id]['parent_id']  = $value['parent_id'];
        $menu_options[$wx_menu_id]['menu_name']  = $value['menu_name'];
        unset($arr[$key]);
    }
    return $menu_options;
}

?>