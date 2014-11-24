<?php

/**
 * ECSHOP 管理中心供货商管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: wanglei $
 * $Id: suppliers.php 15013 2009-05-13 09:31:42Z wanglei $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');
/*------------------------------------------------------ */
//-- 门店列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
     /* 检查权限 */
    admin_priv('suppliers_manage');

    print_r($_SESSION);
    
    /* 查询 */
    $is_dis_searchbar = true;
    $shop_id = intval($_REQUEST['shop_id']);
    if($shop_id > 0){

        $is_dis_searchbar = false;

    }
    if($is_dis_searchbar){
        /*商户列表 */
        $shop_list = get_shop_list();

        $shop_exists = 1;
        if (empty($shop_list))
        {
            $shop_exists = 0;
        }
        
        $smarty->assign('shop_exists', $shop_exists);
        $smarty->assign('shop_list', $shop_list);
        unset($shop_list, $shop_exists);

        $cate_list = get_shop_cat_list();
        $smarty->assign('cat_list', $cate_list);
    }

    $smarty->assign('is_dis_searchbar', $is_dis_searchbar);
    $result = suppliers_list();
    /* 模板赋值 */
    $smarty->assign('ur_here', $_LANG['suppliers_list']); // 当前导航

    $smarty->assign('action_link', array('href' => 'suppliers.php?act=add', 'text' => $_LANG['add_suppliers']));

    $smarty->assign('full_page',        1); // 翻页参数

    $smarty->assign('suppliers_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('suppliers_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('suppliers_manage');

    $result = suppliers_list();

    $smarty->assign('suppliers_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('suppliers_list.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 列表页编辑名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_suppliers_name')
{
    check_authz_json('suppliers_manage');

    $id     = intval($_POST['id']);
    $name   = json_str_iconv(trim($_POST['val']));

    /* 判断名称是否重复 */
    $sql = "SELECT suppliers_id
            FROM " . $ecs->table('suppliers') . "
            WHERE suppliers_name = '$name'
            AND suppliers_id <> '$id' ";
    if ($db->getOne($sql))
    {
        make_json_error(sprintf($_LANG['suppliers_name_exist'], $name));
    }
    else
    {
        /* 保存供货商信息 */
        $sql = "UPDATE " . $ecs->table('suppliers') . "
                SET suppliers_name = '$name'
                WHERE suppliers_id = '$id'";
        if ($result = $db->query($sql))
        {
            /* 记日志 */
            admin_log($name, 'edit', 'supplier');

            clear_cache_files();

            make_json_result(stripslashes($name));
        }
        else
        {
            make_json_result(sprintf($_LANG['agency_edit_fail'], $name));
        }
    }
}

/*------------------------------------------------------ */
//-- 删除供货商
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('suppliers_manage');

    $id = intval($_REQUEST['id']);
    $sql = "SELECT *
            FROM " . $ecs->table('suppliers') . "
            WHERE suppliers_id = '$id'";
    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id'])
    {
        /* 判断供货商是否存在订单 */
        $sql = "SELECT COUNT(*)
                FROM " . $ecs->table('order_info') . "AS O, " . $ecs->table('order_goods') . " AS OG, " . $ecs->table('goods') . " AS G
                WHERE O.order_id = OG.order_id
                AND OG.goods_id = G.goods_id
                AND G.suppliers_id = '$id'";
        $order_exists = $db->getOne($sql, TRUE);
        if ($order_exists > 0)
        {
            $url = 'suppliers.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
            ecs_header("Location: $url\n");
            exit;
        }

        /* 判断供货商是否存在商品 */
        $sql = "SELECT COUNT(*)
                FROM " . $ecs->table('goods') . "AS G
                WHERE G.suppliers_id = '$id'";
        $goods_exists = $db->getOne($sql, TRUE);
        if ($goods_exists > 0)
        {
            $url = 'suppliers.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
            ecs_header("Location: $url\n");
            exit;
        }

        $sql = "DELETE FROM " . $ecs->table('suppliers') . "
            WHERE suppliers_id = '$id'";
        $db->query($sql);

        /* 删除管理员、发货单关联、退货单关联和订单关联的供货商 */
        $table_array = array('admin_user', 'delivery_order', 'back_order');
        foreach ($table_array as $value)
        {
            $sql = "DELETE FROM " . $ecs->table($value) . " WHERE suppliers_id = '$id'";
            $db->query($sql, 'SILENT');
        }

        /* 记日志 */
        admin_log($suppliers['suppliers_name'], 'remove', 'supplier');

        /* 清除缓存 */
        clear_cache_files();
    }

    $url = 'suppliers.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
    ecs_header("Location: $url\n");

    exit;
}

/*------------------------------------------------------ */
//-- 修改门店状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'is_check')
{
    check_authz_json('suppliers_manage');

    $id = intval($_REQUEST['id']);
    $sql = "SELECT suppliers_id, is_check
            FROM " . $ecs->table('suppliers') . "
            WHERE suppliers_id = '$id'";
    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id'])
    {
        $_suppliers['is_check'] = empty($suppliers['is_check']) ? 1 : 0;
        $db->autoExecute($ecs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");
        clear_cache_files();
        make_json_result($_suppliers['is_check']);
    }

    exit;
}

/*------------------------------------------------------ */
//-- 修改门店精品推荐
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'is_best')
{
    check_authz_json('suppliers_manage');

    $id = intval($_REQUEST['id']);
    $sql = "SELECT suppliers_id, is_best
            FROM " . $ecs->table('suppliers') . "
            WHERE suppliers_id = '$id'";
    $suppliers = $db->getRow($sql, TRUE);

    if ($suppliers['suppliers_id'])
    {
        $_suppliers['is_best'] = empty($suppliers['is_best']) ? 1 : 0;
        $db->autoExecute($ecs->table('suppliers'), $_suppliers, '', "suppliers_id = '$id'");
        clear_cache_files();
        make_json_result($_suppliers['is_best']);
    }

    exit;
}
/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch')
{
    /* 取得要操作的记录编号 */
    if (empty($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_record_selected']);
    }
    else
    {
        /* 检查权限 */
        admin_priv('shop_manage');

        $ids = $_POST['checkboxes'];

        if (isset($_POST['remove']))
        {
            $sql = "SELECT *
                    FROM " . $ecs->table('suppliers') . "
                    WHERE suppliers_id " . db_create_in($ids);
            $suppliers = $db->getAll($sql);

            foreach ($suppliers as $key => $value)
            {
                /* 判断供货商是否存在订单 */
                $sql = "SELECT COUNT(*)
                        FROM " . $ecs->table('order_info') . "AS O, " . $ecs->table('order_goods') . " AS OG, " . $ecs->table('goods') . " AS G
                        WHERE O.order_id = OG.order_id
                        AND OG.goods_id = G.goods_id
                        AND G.suppliers_id = '" . $value['suppliers_id'] . "'";
                $order_exists = $db->getOne($sql, TRUE);
                if ($order_exists > 0)
                {
                    unset($suppliers[$key]);
                }

                /* 判断供货商是否存在商品 */
                $sql = "SELECT COUNT(*)
                        FROM " . $ecs->table('goods') . "AS G
                        WHERE G.suppliers_id = '" . $value['suppliers_id'] . "'";
                $goods_exists = $db->getOne($sql, TRUE);
                if ($goods_exists > 0)
                {
                    unset($suppliers[$key]);
                }
            }
            if (empty($suppliers))
            {
                sys_msg($_LANG['batch_drop_no']);
            }


            $sql = "DELETE FROM " . $ecs->table('suppliers') . "
                WHERE suppliers_id " . db_create_in($ids);
            $db->query($sql);

            /* 更新管理员、发货单关联、退货单关联和订单关联的供货商 */
            $table_array = array('admin_user', 'delivery_order', 'back_order');
            foreach ($table_array as $value)
            {
                $sql = "DELETE FROM " . $ecs->table($value) . " WHERE suppliers_id " . db_create_in($ids) . " ";
                $db->query($sql, 'SILENT');
            }

            /* 记日志 */
            $suppliers_names = '';
            foreach ($suppliers as $value)
            {
                $suppliers_names .= $value['suppliers_name'] . '|';
            }
            admin_log($suppliers_names, 'remove', 'supplier');

            /* 清除缓存 */
            clear_cache_files();

            sys_msg($_LANG['batch_drop_ok']);
        }
    }
}

/*------------------------------------------------------ */
//-- 添加、编辑供货商
/*------------------------------------------------------ */
elseif (in_array($_REQUEST['act'], array('add', 'edit')))
{
    /* 检查权限 */
    admin_priv('suppliers_manage');

    // print_r($_SESSION);

    if ($_REQUEST['act'] == 'add')
    {
        $suppliers = array();

        /* 代理机构名称 */
        $agencies_list_name = agencies_list_name();
        $agencies_exists = 1;
        if (empty($agencies_list_name))
        {
            $agencies_exists = 0;
        }

        /*商户列表 */
        $shop_list = get_shop_list();
        $shop_exists = 1;
        if (empty($shop_list))
        {
            $shop_exists = 0;
        }

        if($_SESSION['shop_id'] > 0 ){

            $smarty->assign('is_add', false);
        }else{
            $smarty->assign('is_add', true);
        }
        $smarty->assign('shop_exists', $shop_exists);
        $smarty->assign('shop_list', $shop_list);
        unset($shop_list, $shop_exists);

        $cate_list = get_shop_cat_list();
        $smarty->assign('cat_list', $cate_list);

        /* 取得地区 */
        // $province_list = get_regions(1,1);
        // $smarty->assign('province_list', $province_list);

        // 吉林
        // $city_list = get_regions_list(2,15);
        // 江苏
        $city_list = get_regions_list(2,16);
        $smarty->assign('city_list', $city_list);

        /* 取得所有管理员，*/
        /* 标注哪些是该供货商的('this')，哪些是空闲的('free')，哪些是别的供货商的('other') */
        /* 排除是办事处的管理员 */
        $sql = "SELECT user_id, user_name, CASE
                WHEN suppliers_id = '$id' THEN 'this'
                WHEN suppliers_id = 0 THEN 'free'
                ELSE 'other' END AS type
                FROM " . $ecs->table('admin_user') . "
                WHERE agency_id = 0
                AND action_list <> 'all'";

        if($_SESSION['shop_id'] != 0){

        $sql = "SELECT user_id, user_name, CASE
                WHEN suppliers_id = '$id' THEN 'this'
                WHEN suppliers_id = 0 THEN 'free'
                ELSE 'other' END AS type
                FROM " . $ecs->table('admin_user') . "
                WHERE agency_id = 0 AND shop_id = ". $_SESSION['shop_id'] ."
                AND action_list <> 'all'";

        }                
        $suppliers['admin_list'] = $db->getAll($sql);

        $smarty->assign('ur_here', $_LANG['add_suppliers']);
        $smarty->assign('action_link', array('href' => 'suppliers.php?act=list', 'text' => $_LANG['suppliers_list']));
        $smarty->assign('gd', gd_version());
        $smarty->assign('form_action', 'insert');
        $smarty->assign('suppliers', $suppliers);
        assign_query_info();
        $smarty->display('suppliers_info.htm');

    }
    elseif ($_REQUEST['act'] == 'edit')
    {
        $suppliers = array();

        /* 取得供货商信息 */
        $id = $_REQUEST['id'];
        $sql = "SELECT * FROM " . $ecs->table('suppliers') . " WHERE suppliers_id = '$id'";
        $suppliers = $db->getRow($sql);
        if (count($suppliers) <= 0)
        {
            sys_msg('suppliers does not exist');
        }

        /* 代理机构名称 */
        $agencies_list_name = agencies_list_name();
        $agencies_exists = 1;
        if (empty($agencies_list_name))
        {
            $agencies_exists = 0;
        }

        $cate_list = get_shop_cat_list();
        $smarty->assign('cat_list', $cate_list);

        /* 取得地区 */
        // $province_list = get_regions(1,1);
        // $smarty->assign('province_list', $province_list);

        // 吉林
        // $city_list = get_regions_list(2,15);
        // 江苏
        $city_list = get_regions_list(2,16);
        $smarty->assign('city_list', $city_list);

        /* 图片列表 */
        $sql = "SELECT * FROM " . $ecs->table('supplier_gallery') . " WHERE supplier_id = '$id'";
        $img_list = $db->getAll($sql);

        /* 格式化相册图片路径 */
        if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0))
        {
            foreach ($img_list as $key => $gallery_img)
            {
                $gallery_img[$key]['img_url'] = get_image_path($gallery_img['supplier_id'], $gallery_img['img_original'], false, 'gallery');
                $gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['supplier_id'], $gallery_img['img_original'], true, 'gallery');
            }
        }
        else
        {
            foreach ($img_list as $key => $gallery_img)
            {
                $gallery_img[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
            }
        }

        /* 取得所有管理员，*/
        /* 标注哪些是该供货商的('this')，哪些是空闲的('free')，哪些是别的供货商的('other') */
        /* 排除是办事处的管理员 */
        $sql = "SELECT user_id, user_name, CASE
                WHEN suppliers_id = '$id' THEN 'this'
                WHEN suppliers_id = 0 THEN 'free'
                ELSE 'other' END AS type
                FROM " . $ecs->table('admin_user') . "
                WHERE agency_id = 0
                AND action_list <> 'all'";

        if($_SESSION['shop_id'] != 0){

        $sql = "SELECT user_id, user_name, CASE
                WHEN suppliers_id = '$id' THEN 'this'
                WHEN suppliers_id = 0 THEN 'free'
                ELSE 'other' END AS type
                FROM " . $ecs->table('admin_user') . "
                WHERE agency_id = 0 AND shop_id = ". $_SESSION['shop_id'] ."
                AND action_list <> 'all'";

        }                
        $suppliers['admin_list'] = $db->getAll($sql);


        $smarty->assign('ur_here', $_LANG['edit_suppliers']);
        $smarty->assign('action_link', array('href' => 'suppliers.php?act=list', 'text' => $_LANG['suppliers_list']));

        $smarty->assign('form_action', 'update');
        $smarty->assign('suppliers', $suppliers);
        $smarty->assign('img_list', $img_list);
        assign_query_info();

        $smarty->display('suppliers_info.htm');
    }

}

