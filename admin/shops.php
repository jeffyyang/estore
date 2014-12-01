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
 * $Id: brand.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

$exc = new exchange($ecs->table("shop"), $db, 'shop_id', 'shop_name');

/*------------------------------------------------------ */
//-- 商户列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['shop_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['shop_add'], 'href' => 'shops.php?act=add'));
    $smarty->assign('full_page',    1);

    $shop_list = get_shoplist();

    $smarty->assign('shop_list',    $shop_list['shop']);
    $smarty->assign('filter',       $shop_list['filter']);
    $smarty->assign('record_count', $shop_list['record_count']);
    $smarty->assign('page_count',   $shop_list['page_count']);

    assign_query_info();
    $smarty->display('shops_list.htm');
}

/*------------------------------------------------------ */
//-- 添加商户
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('shop_manage');
    $shop = array();

    $cate_list = get_shop_cat_list();
    $smarty->assign('cat_list', $cate_list);

    /* 取得地区 */
    // $province_list = get_regions(1,1);
    // $smarty->assign('province_list', $province_list);

    // 吉林
    $city_list = get_regions_list(2,15);
    // 江苏
    // $city_list = get_regions_list(2,16);
    $smarty->assign('city_list', $city_list);    

    $is_add = true;
    $smarty->assign('is_add', $is_add);    
    $smarty->assign('ur_here',     $_LANG['shop_add']);
    $smarty->assign('action_link', array('text' => $_LANG['shop_list'], 'href' => 'shops.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->assign('shop', array('sort_order'=>50, 'is_show'=>1));
    $smarty->display('shops_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    /*检查商户名是否重复*/
    admin_priv('shop_manage');

    $is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;

    $is_only = $exc->is_only('shop_name', $_POST['shop_name']);

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['shopname_exist'], stripslashes($_POST['shop_name'])), 1);
    }

    /*对描述处理*/
    if (!empty($_POST['shop_desc']))
    {
        $_POST['shop_desc'] = $_POST['shop_desc'];
    }

     /*处理图片*/
    $logo_img_name = basename($image->upload_image($_FILES['shop_logo'],'shopimg'));

     /*处理图片*/
    $id_img_name = basename($image->upload_image($_FILES['id_img'],'shopimg'));

     /*处理图片*/
    $lic_img_name = basename($image->upload_image($_FILES['lic_img'],'shopimg'));


     /*处理URL*/
    $site_url = sanitize_url( $_POST['site_url'] );

    /*插入数据*/

    $sql = "INSERT INTO ".$ecs->table('shop')."(pcat_id, cat_id, shop_name, site_url, shop_desc, shop_logo, lic_snapshot, id_snapshot, is_show, sort_order) ".
           "VALUES ('$_POST[main_cate_id]','$_POST[sub_cate_id]','$_POST[shop_name]', '$site_url', '$_POST[shop_desc]', '$logo_img_name', '$id_img_name', '$lic_img_name', '$is_show', '$_POST[sort_order]')";
    $db->query($sql);
    $shop_id = $db->Insert_ID();

    admin_log($_POST['shop_name'],'add','shop');


    /* 添加门店资料 */
    $sql = "INSERT INTO ".$ecs->table('suppliers')." (pcat_id, cat_id, suppliers_name, shop_id, region_cities, region_districts, place_id, address, office_phone) ".
           "VALUES ('$_POST[main_cate_id]','$_POST[sub_cate_id]','$_POST[shop_name]','$shop_id', '$_POST[city]', '$_POST[district]', '$_POST[place]', '$_POST[suppliers_address]', '$_POST[office_phone]')";

    $db->query($sql);
    $suppliers_id = $db->Insert_ID();

    admin_log($_POST['shop_name'],'add','supplier');
    /* 转入权限分配列表 */

    /* 判断管理员是否已经存在 */
    if (!empty($_POST['user_name']))
    {
        $sql = "SELECT *
                FROM " . $ecs->table('admin_user') . "
                WHERE user_name = '" . stripslashes($_POST['user_name']) . "' ";

        if ($db->getOne($sql))
        {
             sys_msg(sprintf($_LANG['user_name_exist'], stripslashes($_POST['user_name'])), 1);
        }

    }

    /* Email地址是否有重复 */
    if (!empty($_POST['email']))
    {
        $sql = "SELECT *
                FROM " . $ecs->table('admin_user') . "
                WHERE email = '" . stripslashes($_POST['email']) . "' ";

        if ($db->getOne($sql))
        {
            sys_msg(sprintf($_LANG['email_exist'], stripslashes($_POST['email'])), 1);
        }
    }

    /* 获取添加日期及密码 */
    $add_time = gmtime();
    $role_id = '';
    $password  = md5($_POST['password']);
    $action_list = '';
    if (!empty($_POST['select_role']))
    {
        $sql = "SELECT action_list, nav_list FROM " . $ecs->table('role') . " WHERE role_id = '".$_POST['select_role']."'";
        $row = $db->getRow($sql);
        $action_list = $row['action_list'];
        $role_id = $_POST['select_role'];
    }


    // $sql = "SELECT nav_list FROM " . $ecs->table('admin_user') . " WHERE action_list = 'all'";
    $row = $db->getRow($sql);
  
    $sql = "INSERT INTO ".$ecs->table('admin_user')." (user_name, email, password, add_time, nav_list, action_list, shop_id, role_id) ".
           "VALUES ('".trim($_POST['user_name'])."', '".trim($_POST['email'])."', '$password', '$add_time', '$row[nav_list]', '$action_list', '$shop_id', '$role_id')";


    $db->query($sql);
    /* 转入权限分配列表 */
    $new_id = $db->Insert_ID();
    admin_log(trim($_POST['user_name']), 'add', 'admin');

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'shops.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'shops.php?act=list';

    sys_msg($_LANG['shopadd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑商户信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('shop_manage');
    $sql = "SELECT shop_id, shop_name, pcat_id, cat_id, site_url, shop_logo, id_snapshot, lic_snapshot, shop_desc, is_show, sort_order ".
            "FROM " .$ecs->table('shop'). " WHERE shop_id='$_REQUEST[id]'";
    $shop = $db->GetRow($sql);

    $cate_list = get_shop_cat_list();
    $smarty->assign('cat_list', $cate_list);    

    $smarty->assign('ur_here',     $_LANG['shop_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['shop_list'], 'href' => 'shops.php?act=list&' . list_link_postfix()));
    $smarty->assign('shop',       $shop);
    $smarty->assign('form_action', 'updata');

    assign_query_info();
    $smarty->display('shops_info.htm');
}
elseif ($_REQUEST['act'] == 'updata')
{
    admin_priv('shop_manage');
    if ($_POST['shop_name'] != $_POST['old_shopname'])
    {
        /*检查商户名是否相同*/
        $is_only = $exc->is_only('shop_name', $_POST['shop_name'], $_POST['id']);

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['shopname_exist'], stripslashes($_POST['shop_name'])), 1);
        }
    }

    /*对描述处理*/
    if (!empty($_POST['shop_desc']))
    {
        $_POST['shop_desc'] = $_POST['shop_desc'];
    }

    $is_show = isset($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) : 0;
     /*处理URL*/
    $site_url = sanitize_url( $_POST['site_url'] );

     /*处理图片*/
    $logo_img_name = basename($image->upload_image($_FILES['shop_logo'],'shopimg'));

     /*处理图片*/
    $id_img_name = basename($image->upload_image($_FILES['id_img'],'shopimg'));

     /*处理图片*/
    $lic_img_name = basename($image->upload_image($_FILES['lic_img'],'shopimg'));


    $param = "shop_name = '$_POST[shop_name]',  site_url='$site_url', shop_desc='$_POST[shop_desc]', is_show='$is_show', sort_order='$_POST[sort_order]' ";
    if (!empty($logo_img_name))
    {
        //有图片上传
        $param .= " ,shop_logo = '$logo_img_name' ";
    }

    if (!empty($id_img_name))
    {
        //有图片上传
        $param .= " ,id_snapshot = '$id_img_name' ";
    }

    if (!empty($lic_img_name))
    {
        //有图片上传
        $param .= " ,lic_snapshot = '$lic_img_name' ";
    }


    if ($exc->edit($param,  $_POST['id']))
    {
        /* 清除缓存 */
        clear_cache_files();

        admin_log($_POST['shop_name'], 'edit', 'shop');

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'shops.php?act=list&' . list_link_postfix();
        $note = vsprintf($_LANG['shopedit_succed'], $_POST['shop_name']);
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑商户名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_shop_name')
{
    check_authz_json('shop_manage');

    $id     = intval($_POST['id']);
    $name   = json_str_iconv(trim($_POST['val']));

    /* 检查名称是否重复 */
    if ($exc->num("shop_name",$name, $id) != 0)
    {
        make_json_error(sprintf($_LANG['shopname_exist'], $name));
    }
    else
    {
        if ($exc->edit("shop_name = '$name'", $id))
        {
            admin_log($name,'edit','shop');
            make_json_result(stripslashes($name));
        }
        else
        {
            make_json_result(sprintf($_LANG['shopedit_fail'], $name));
        }
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('shop_manage');

    $id     = intval($_POST['id']);
    $order  = intval($_POST['val']);
    $name   = $exc->get_name($id);

    if ($exc->edit("sort_order = '$order'", $id))
    {
        admin_log(addslashes($name),'edit','shop');

        make_json_result($order);
    }
    else
    {
        make_json_error(sprintf($_LANG['shopedit_fail'], $name));
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_show')
{
    check_authz_json('shop_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_show='$val'", $id);

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_check')
{
    check_authz_json('shop_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_check='$val'", $id);

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 删除商户信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('shop_manage');

    $id = intval($_GET['id']);

    $sql = "SELECT *
            FROM " . $ecs->table('shop') . "
            WHERE shop_id = '$id'";
    $shop = $db->getRow($sql, TRUE);


    if ($shop['shop_id'])
    {
        /* 判断商户是否有门店 */
        $sql = "SELECT COUNT(*)
                FROM " .$ecs->table('suppliers'). " WHERE shop_id = '$id'";

        $supplier_exists = $db->getOne($sql, TRUE);
        if ($supplier_exists > 0)
        {
            $url = 'shops.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
            ecs_header("Location: $url\n");
            exit;
        }

         /* 删除商户的图标 */
        if(!empty($shop['shop_logo'])){
              @unlink(ROOT_PATH . DATA_DIR . '/shopimg/' .$logo_name);
        }
        $exc->drop($id);
        admin_log($shop['shop_name'],'remove','shop');
    }
   
    /* 清除缓存 */
    clear_cache_files();
    $url = 'shops.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 删除商户Logo
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_logo')
{
    /* 权限判断 */
    admin_priv('shop_manage');
    $shop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得logo名称 */
    $sql = "SELECT shop_logo FROM " .$ecs->table('shop'). " WHERE shop_id = '$shop_id'";
    $logo_name = $db->getOne($sql);

    if (!empty($logo_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/shopimg/' .$logo_name);
        $sql = "UPDATE " .$ecs->table('shop'). " SET shop_logo = '' WHERE shop_id = '$shop_id'";
        $db->query($sql);
    }
    $link= array(array('text' => $_LANG['shop_edit_lnk'], 'href' => 'shops.php?act=edit&id=' . $shop_id), array('text' => $_LANG['shop_list_lnk'], 'href' => 'shops.php?act=list'));
    sys_msg($_LANG['drop_shop_logo_success'], 0, $link);
}

/*------------------------------------------------------ */
//-- 删除营业执照图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_licimg')
{
    /* 权限判断 */
    admin_priv('shop_manage');
    $brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得logo名称 */
    $sql = "SELECT lic_snapshot FROM " .$ecs->table('shop'). " WHERE shop_id = '$shop_id'";
    $img_name = $db->getOne($sql);

    if (!empty($img_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/shopimg/' .$logo_name);
        $sql = "UPDATE " .$ecs->table('shop'). " SET lic_snapshot = '' WHERE shop_id = '$shop_id'";
        $db->query($sql);
    }
    $link= array(array('text' => $_LANG['shop_edit_lnk'], 'href' => 'shops.php?act=edit&id=' . $shop_id), array('text' => $_LANG['shop_list_lnk'], 'href' => 'shops.php?act=list'));
    sys_msg($_LANG['drop_lic_img_success'], 0, $link);
}

/*------------------------------------------------------ */
//-- 删除法人身份证图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_idimg')
{
    /* 权限判断 */
    admin_priv('shop_manage');
    $shop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得logo名称 */
    $sql = "SELECT id_snapshot FROM " .$ecs->table('shop'). " WHERE shop_id = '$shop_id'";
    $img_name = $db->getOne($sql);

    if (!empty($img_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/shopimg/' .$img_name);
        $sql = "UPDATE " .$ecs->table('shop'). " SET id_snapshot = '' WHERE shop_id = '$shop_id'";
        $db->query($sql);
    }
    $link= array(array('text' => $_LANG['shop_edit_lnk'], 'href' => 'shops.php?act=edit&id=' . $shop_id), array('text' => $_LANG['shop_list_lnk'], 'href' => 'shops.php?act=list'));
    sys_msg($_LANG['drop_id_img_success'], 0, $link);
}
/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $shop_list = get_shoplist();
    $smarty->assign('shop_list',    $shop_list['shop']);
    $smarty->assign('filter',       $shop_list['filter']);
    $smarty->assign('record_count', $shop_list['record_count']);
    $smarty->assign('page_count',   $shop_list['page_count']);

    make_json_result($smarty->fetch('shops_list.htm'), '',
        array('filter' => $shop_list['filter'], 'page_count' => $shop_list['page_count']));
}

/**
 * 获取商户列表
 *
 * @access  public
 * @return  array
 */
function get_shoplist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();

        /* 记录总数以及页数 */
        if (isset($_POST['shop_name']))
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('shop') .' WHERE shop_name = \''.$_POST['shop_name'].'\'';
        }
        else
        {
            $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('shop');
        }

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 查询记录 */
        if (isset($_POST['shop_name']))
        {
            if(strtoupper(EC_CHARSET) == 'GBK')
            {
                $keyword = iconv("UTF-8", "gb2312", $_POST['shop_name']);
            }
            else
            {
                $keyword = $_POST['shop_name'];
            }
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('shop')." WHERE shop_name like '%{$keyword}%' ORDER BY sort_order ASC";
        }
        else
        {
            $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('shop')." ORDER BY sort_order ASC";
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
        $shop_logo = empty($rows['shop_logo']) ? '' :
            '<a href="../' . DATA_DIR . '/shopimg/'.$rows['shop_logo'].'" target="_brank"><img src="images/picflag.gif" width="16" height="16" border="0" alt='.$GLOBALS['_LANG']['shop_logo'].' /></a>';
        $site_url   = empty($rows['site_url']) ? 'N/A' : '<a href="'.$rows['site_url'].'" target="_brank">'.$rows['site_url'].'</a>';

        $rows['shop_logo'] = $shop_logo;
        $rows['site_url']   = $site_url;

        $arr[] = $rows;
    }

    return array('shop' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
