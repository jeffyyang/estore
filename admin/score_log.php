<?php

/**
 * ECSHOP 管理中心帐户变动记录
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
//-- 积分记录列表
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

    $change_type = empty($_REQUEST['change_type']) ? -1 : intval($_REQUEST['change_type']);
    $smarty->assign('change_type', $change_type);

    $smarty->assign('ur_here',      $_LANG['score_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['adjust_score'], 'href' => 'score_log.php?act=adjust&user_id=' . $user_id));
    $smarty->assign('full_page',    1);

    $score_list = scorelog_list($user_id);

    $smarty->assign('score_list',   $score_list['score']);
    $smarty->assign('filter',       $score_list['filter']);
    $smarty->assign('record_count', $score_list['record_count']);
    $smarty->assign('page_count',   $score_list['page_count']);

    assign_query_info();
    $smarty->display('scorelog_list.htm');
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

    if (empty($_REQUEST['account_type']) || !in_array($_REQUEST['account_type'],
        array('user_money', 'frozen_money', 'rank_points', 'pay_points')))
    {
        $account_type = '';
    }
    else
    {
        $account_type = $_REQUEST['account_type'];
    }
    $smarty->assign('account_type', $account_type);

    $account_list = get_scorelist($user_id, $account_type);
    $smarty->assign('score_list', $score_list['score']);
    $smarty->assign('filter',       $score_list['filter']);
    $smarty->assign('record_count', $score_list['record_count']);
    $smarty->assign('page_count',   $score_list['page_count']);

    make_json_result($smarty->fetch('scorelog_list'), '',
        array('filter' => $account_list['filter'], 'page_count' => $account_list['page_count']));
}

/*------------------------------------------------------ */
//-- 调节帐户
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'adjust')
{
    print_r($_SESSION);
    
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
    $smarty->assign('ur_here', $_LANG['adjust_score']);
    $smarty->assign('action_link', array('href' => 'score_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['score_list']));
    assign_query_info();
    $smarty->display('scorelog_info.htm');
}

/*------------------------------------------------------ */
//-- 提交添加、编辑积分记录
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
    $pay_points     = floatval($_POST['add_sub_pay_points']) * abs(floatval($_POST['pay_points']));

    if ($pay_points == 0)
    {
        sys_msg($_LANG['no_account_change']);
    }

    /* 保存 */
    log_score_change($user_id, $pay_points, $change_desc);

    /* 提示信息 */
    $links = array(
        array('href' => 'score_log.php?act=list&user_id=' . $user_id, 'text' => $_LANG['score_list'])
    );
    sys_msg($_LANG['log_account_change_ok'], 0, $links);
}




/**
 *  获取用户积分列表
 *
 * @access  public
 * @param
 * @return void
 */
function scorelog_list($user_id = 0)
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['pay_type'] = isset($_REQUEST['pay_type']) ? $_REQUEST['pay_type'] : -1;

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE user_id =' .$user_id ;

        if ($filter['pay_type'] > -1)
        {
            $where .= " AND p.process_type = '" . $filter['pay_type'] . "'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('user_score_his') . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('user_score_his') . $where ."
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

    /* 格式化数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $row[$key]['expire_time'] = local_date($GLOBALS['_CFG']['date_format'], $value['expire_time']);

        if($value['operator'] == '_BUY_GOODS_'){
            $row[$key]['change_type'] = '赠送';
            $row[$key]['src_dec'] = '消费赠送积分';
        }elseif ($value['operator'] == '_SCORE_GIFT_') {
            $row[$key]['change_type'] = '消费';
            $row[$key]['src_dec'] = '积分兑换消费';
        }else{
            $row[$key]['change_type'] = '赠送';
            $row[$key]['src_dec'] = '消费赠送积分';
        }
    }
    $arr = array('score' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   int     $pay_points     消费积分变动
 * @param   string  $change_desc    变动说明
 * @param   int     $change_type    变动类型：参见常量文件
 * @return  void
 */
function log_score_change($user_id, $pay_points = 0, $change_desc = '')
{

    /* 插入帐户变动记录 */
    $score_log = array(
        'user_id'       => $user_id,
        'add_type'      => 1,
        'pay_points'    => $pay_points,
        'add_time'      => gmtime(),
        'admin_user'    => $_SESSION['admin_name'],
        'admin_note'    => $change_desc,
        'operator'      => 'ACT_ADJUST'
    );
    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('user_score_his'), $score_log, 'INSERT');

    /* 更新用户信息 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('users') .
            " SET　pay_points = pay_points + ('$pay_points')" .
            " WHERE user_id = '$user_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
}

?>