/*------------------------------------------------------ */
//-- 提交添加、编辑供货商
/*------------------------------------------------------ */
elseif (in_array($_REQUEST['act'], array('insert', 'update')))
{
    /* 检查权限 */
    admin_priv('suppliers_manage');

    if ($_REQUEST['act'] == 'insert')
    {

        /* 提交值 */
        $suppliers['suppliers_name']    = !empty($_POST['suppliers_name'])      ? trim($_POST['suppliers_name'])    : '';
        $suppliers['pcat_id']           = !empty($_POST['main_cate_id'])        ? intval($_POST['main_cate_id'])    : 0;
        $suppliers['cat_id']            = !empty($_POST['sub_cate_id'])         ? intval($_POST['sub_cate_id'])     : 0;
        $suppliers['agency_id']         = !empty($_POST['agency_id'])           ? intval($_POST['agency_id'])       : 0;
        $suppliers['shop_price']        = !empty($_POST['shop_price'])          ? intval($_POST['shop_price'])      : 30;
        $suppliers['office_phone']      = !empty($_POST['office_phone'])        ? trim($_POST['office_phone'])      : '';
        $suppliers['mobile_phone']      = !empty($_POST['mobile_phone'])        ? trim($_POST['mobile_phone'])      : '';
        $suppliers['opening_hours']      = !empty($_POST['opening_hours'])       ? trim($_POST['opening_hours'])    : '';
        $suppliers['comment_rank']      = !empty($_POST['comment_rank'])        ? intval($_POST['comment_rank'])    : 3;
        $suppliers['region_cities']     = !empty($_POST['city'])                ? intval($_POST['city'])            : 0;
        $suppliers['region_districts']  = !empty($_POST['district'])            ? intval($_POST['district'])        : 0;     
        $suppliers['place_id']          = !empty($_POST['place'])               ? intval($_POST['place'])           : 0;         
        $suppliers['map_lat']           = !empty($_POST['map_lat'])             ? doubleval($_POST['map_lat'])      : 0;
        $suppliers['map_lng']           = !empty($_POST['map_lng'])             ? doubleval($_POST['map_lng'])      : 0;
        $suppliers['address']           = !empty($_POST['suppliers_address'])   ? trim($_POST['suppliers_address']) : '';
        $suppliers['suppliers_desc']    = !empty($_POST['suppliers_desc'])      ? trim($_POST['suppliers_desc'])    : '';

        /* 判断名称是否重复 */
        $sql = "SELECT suppliers_id
                FROM " . $ecs->table('suppliers') . "
                WHERE suppliers_name = '" . $suppliers['suppliers_name'] . "' ";
        if ($db->getOne($sql))
        {
            sys_msg($_LANG['suppliers_name_exist']);
        }

        /*处理图片*/
        $logo_img_name = basename($image->upload_image($_FILES['logo_img'],'supplierimg'));
        $suppliers['logo_img'] = $logo_img_name;


        foreach ($_FILES['img_url']['name'] AS $key => $name)
        {
            if($_FILES['img_url']['name'][$key] !== '') { 
                $suppliers['is_has_gallery'] = 1;
                break;
            }
        }

        $db->autoExecute($ecs->table('suppliers'), $suppliers, 'INSERT');
        $suppliers['suppliers_id'] = $db->insert_id();

        if (isset($_POST['admins']))
        {
            $sql = "UPDATE " . $ecs->table('admin_user') . " SET suppliers_id = '" . $suppliers['suppliers_id'] . "', action_list = '" . SUPPLIERS_ACTION_LIST . "' WHERE user_id " . db_create_in($_POST['admins']);
            $db->query($sql);
        }

        /* 处理相册图片 */
        handle_gallery_image($suppliers['suppliers_id'], $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file']);

        /* 记日志 */
        admin_log($suppliers['suppliers_name'], 'add', 'supplier');

        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $links = array(array('href' => 'suppliers.php?act=add',  'text' => $_LANG['continue_add_suppliers']),
                       array('href' => 'suppliers.php?act=list', 'text' => $_LANG['back_suppliers_list'])
                       );
        sys_msg($_LANG['add_suppliers_ok'], 0, $links);

    }

    if ($_REQUEST['act'] == 'update')
    {

        /* 提交值 */
        $suppliers = array('id'   => trim($_POST['id']));

        $suppliers['new']['suppliers_name']    = !empty($_POST['suppliers_name'])      ? trim($_POST['suppliers_name'])    : '';
        // $suppliers['new']['pcat_id']           = !empty($_POST['main_cate_id'])        ? intval($_POST['main_cate_id'])    : 0;
        // $suppliers['new']['cat_id']            = !empty($_POST['sub_cate_id'])         ? intval($_POST['sub_cate_id'])     : 0;
        $suppliers['new']['agency_id']         = !empty($_POST['agency_id'])           ? intval($_POST['agency_id'])       : 0;
        $suppliers['new']['shop_price']        = !empty($_POST['shop_price'])          ? intval($_POST['shop_price'])      : 30;
        $suppliers['new']['office_phone']      = !empty($_POST['office_phone'])        ? trim($_POST['office_phone'])      : '';
        $suppliers['new']['mobile_phone']      = !empty($_POST['mobile_phone'])        ? trim($_POST['mobile_phone'])      : '';
        $suppliers['new']['opening_hours']      = !empty($_POST['opening_hours'])       ? trim($_POST['opening_hours'])    : '';
        $suppliers['new']['comment_rank']      = !empty($_POST['comment_rank'])        ? intval($_POST['comment_rank'])    : 3;
        $suppliers['new']['region_cities']     = !empty($_POST['city'])                ? intval($_POST['city'])            : 0;
        $suppliers['new']['region_districts']  = !empty($_POST['district'])            ? intval($_POST['district'])        : 0;     
        $suppliers['new']['place_id']          = !empty($_POST['place'])               ? intval($_POST['place'])           : 0;         
        $suppliers['new']['map_lat']           = !empty($_POST['map_lat'])             ? doubleval($_POST['map_lat'])      : 0;
        $suppliers['new']['map_lng']           = !empty($_POST['map_lng'])             ? doubleval($_POST['map_lng'])      : 0;
        $suppliers['new']['address']           = !empty($_POST['suppliers_address'])   ? trim($_POST['suppliers_address']) : '';
        $suppliers['new']['suppliers_desc']    = !empty($_POST['suppliers_desc'])      ? trim($_POST['suppliers_desc'])    : '';


        /* 获取门店信息 */
        $sql = "SELECT * FROM " . $ecs->table('suppliers') . " WHERE suppliers_id = '" . $suppliers['id'] . "'";
        $suppliers['old'] = $db->getRow($sql);
        if (empty($suppliers['old']['suppliers_id']))
        {
            sys_msg('suppliers does not exist');
        }

        /* 判断名称是否重复 */
        $sql = "SELECT suppliers_id
                FROM " . $ecs->table('suppliers') . "
                WHERE suppliers_name = '" . $suppliers['new']['suppliers_name'] . "'
                AND suppliers_id <> '" . $suppliers['id'] . "'";

        $row = $db->getRow($sql);        
        if ($db->getOne($sql))
        {
            sys_msg($_LANG['suppliers_name_exist']);
        }

        // 如果上传了图标,并且有旧图，删除旧图
        if(($_FILES['logo_img']['tmp_name'] != '' && $_FILES['logo_img']['tmp_name'] != 'none')){

            $old_logo_img = $suppliers['old']['logo_img'];

            if ($old_logo_img != '' && is_file('../data/supplierimg/' . $old_logo_img))
            {
                @unlink('../data/supplierimg/' . $old_logo_img);
            }

        $logo_img_name = basename($image->upload_image($_FILES['logo_img'],'supplierimg'));
        $suppliers['new']['logo_img'] = $logo_img_name;

        }
        // 如果上传了图标缩略图，并且有旧图，删除旧图
        if(($_FILES['logo_thumb']['tmp_name'] != '' && $_FILES['logo_thumb']['tmp_name'] != 'none')){

            $old_logo_thumb = $suppliers['old']['logo_thumb'];
           if ($old_logo_thumb != '' && is_file('../data/supplierimg/' . $old_logo_thumb))
            {
                @unlink('../data/supplierimg/' . $old_logo_thumb);
            }

            $logo_thumb_name = basename($image->upload_image($_FILES['logo_thumb'],'supplierimg'));
            $suppliers['new']['logo_thumb'] = $logo_thumb_name;        
        }


        foreach ($_FILES['img_url']['name'] AS $key => $name)
        {
            if($_FILES['img_url']['name'][$key] !== '') { 
                $suppliers['new']['is_has_gallery'] = 1;
                break;
            }
        }

        /* 保存门店信息 */
        $db->autoExecute($ecs->table('suppliers'), $suppliers['new'], 'UPDATE', "suppliers_id = '" . $suppliers['id'] . "'");


        /* 处理相册图片 */
        handle_gallery_image($suppliers['id'], $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file']);


        /* 清空门店的管理员 */
        $sql = "UPDATE " . $ecs->table('admin_user') . " SET suppliers_id = 0, action_list = '" . SUPPLIERS_ACTION_LIST . "' WHERE suppliers_id = '" . $suppliers['id'] . "'";
        $db->query($sql);

        /* 添加供货商的管理员 */
        if (isset($_POST['admins']))
        {
            $sql = "UPDATE " . $ecs->table('admin_user') . " SET suppliers_id = '" . $suppliers['old']['suppliers_id'] . "' WHERE user_id " . db_create_in($_POST['admins']);
            $db->query($sql);
        }

        /* 记日志 */
        admin_log($suppliers['old']['suppliers_name'], 'edit', 'supplier');

        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $links[] = array('href' => 'suppliers.php?act=list', 'text' => $_LANG['back_suppliers_list']);
        sys_msg($_LANG['edit_suppliers_ok'], 0, $links);
    }

}
/*------------------------------------------------------ */
//-- 删除图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_image')
{
    check_authz_json('suppliers_manage');

    $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);

    /* 删除图片文件 */
    $sql = "SELECT img_url, thumb_url, img_original, supplier_id " .
            " FROM " . $GLOBALS['ecs']->table('supplier_gallery') .
            " WHERE img_id = '$img_id'";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row['img_url'] != '' && is_file('../' . $row['img_url']))
    {
        @unlink('../' . $row['img_url']);
    }
    if ($row['thumb_url'] != '' && is_file('../' . $row['thumb_url']))
    {
        @unlink('../' . $row['thumb_url']);
    }
    if ($row['img_original'] != '' && is_file('../' . $row['img_original']))
    {
        @unlink('../' . $row['img_original']);
    }

    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('supplier_gallery') . " WHERE img_id = '$img_id' LIMIT 1";
    $GLOBALS['db']->query($sql);

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('supplier_gallery') . " WHERE supplier_id = '" .$row['supplier_id'] ."'";
    $count = $GLOBALS['db']->getOne($sql);

    if($count < 1){

        $sql = "UPDATE " . $ecs->table('suppliers') . " SET is_has_gallery = 0 WHERE suppliers_id = '" . $row['supplier_id'] . "'";
        $db->query($sql);
    }

    clear_cache_files();
    make_json_result($img_id);
}
/*------------------------------------------------------ */
//-- 显示图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'show_logo')
{

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $img_url = $_GET['img_url'];
    }
    else
    {
        if (strpos($_GET['img_url'], 'http://') === 0)
        {
            $img_url = $_GET['img_url'];
        }
        else
        {
            $img_url = '../data/supplierimg/' . $_GET['img_url'];
        }
    }
    $smarty->assign('img_url', $img_url);
    $smarty->display('goods_show_image.htm');
}

