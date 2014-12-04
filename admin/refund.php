<?php

/**
 * ECSHOP 管理中心退款
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: account_log.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

/*------------------------------------------------------ */
//-- 退款记录列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{

    // print_r($_SESSION);
    /* 检查参数 */
    $smarty->assign('ur_here',      $_LANG['refund_list']);
    $smarty->assign('full_page',    1);

    $refund_list = refund_list();
    $smarty->assign('refund_list',  $refund_list['refund']);
    $smarty->assign('filter',       $refund_list['filter']);
    $smarty->assign('record_count', $refund_list['record_count']);
    $smarty->assign('page_count',   $refund_list['page_count']);

    assign_query_info();
    $smarty->display('refund_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    /* 检查参数 */

    $refund_list = refund_list($user_id, $pay_type);
    $smarty->assign('refund_list',  $refund_list['refund']);
    $smarty->assign('filter',       $refund_list['filter']);
    $smarty->assign('record_count', $refund_list['record_count']);
    $smarty->assign('page_count',   $refund_list['page_count']);

    make_json_result($smarty->fetch('refund_list.htm'), '',
        array('filter' => $account_list['filter'], 'page_count' => $account_list['page_count']));
}

elseif ($_REQUEST['act'] == 'confirm')
{
    // check_authz_json('refund_manage');

    $id = intval($_REQUEST['id']);
    $sql = "SELECT log_id, status, order_id, order_item_id
            FROM " . $ecs->table('pay_log') . "
            WHERE log_id = '$id'";
    $refund = $db->getRow($sql, TRUE);

    if ($refund['log_id'])
    {
        $_refund['status'] = 2;
        $_refund['admin_user'] = $_SESSION['admin_name'];
        $_refund['admin_note'] = '已经确认';

        $db->autoExecute($ecs->table('pay_log'), $_refund, '', "log_id = '$id'");

        $order = order_info($refund['order_id']);

        if($order['is_separate'] == 0){
            $_order['order_status'] = OS_REFUNDING;

            $order_goods_list = order_goods($refund['order_id']);
            foreach ($order_goods_list AS $key => $value)
            {
                if ($value['exchange_status'] == CD_APPLY_FOR_REFUND)
                {
                    $order_goods['exchange_status'] = CD_REFUNDING;
                    update_excode_goods($value['rec_id'], $order_goods);
                }
            }
            update_order($refund['order_id'],$_order);

        }else{
            // 处理虚拟物品分单退货
            $_order_goods['exchange_status'] = CD_REFUNDING;
            update_excode_goods($refund['order_item_id'], $_order_goods);
        }
        clear_cache_files();
        make_json_result($_refund['status']);
    }

    exit;
}

elseif ($_REQUEST['act'] == 'end')
{
    // check_authz_json('refund_manage');

    $id = intval($_REQUEST['id']);
    $sql = "SELECT log_id, status, order_id, order_item_id
            FROM " . $ecs->table('pay_log') . "
            WHERE log_id = '$id'";
    $refund = $db->getRow($sql, TRUE);

    if ($refund['log_id'])
    {
        $_refund['status'] = 3;
        $_refund['admin_note'] = '退款完成';
        $_refund['paid_time'] = gmtime();
        $db->autoExecute($ecs->table('pay_log'), $_refund, '', "log_id = '$id'");

        $order = order_info($refund['order_id']);
        if($order['is_separate'] == 0){
            $_order['order_status'] = OS_REFUNDED;

            $order_goods_list = order_goods($refund['order_id']);
            foreach ($order_goods_list AS $key => $value)
            {
                if ($value['exchange_status'] == CD_REFUNDING)
                {   
                    $order_goods['exchange_status'] = CD_REFUNDED;
                    update_excode_goods($value['rec_id'], $order_goods);
                }
            }
            update_order($refund['order_id'],$_order);

        }else{
            // 处理虚拟物品分单退货
            $_order_goods['exchange_status'] = CD_REFUNDED;
            update_excode_goods($refund['order_item_id'], $_order_goods);
        }
        clear_cache_files();
        make_json_result($_refund['status']);
    }

    exit;
}

