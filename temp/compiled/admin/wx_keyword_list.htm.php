<!-- $Id: category_list.htm 17019 2010-01-29 10:10:34Z liuhui $ -->
<?php if ($this->_var['full_page']): ?>
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,listtable.js')); ?>

<form method="post" action="" name="listForm">
<!-- start ad position list -->
<div class="list-div" id="listDiv">
<?php endif; ?>

<table width="100%" cellspacing="1" cellpadding="2" id="list-table">
  <tr>
    <th><?php echo $this->_var['lang']['wx_keyword_id']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_keywords']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_content_type']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_content_article_id']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_content']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_status']; ?></th>
    <th><?php echo $this->_var['lang']['wx_keyword_create_time']; ?></th>
    <th><?php echo $this->_var['lang']['handler']; ?></th>
  </tr>
  <?php $_from = $this->_var['keyword_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');if (count($_from)):
    foreach ($_from AS $this->_var['keyword']):
?>
  <tr align="center" id="<?php echo $this->_var['keyword']['keyword_id']; ?>">
    <td align="left" class="first-cell" >
      <span><a href="wx_keyword.php?act=list&wx_keyword_id=<?php echo $this->_var['keyword']['keyword_id']; ?>"><?php echo $this->_var['keyword']['keyword_id']; ?></a></span>
    </td>
    <td width="5%"><?php echo $this->_var['keyword']['keywords']; ?></td>
    <td width="10%"><?php if ($this->_var['keyword']['msg_type'] == 'text'): ?> 纯文本消息 <?php else: ?>  图文消息 <?php endif; ?> </td>
    <td width="5%"><?php if ($this->_var['keyword']['article_id'] > 0): ?> <?php echo $this->_var['keyword']['article_id']; ?> <?php endif; ?> </td>
    <td width="30%"><?php echo $this->_var['keyword']['content']; ?></td>
    <td width="10%"><?php if ($this->_var['keyword']['status'] == 1): ?> 已生效 <?php else: ?>  未生效 <?php endif; ?></td>
    <td width="15%"><?php echo $this->_var['keyword']['create_time']; ?></td>
    <td width="24%" align="center">
      <a href="wx_keyword.php?act=edit&amp;keyword_id=<?php echo $this->_var['keyword']['keyword_id']; ?>"><?php echo $this->_var['lang']['edit']; ?></a> |
      <a href="javascript:;" onclick="listTable.remove(<?php echo $this->_var['keyword']['keyword_id']; ?>,'<?php echo $this->_var['lang']['drop_confirm']; ?>')" title="<?php echo $this->_var['lang']['remove']; ?>"><?php echo $this->_var['lang']['remove']; ?></a>
    </td>
  </tr>
  <?php endforeach; else: ?>
  <tr><td class="no-records" colspan="10"><?php echo $this->_var['lang']['no_records']; ?></td></tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php if ($this->_var['full_page']): ?>
</div>
</form>


<script language="JavaScript">
<!--
onload = function()
{
  // 开始检查订单
  startCheckOrder();
}
//-->
</script>


<?php echo $this->fetch('pagefooter.htm'); ?>
<?php endif; ?>