/*------------------------------------------------------ */
//-- 显示相册图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'show_image')
{

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $img_url = $_GET['img_url'];
    }
    else
    {
        if (strpos($_GET['img_url'], 'http://') === 0)
        {
            $img_url = $_GET['img_url'];
        }
        else
        {
            $img_url = '../' . $_GET['img_url'];
        }
    }
    $smarty->assign('img_url', $img_url);
    $smarty->display('goods_show_image.htm');
}

/**
 * 获得指定商品的相册
 *
 * @access  public
 * @param   integer     $supplier_id
 * @return  array
 */
function get_supplier_gallery($supplier_id)
{
    $sql = 'SELECT img_id, img_url, thumb_url, img_desc' .
        ' FROM ' . $GLOBALS['ecs']->table('ecs_supplier_gallery') .
        " WHERE supplier_id = '$supplier_id' LIMIT " . $GLOBALS['_CFG']['supplier_gallery_number'];
    $row = $GLOBALS['db']->getAll($sql);
    /* 格式化相册图片路径 */
    foreach($row as $key => $gallery_img)
    {
        $row[$key]['img_url'] = get_image_path($supplier_id, $gallery_img['img_url'], false, 'gallery');
        $row[$key]['thumb_url'] = get_image_path($supplier_id, $gallery_img['thumb_url'], true, 'gallery');
    }
    return $row;
}

