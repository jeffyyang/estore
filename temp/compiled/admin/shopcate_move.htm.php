<!-- $Id: category_move.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<div class="main-div">
<form action="category.php" method="post" name="theForm" enctype="multipart/form-data">
<table width="100%">
  <tr>
    <td>
      <div style="font-weight:bold"><img src="images/notice.gif" width="16" height="16" border="0" /> <?php echo $this->_var['lang']['cat_move_desc']; ?></div>
       <ul>
         <li><?php echo $this->_var['lang']['cat_move_notic']; ?></li>
       </ul>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <strong><?php echo $this->_var['lang']['source_cat']; ?></strong>&nbsp;&nbsp;
      <select name="cat_id">
       <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
       <?php echo $this->_var['cat_select']; ?>
      </select>&nbsp;&nbsp;
      <strong><?php echo $this->_var['lang']['target_cat']; ?></strong>
      <select name="target_cat_id">
       <option value="0"><?php echo $this->_var['lang']['select_please']; ?></option>
       <?php echo $this->_var['cat_select']; ?>
      </select>&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" name="move_cat" value="<?php echo $this->_var['lang']['start_move_cat']; ?>" class="button">
      <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" class="button" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_act']; ?>" />
    </td>
  </tr>
</table>
</form>
</div>

<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>

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