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
    /* 检查参数 */
    $smarty->assign('ur_here',      $_LANG['refund_list']);
    // $smarty->assign('action_link',  array('text' => $_LANG['add_account'], 'href' => 'pay_log.php?act=add&user_id=' . $user_id));
    $smarty->assign('full_page',    1);

    $refund_list = get_refund_list($user_id, $pay_type);

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
    // 查用户
    // $user_name = empty($_REQUEST['user_name']) ? 0 : intval($_REQUEST['user_name']);
    // if ($user_id <= 0)
    // {
    //     sys_msg('invalid param');
    // }
    // $user = user_info($user_id);

    // if (empty($user))
    // {
    //     sys_msg($_LANG['user_not_exist']);
    // }
    // $smarty->assign('user', $user);

    if (empty($_REQUEST['process_type']) || !in_array($_REQUEST['process_type'],
        array('payed', 'refund_apply', 'refund_confirm', 'refunded')))
    {
        $pay_type = '';
    }
    else
    {
        $pay_type = $_REQUEST['pay_type'];
    }
    $smarty->assign('pay_type', $pay_type);

    $refund_list = get_refund_list($user_id, $pay_type);
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
    $sql = "SELECT log_id, status
            FROM " . $ecs->table('pay_log') . "
            WHERE log_id = '$id'";
    $refund = $db->getRow($sql, TRUE);

    if ($refund['log_id'])
    {
        $_refund['status'] = 2;
        $_refund['admin_note'] = '已经确认';
        $_refund['is_best'] = empty($refund['is_best']) ? 1 : 0;

        $db->autoExecute($ecs->table('pay_log'), $_refund, '', "log_id = '$id'");
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

?>