/**
 * 分支机构列表
 *
 * @access  public
 * @return  array
 */
function agencies_list_name()
{
    $sql = 'SELECT agency_id, agency_name' .
        ' FROM ' . $GLOBALS['ecs']->table('agency') .
        " ORDER BY agency_id ASC ";
    $row = $GLOBALS['db']->getAll($sql);
    return $row;
}

/**
 *  获取门店列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function suppliers_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['pcat_id']          = empty($_REQUEST['pcat_id']) ? 0 : intval($_REQUEST['pcat_id']);
        $filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['intro_type']       = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
        if($_SESSION['shop_id'] > 0){
            $filter['shop_id'] = $_SESSION['shop_id'];
        }else{
            $filter['shop_id'] = isset($_REQUEST['shop_id']) ? (empty($_REQUEST['shop_id']) ? '' : trim($_REQUEST['shop_id'])) : '';            
        }
        $filter['is_on_check'] = isset($_REQUEST['is_on_check']) ? ((empty($_REQUEST['is_on_check']) && $_REQUEST['is_on_check'] === 0) ? '' : trim($_REQUEST['is_on_check'])) : '';
        $filter['keyword']          = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
        /* 过滤信息 */
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'suppliers_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE 1';

        /* 推荐类型 */
        switch ($filter['intro_type'])
        {
            case 'is_best':
                $where .= " AND is_best=1";
                break;
            case 'is_hot':
                $where .= ' AND is_hot=1';
                break;
            case 'is_new':
                $where .= ' AND is_new=1';
                break;
            case 'all_type';
        }

        /* 主分类 */
        if (!empty($filter['pcat_id']))
        {
            $where .= " AND (pcat_id = '" . $filter['pcat_id'] . "')";
        }

        /* 子分类 */
        if (!empty($filter['cat_id']))
        {
            $where .= " AND (cat_id = '" . $filter['cat_id'] . "')";
        }  

        /* 商户 */
        if (!empty($filter['shop_id']))
        {
            $where .= " AND (shop_id = '" . $filter['shop_id'] . "')";
        }

        /* 状态 */
        if ($filter['is_on_check'] !== '')
        {
            $where .= " AND (is_check = '" . $filter['is_on_check'] . "')";
        }

        /* 关键字 */
        if (!empty($filter['keyword']))
        {
            $where .= " AND (suppliers_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%')";
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('suppliers') . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT suppliers_id, suppliers_name, cat_id, region_cities, comment_rank, office_phone, is_best, suppliers_desc, is_check
                FROM " . $GLOBALS['ecs']->table("suppliers") . "
                $where
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. "
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    $arr = array('result' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}


/**
 * 保存某门店的相册图片
 * @param   int     $supplier_id   门店标识
 * @param   array   $image_files
 * @param   array   $image_descs
 * @return  void
 */
function handle_gallery_image($supplier_id, $image_files, $image_descs, $image_urls)
{
    /* 是否处理缩略图 */
    $proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)? false : true;

    foreach ($image_descs AS $key => $img_desc)
    {
        /* 是否成功上传 */
        $flag = false;
        if (isset($image_files['error']))
        {
            if ($image_files['error'][$key] == 0)
            {
                $flag = true;
            }
        }
        else
        {
            if ($image_files['tmp_name'][$key] != 'none')
            {
                $flag = true;
            }
        }

        if ($flag)
        {
            // 生成缩略图
            if ($proc_thumb)
            {
                $thumb_url = $GLOBALS['image']->make_thumb($image_files['tmp_name'][$key], $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                $thumb_url = is_string($thumb_url) ? $thumb_url : '';
            }

            $upload = array(
                'name' => $image_files['name'][$key],
                'type' => $image_files['type'][$key],
                'tmp_name' => $image_files['tmp_name'][$key],
                'size' => $image_files['size'][$key],
            );
            if (isset($image_files['error']))
            {
                $upload['error'] = $image_files['error'][$key];
            }
            $img_original = $GLOBALS['image']->upload_image($upload);
            if ($img_original === false)
            {
                sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
            }
            $img_url = $img_original;

            if (!$proc_thumb)
            {
                $thumb_url = $img_original;
            }
            // 如果服务器支持GD 则添加水印
            if ($proc_thumb && gd_version() > 0)
            {
                $pos        = strpos(basename($img_original), '.');
                $newname    = dirname($img_original) . '/' . $GLOBALS['image']->random_filename() . substr(basename($img_original), $pos);
                copy('../' . $img_original, '../' . $newname);
                $img_url    = $newname;

                $GLOBALS['image']->add_watermark('../'.$img_url,'',$GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
            }

            /* 重新格式化图片名称 */
            $img_original = reformat_image_name('gallery', $supplier_id, $img_original, 'source');
            $img_url = reformat_image_name('gallery', $supplier_id, $img_url, 'supplier');
            $thumb_url = reformat_image_name('gallery_thumb', $supplier_id, $thumb_url, 'thumb');
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('supplier_gallery') . " (supplier_id, img_url, img_desc, thumb_url, img_original) " .
                    "VALUES ('$supplier_id', '$img_url', '$img_desc', '$thumb_url', '$img_original')";
            $GLOBALS['db']->query($sql);
            /* 不保留商品原图的时候删除原图 */
            if ($proc_thumb && !$GLOBALS['_CFG']['retain_original_img'] && !empty($img_original))
            {
                $GLOBALS['db']->query("UPDATE " . $GLOBALS['ecs']->table('supplier_gallery') . " SET img_original='' WHERE `supplier_id`='{$supplier_id}'");
                @unlink('../' . $img_original);
            }
        }
        elseif (!empty($image_urls[$key]) && ($image_urls[$key] != $GLOBALS['_LANG']['img_file']) && ($image_urls[$key] != 'http://') && copy(trim($image_urls[$key]), ROOT_PATH . 'temp/' . basename($image_urls[$key])))
        {
            $image_url = trim($image_urls[$key]);

            //定义原图路径
            $down_img = ROOT_PATH . 'temp/' . basename($image_url);

            // 生成缩略图
            if ($proc_thumb)
            {
                $thumb_url = $GLOBALS['image']->make_thumb($down_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                $thumb_url = is_string($thumb_url) ? $thumb_url : '';
                $thumb_url = reformat_image_name('gallery_thumb', $supplier_id, $thumb_url, 'thumb');
            }

            if (!$proc_thumb)
            {
                $thumb_url = htmlspecialchars($image_url);
            }

            /* 重新格式化图片名称 */
            $img_url = $img_original = htmlspecialchars($image_url);
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('supplier_gallery') . " (supplier_id, img_url, img_desc, thumb_url, img_original) " .
                    "VALUES ('$supplier_id', '$img_url', '$img_desc', '$thumb_url', '$img_original')";
            $GLOBALS['db']->query($sql);

            @unlink($down_img);
        }
    }
}

/**
 * 格式化商品图片名称（按目录存储）
 *
 */
function reformat_image_name($type, $supplier_id, $source_img, $position='')
{
    $rand_name = gmtime() . sprintf("%03d", mt_rand(1,999));
    $img_ext = substr($source_img, strrpos($source_img, '.'));
    $dir = 'images';
    if (defined('IMAGE_DIR'))
    {
        $dir = IMAGE_DIR;
    }
    $sub_dir = date('Ym', gmtime()).'/supplier';
    if (!make_dir(ROOT_PATH.$dir.'/'.$sub_dir))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/'.$sub_dir.'/source_img'))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/'.$sub_dir.'/supplier_img'))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/'.$sub_dir.'/thumb_img'))
    {
        return false;
    }
    switch($type)
    {
        case 'supplier':
            $img_name = $supplier_id . '_G_' . $rand_name;
            break;
        case 'supplier_thumb':
            $img_name = $supplier_id . '_thumb_G_' . $rand_name;
            break;
        case 'gallery':
            $img_name = $supplier_id . '_P_' . $rand_name;
            break;
        case 'gallery_thumb':
            $img_name = $supplier_id . '_thumb_P_' . $rand_name;
            break;
    }
    if ($position == 'source')
    {
        if (move_image_file(ROOT_PATH.$source_img, ROOT_PATH.$dir.'/'.$sub_dir.'/source_img/'.$img_name.$img_ext))
        {
            return $dir.'/'.$sub_dir.'/source_img/'.$img_name.$img_ext;
        }
    }
    elseif ($position == 'thumb')
    {
        if (move_image_file(ROOT_PATH.$source_img, ROOT_PATH.$dir.'/'.$sub_dir.'/thumb_img/'.$img_name.$img_ext))
        {
            return $dir.'/'.$sub_dir.'/thumb_img/'.$img_name.$img_ext;
        }
    }
    else
    {
        if (move_image_file(ROOT_PATH.$source_img, ROOT_PATH.$dir.'/'.$sub_dir.'/supplier_img/'.$img_name.$img_ext))
        {
            return $dir.'/'.$sub_dir.'/supplier_img/'.$img_name.$img_ext;
        }
    }
    return false;
}

function move_image_file($source, $dest)
{
    if (@copy($source, $dest))
    {
        @unlink($source);
        return true;
    }
    return false;
}

?>