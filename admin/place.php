<?php

/**
 * ECSHOP 管理中心品牌管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: place.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
require(ROOT_PATH . 'includes/cls_json.php');
$image = new cls_image($_CFG['bgcolor']);

$exc = new exchange($ecs->table("place"), $db, 'place_id', 'place_name');

/*------------------------------------------------------ */
//-- 商圈列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['19_place_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['20_place_add'], 'href' => 'place.php?act=add'));
    $smarty->assign('full_page',    1);

    $place_list = get_place_list();

    $smarty->assign('place_list',   $place_list['places']);
    $smarty->assign('filter',       $brand_list['filter']);
    $smarty->assign('record_count', $brand_list['record_count']);
    $smarty->assign('page_count',   $brand_list['page_count']);

    assign_query_info();
    $smarty->display('place_list.htm');
}

/*------------------------------------------------------ */
//-- 添加商圈
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('place_manage');

    $smarty->assign('ur_here',     $_LANG['20_place_add']);
    $smarty->assign('action_link', array('text' => $_LANG['19_place_list'], 'href' => 'place.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->assign('place', array('sort_order'=>50, 'is_show'=>1));
    $smarty->display('place_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    /*检查品牌名是否重复*/
    admin_priv('place_manage');

    $is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

    $is_only = $exc->is_only('brand_name', $_POST['brand_name']);

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['brandname_exist'], stripslashes($_POST['brand_name'])), 1);
    }

    /*对描述处理*/
    if (!empty($_POST['brand_desc']))
    {
        $_POST['brand_desc'] = $_POST['brand_desc'];
    }

     /*处理图片*/
    $img_name = basename($image->upload_image($_FILES['brand_logo'],'brandlogo'));

     /*处理URL*/
    $site_url = sanitize_url( $_POST['site_url'] );

    /*插入数据*/

    $sql = "INSERT INTO ".$ecs->table('brand')."(brand_name, site_url, brand_desc, brand_logo, is_show, sort_order) ".
           "VALUES ('$_POST[brand_name]', '$site_url', '$_POST[brand_desc]', '$img_name', '$is_show', '$_POST[sort_order]')";
    $db->query($sql);

    admin_log($_POST['brand_name'],'add','place');

    /* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'place.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'place.php?act=list';

    sys_msg($_LANG['placeadd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑商圈名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_place_name')
{
    check_authz_json('place_manage');

    $id     = intval($_POST['id']);
    $name   = json_str_iconv(trim($_POST['val']));

    /* 检查名称是否重复 */
    if ($exc->num("brand_name",$name, $id) != 0)
    {
        make_json_error(sprintf($_LANG['placename_exist'], $name));
    }
    else
    {
        if ($exc->edit("place_name = '$name'", $id))
        {
            admin_log($name,'edit','place');
            make_json_result(stripslashes($name));
        }
        else
        {
            make_json_result(sprintf($_LANG['brandedit_fail'], $name));
        }
    }
}

