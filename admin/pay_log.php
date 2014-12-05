<?php

/**
 * ECSHOP 管理中心付款记录
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: pay_log.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

/*------------------------------------------------------ */
//-- 支付记录列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
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

    $pay_type = empty($_REQUEST['pay_type']) ? -1 : intval($_REQUEST['pay_type']);
    $smarty->assign('pay_type',  $pay_type);

    $smarty->assign('ur_here',      $_LANG['pay_list']);
    // $smarty->assign('action_link',  array('text' => $_LANG['add_account'], 'href' => 'pay_log.php?act=add&user_id=' . $user_id));
    $smarty->assign('full_page',    1);

    $paylog_list = paylog_list($user_id);
    $smarty->assign('paylog_list',  $paylog_list['paylog']);
    $smarty->assign('filter',       $paylog_list['filter']);
    $smarty->assign('record_count', $paylog_list['record_count']);
    $smarty->assign('page_count',   $paylog_list['page_count']);

    assign_query_info();
    $smarty->display('paylog_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
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


     $paylog_list = paylog_list($user_id);

    $smarty->assign('pay_list', $paylog_list['paylog']);
    $smarty->assign('filter',       $paylog_list['filter']);
    $smarty->assign('record_count', $paylog_list['record_count']);
    $smarty->assign('page_count',   $paylog_list['page_count']);

    make_json_result($smarty->fetch('paylog_list.htm'), '',
        array('filter' => $paylog_list['filter'], 'page_count' => $paylog_list['page_count']));
}

/*------------------------------------------------------ */
//-- 用户调账
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
    $smarty->assign('action_link', array('href' => 'pay_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['account_list']));
    assign_query_info();
    $smarty->display('paylog_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑调账
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

    if ($user_money == 0)
    {
        sys_msg($_LANG['no_account_change']);
    }

    /* 保存 */
    log_account_change($user_id, $user_money, $frozen_money, $rank_points, $pay_points, $change_desc, ACT_ADJUSTING);

    /* 提示信息 */
    $links = array(
        array('href' => 'pay_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['paylog_list'])
    );
    sys_msg($_LANG['log_account_change_ok'], 0, $links);
}


/**
 *  获取支付记录
 *
 * @access  public
 * @param
 * @return void
 */
function paylog_list($user_id = 0)
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['status'] = isset($_REQUEST['status']) ? $_REQUEST['status'] : -1;
        $filter['pay_type'] = isset($_REQUEST['pay_type']) ? $_REQUEST['pay_type'] : -1;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'p.add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE p.user_id =' .$user_id ;

        if ($filter['pay_type'] > -1)
        {
            $where .= " AND p.process_type = '" . $filter['pay_type'] . "'";
        }

        if ($filter['order_sn'])
        {
            $where .= " AND p.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['status'] > 0)
        {
            $where .= " AND p.status = '" . mysql_like_quote($filter['status']) . "'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('pay_log') . " AS p " . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT p.* FROM " . $GLOBALS['ecs']->table('pay_log') . " AS p " . $where ."
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
            $row[$key]['status_name'] = $GLOBALS['_LANG']['pay_status'][1];
        }
        elseif ($value['status'] == 2)
        {
            $row[$key]['status_name'] = $GLOBALS['_LANG']['pay_status'][2];
        }
        elseif ($value['status'] == 3)
        {
            $row[$key]['status_name'] = $GLOBALS['_LANG']['pay_status'][3];
        }        
        else
        {
        $row[$key]['status_name'] = $GLOBALS['_LANG']['pay_status'][0];
        }
        // $row[$key]['suppliers_name'] = isset($_suppliers_list[$value['suppliers_id']]) ? $_suppliers_list[$value['suppliers_id']] : '';
    }
    $arr = array('paylog' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>