/*------------------------------------------------------ */
//-- 调节帐户
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 检查权限 */
    admin_priv('account_manage');
    /* 检查参数 */
    $user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
    if ($user_id <= 0)
    {
        sys_msg('invalid param');
    }
    $user = user_info($user_id);
    if (empty($user))
    {
        sys_msg($_LANG['user_not_exist']);
    }
    $smarty->assign('user', $user);

    /* 显示模板 */
    $smarty->assign('ur_here', $_LANG['add_account']);
    $smarty->assign('action_link', array('href' => 'account_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['account_list']));
    assign_query_info();
    $smarty->display('paylog_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑办事处
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 检查权限 */
    admin_priv('account_manage');
    $token=trim($_POST['token']);
    if($token!=$_CFG['token'])
    {
        sys_msg($_LANG['no_account_change'], 1);
    }

    /* 检查参数 */
    $user_id = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
    if ($user_id <= 0)
    {
        sys_msg('invalid param');
    }
    $user = user_info($user_id);
    if (empty($user))
    {
        sys_msg($_LANG['user_not_exist']);
    }

    /* 提交值 */
    $change_desc    = sub_str($_POST['change_desc'], 255, false);
    $user_money     = floatval($_POST['add_sub_user_money']) * abs(floatval($_POST['user_money']));
    $frozen_money   = floatval($_POST['add_sub_frozen_money']) * abs(floatval($_POST['frozen_money']));
    $rank_points    = floatval($_POST['add_sub_rank_points']) * abs(floatval($_POST['rank_points']));
    $pay_points     = floatval($_POST['add_sub_pay_points']) * abs(floatval($_POST['pay_points']));

    if ($user_money == 0 && $frozen_money == 0 && $rank_points == 0 && $pay_points == 0)
    {
        sys_msg($_LANG['no_account_change']);
    }

    /* 保存 */
    log_account_change($user_id, $user_money, $frozen_money, $rank_points, $pay_points, $change_desc, ACT_ADJUSTING);

    /* 提示信息 */
    $links = array(
        array('href' => 'account_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['account_list'])
    );
    sys_msg($_LANG['log_account_change_ok'], 0, $links);
}

/**
 * 取得退款记录
 * @param   int     $user_id    用户id
 * @param   string  $pay_type   支付类型：payed表示支付，refund_apply表示退款申请，
 *                  refund_confirm表示退款确认，refunded表示退款完成
 * @return  array
 */
function get_refund_list($pay_type = '')
{
    /* 检查参数 */
    //refund_status

    $where = " WHERE process_type <> 0 ";
    // if (in_array($pay_type, array('payed', 'refund_apply', 'refund_confirm', 'refunded')))
    // {
    //     $where .= " AND $process_type <> 0 ";
    // }

    /* 初始化分页参数 */

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('pay_log') . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') . $where .
            " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['add_time']  = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
        $row['paid_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['paid_time']);
        $arr[] = $row;
    }

    return array('refund' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


/**
 *  获取退款列表信息
 *
 * @access  public
 * @param
 * @return void
 */
function refund_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'p.add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE p.process_type > 0 ';
        if ($filter['order_sn'])
        {
            $where .= " AND p.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['status'] >= 0)
        {
            $where .= " AND p.status = '" . mysql_like_quote($filter['status']) . "'";
        }

        /* 获取管理员信息 */
        // $admin_info = admin_info();

        /* 如果管理员属于某个办事处，只列出这个办事处管辖的退款单 */
        // if ($admin_info['agency_id'] > 0)
        // {
        //     $where .= " AND agency_id = '" . $admin_info['agency_id'] . "' ";
        // }

        /* 如果管理员属于某个供货商，只列出这个供货商的退款单 */
        // if ($admin_info['suppliers_id'] > 0)
        // {
        //     $where .= " AND suppliers_id = '" . $admin_info['suppliers_id'] . "' ";
        // }

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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('pay_log') . " AS p " . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT p.*, o.suppliers_id
                FROM " . $GLOBALS['ecs']->table('pay_log') . " AS p " .
                " LEFT JOIN " .$GLOBALS['ecs']->table('order_info'). " AS o ON p.order_id=o.order_id ". $where ."
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. "
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    /* 获取供货商列表 */
    // $suppliers_list = get_suppliers_list();
    // $_suppliers_list = array();
    // foreach ($suppliers_list as $value)
    // {
    //     $_suppliers_list[$value['suppliers_id']] = $value['suppliers_name'];
    // }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式化数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $row[$key]['paid_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['paid_time']);
        if ($value['status'] == 1)
        {
            $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
        }
        elseif ($value['status'] == 2)
        {
            $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][2];
        }
        else
        {
        $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
        }
        // $row[$key]['suppliers_name'] = isset($_suppliers_list[$value['suppliers_id']]) ? $_suppliers_list[$value['suppliers_id']] : '';
    }
    $arr = array('refund' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>