elseif($_REQUEST['act'] == 'add_place')
{
    $place = empty($_REQUEST['place']) ? '' : json_str_iconv(trim($_REQUEST['place']));
    $district = empty($_REQUEST['district']) ? '' : json_str_iconv(trim($_REQUEST['district']));

    if(place_exists($district,$place))
    {
        make_json_error($_LANG['placename_exist']);
    }
    else
    {
        $sql = "INSERT INTO " . $ecs->table('place') . "(region_id, place_name)" .
               "VALUES ('$district', '$place')";
   
        $db->query($sql);
        $place_id = $db->insert_id();

        $arr = array("id"=>$place_id, "place"=>$place);

        make_json_result($arr);
    }
}
/*------------------------------------------------------ */
//-- 获取县区商圈的下拉列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'get_place_options')
{
    // check_authz_json('place_manage');

    $parent = !empty($_REQUEST['parent']) ? intval($_REQUEST['parent']) : 0;
    $arr['places'] = get_places($parent);
    $arr['target']  = !empty($_REQUEST['target']) ? stripslashes(trim($_REQUEST['target'])) : '';
    $arr['target']  = htmlspecialchars($arr['target']);

    $json = new JSON;
    echo $json->encode($arr);
}
/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('place_manage');

    $id     = intval($_POST['id']);
    $order  = intval($_POST['val']);
    $name   = $exc->get_name($id);

    if ($exc->edit("sort_order = '$order'", $id))
    {
        admin_log(addslashes($name),'edit','brand');

        make_json_result($order);
    }
    else
    {
        make_json_error(sprintf($_LANG['brandedit_fail'], $name));
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_show')
{
    check_authz_json('place_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_show='$val'", $id);

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 删除品牌
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('place_manage');

    $id = intval($_GET['id']);

    /* 删除该品牌的图标 */
/*    $sql = "SELECT brand_logo FROM " .$ecs->table('brand'). " WHERE brand_id = '$id'";
    $logo_name = $db->getOne($sql);
    if (!empty($logo_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/brandlogo/' .$logo_name);
    }

    $exc->drop($id);*/

    /* 更新商品的品牌编号 */
    $sql = "UPDATE " .$ecs->table('suppliers'). " SET place_id=0 WHERE place_id='$id'";
    $db->query($sql);

    $url = 'place.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $place_list = get_place_list();
    $smarty->assign('place_list',   $place_list['places']);
    $smarty->assign('filter',       $brand_list['filter']);
    $smarty->assign('record_count', $brand_list['record_count']);
    $smarty->assign('page_count',   $brand_list['page_count']);

    make_json_result($smarty->fetch('place_list.htm'), '',
        array('filter' => $brand_list['filter'], 'page_count' => $brand_list['page_count']));
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
/**
 * 检查商圈名是否已经存在
 *
 * @param   string     $place    商圈名
 * @return  boolean
 */
function place_exists($district, $place)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('place').
           " WHERE  region_id = '$district' AND place_name = '$place' ";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获得指定区县的所有商圈下拉表
 *
 * @access      public
 * @param       int     region    区县编号
 * @return      array
 */
function get_places($parent = 0)
{
    $sql = 'SELECT place_id, place_name FROM ' . $GLOBALS['ecs']->table('place') .
            " WHERE region_id = '$parent'";
    return $GLOBALS['db']->GetAll($sql);
}


/**
 * 获取商圈列表
 *
 * @access  public
 * @return  array
 */
function get_place_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();

        /* 记录总数以及页数 */
        if (isset($_POST['place_name']))
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('place') .' WHERE place_name = \''.$_POST['place_name'].'\'';
        }
        else
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('place');
        }

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询记录 */
        if (isset($_POST['place_name']))
        {
            if(strtoupper(EC_CHARSET) == 'GBK')
            {
                $keyword = iconv("UTF-8", "gb2312", $_POST['place_name']);
            }
            else
            {
                $keyword = $_POST['place_name'];
            }
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('place')." WHERE place_name like '%{$keyword}%' ORDER BY place_id ASC";
        }
        else
        {
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('place')." ORDER BY place_id ASC";
        }

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        // $brand_logo = empty($rows['brand_logo']) ? '' :
        //     '<a href="../' . DATA_DIR . '/brandlogo/'.$rows['brand_logo'].'" target="_brank"><img src="images/picflag.gif" width="16" height="16" border="0" alt='.$GLOBALS['_LANG']['brand_logo'].' /></a>';
        // $site_url   = empty($rows['site_url']) ? 'N/A' : '<a href="'.$rows['site_url'].'" target="_brank">'.$rows['site_url'].'</a>';

        // $rows['brand_logo'] = $brand_logo;
        // $rows['site_url']   = $site_url;
        $arr[] = $rows;
    }

    return array('places' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


?>
