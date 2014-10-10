<!-- $Id: brand_info.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<div class="main-div">
<form method="post" action="shop.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label"><?php echo $this->_var['lang']['shop_name']; ?></td>
    <td><input type="text" name="shop_name" maxlength="60" value="<?php echo $this->_var['shop']['shop_name']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_shop_category']; ?></td>
    <td><select name="cat_id" onchange="hideCatDiv()" ><option value="0"><?php echo $this->_var['lang']['select_please']; ?></option><?php echo $this->_var['cat_list']; ?></select>
      <?php if ($this->_var['is_add']): ?>
      <a href="javascript:void(0)" onclick="rapidCatAdd()" title="<?php echo $this->_var['lang']['rapid_add_cat']; ?>" class="special"><?php echo $this->_var['lang']['rapid_add_cat']; ?></a>
      <span id="category_add" style="display:none;">
      <input class="text" size="10" name="addedCategoryName" />
       <a href="javascript:void(0)" onclick="addCategory()" title="<?php echo $this->_var['lang']['button_submit']; ?>" class="special" ><?php echo $this->_var['lang']['button_submit']; ?></a>
       <a href="javascript:void(0)" onclick="return goCatPage()" title="<?php echo $this->_var['lang']['category_manage']; ?>" class="special" ><?php echo $this->_var['lang']['category_manage']; ?></a>
       <a href="javascript:void(0)" onclick="hideCatDiv()" title="<?php echo $this->_var['lang']['hide']; ?>" class="special" ><<</a>
       </span>
       <?php endif; ?>
       <?php echo $this->_var['lang']['require_field']; ?>
    </td>
  </tr>  
  <tr>
    <td class="label"><?php echo $this->_var['lang']['site_url']; ?></td>
    <td><input type="text" name="site_url" maxlength="60" size="40" value="<?php echo $this->_var['shop']['site_url']; ?>" /></td>
  </tr>
  <tr>
    <td class="label"><a href="javascript:showNotice('warn_shoplogo');" title="<?php echo $this->_var['lang']['form_notice']; ?>">
        <img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['shop_logo']; ?></td>
    <td><input type="file" name="shop_logo" id="logo" size="45"><?php if ($this->_var['shop']['shop_logo'] != ""): ?><input type="button" value="<?php echo $this->_var['lang']['drop_shop_logo']; ?>" onclick="if (confirm('<?php echo $this->_var['lang']['confirm_drop_logo']; ?>'))location.href='shop.php?act=drop_logo&id=<?php echo $this->_var['shop']['shop_id']; ?>'"><?php endif; ?>
    <br /><span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="warn_shoplogo">
    <?php if ($this->_var['shop']['shop_logo'] == ''): ?>
    <?php echo $this->_var['lang']['up_shoplogo']; ?>
    <?php else: ?>
    <?php echo $this->_var['lang']['warn_shoplogo']; ?>
    <?php endif; ?>
    </span>
    </td>
  </tr>
    <tr>
    <td class="label"><a href="javascript:showNotice('warn_lic_img');" title="<?php echo $this->_var['lang']['form_notice']; ?>">
        <img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['label_lic_img']; ?></td>
    <td><input type="file" name="lic_img" id="img_lic" size="45"><?php if ($this->_var['shop']['shop_logo'] != ""): ?><input type="button" value="<?php echo $this->_var['lang']['drop_lic_img']; ?>" onclick="if (confirm('<?php echo $this->_var['lang']['confirm_drop_licimg']; ?>'))location.href='shop.php?act=drop_licimg&id=<?php echo $this->_var['shop']['shop_id']; ?>'"><?php endif; ?>
    <br /><span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="warn_licimg">
    <?php if ($this->_var['shop']['shop_logo'] == ''): ?>
    <?php echo $this->_var['lang']['up_shoplogo']; ?>
    <?php else: ?>
    <?php echo $this->_var['lang']['warn_lic_img']; ?>
    <?php endif; ?>
    </span>
    </td>
  </tr>
  <tr>
    <td class="label"><a href="javascript:showNotice('warn_id_img');" title="<?php echo $this->_var['lang']['form_notice']; ?>">
        <img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['label_id_img']; ?></td>
    <td><input type="file" name="id_img" id="img_id" size="45"><?php if ($this->_var['shop']['shop_logo'] != ""): ?><input type="button" value="<?php echo $this->_var['lang']['drop_id_img']; ?>" onclick="if (confirm('<?php echo $this->_var['lang']['confirm_drop_idimg']; ?>'))location.href='shop.php?act=drop_idimg&id=<?php echo $this->_var['shop']['shop_id']; ?>'"><?php endif; ?>
    <br /><span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="warn_idimg">
    <?php if ($this->_var['shop']['shop_logo'] == ''): ?>
    <?php echo $this->_var['lang']['up_shoplogo']; ?>
    <?php else: ?>
    <?php echo $this->_var['lang']['warn_id_img']; ?>
    <?php endif; ?>
    </span>
    </td>
  </tr>    
  <tr>
    <td class="label"><?php echo $this->_var['lang']['shop_desc']; ?></td>
    <td><textarea  name="shop_desc" cols="60" rows="4"  ><?php echo $this->_var['shop']['shop_desc']; ?></textarea></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['sort_order']; ?></td>
    <td><input type="text" name="sort_order" maxlength="40" size="15" value="<?php echo $this->_var['shop']['sort_order']; ?>" /></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['is_show']; ?></td>
    <td><input type="radio" name="is_show" value="1" <?php if ($this->_var['brand']['is_show'] == 1): ?>checked="checked"<?php endif; ?> /> <?php echo $this->_var['lang']['yes']; ?>
        <input type="radio" name="is_show" value="0" <?php if ($this->_var['brand']['is_show'] == 0): ?>checked="checked"<?php endif; ?> /> <?php echo $this->_var['lang']['no']; ?>
        (<?php echo $this->_var['lang']['visibility_notes']; ?>)
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"><br />
      <input type="submit" class="button" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
      <input type="reset" class="button" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_action']; ?>" />
      <input type="hidden" name="old_shopname" value="<?php echo $this->_var['shop']['shop_name']; ?>" />
      <input type="hidden" name="id" value="<?php echo $this->_var['shop']['shop_id']; ?>" />
      <input type="hidden" name="old_shoplogo" value="<?php echo $this->_var['shop']['shop_logo']; ?>">
    </td>
  </tr>
</table>
</form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>

<script language="JavaScript">
<!--
document.forms['theForm'].elements['shop_name'].focus();
onload = function()
{
    // 开始检查订单
    startCheckOrder();
}
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("shop_name",  no_shopname);
    validator.isNumber("sort_order", require_num, true);
    return validator.passed();
}

  function rapidCatAdd()
  {
      var cat_div = document.getElementById("category_add");

      if(cat_div.style.display != '')
      {
          var cat =document.forms['theForm'].elements['addedCategoryName'];
          cat.value = '';
          cat_div.style.display = '';
      }
  }

  function addCategory()
  {
      var parent_id = document.forms['theForm'].elements['cat_id'];
      var cat = document.forms['theForm'].elements['addedCategoryName'];
      if(cat.value.replace(/^\s+|\s+$/g, '') == '')
      {
          alert(shopcate_name_not_null);
          return;
      }

      var params = 'parent_id=' + parent_id.value;
      params += '&cat=' + cat.value;
      Ajax.call('shopcate.php?is_ajax=1&act=add_category', params, addCatResponse, 'GET', 'JSON');
  }

  function hideCatDiv()
  {
      var category_add_div = document.getElementById("category_add");
      if(category_add_div.style.display != null)
      {
          category_add_div.style.display = 'none';
      }
  }

  function addCatResponse(result)
  {
      if (result.error == '1' && result.message != '')
      {
          alert(result.message);
          return;
      }

      var category_add_div = document.getElementById("category_add");
      category_add_div.style.display = 'none';

      var response = result.content;

      var selCat = document.forms['theForm'].elements['cat_id'];
      var opt = document.createElement("OPTION");
      opt.value = response.id;
      opt.selected = true;
      opt.innerHTML = response.cat;

      //鑾峰彇瀛愬垎绫荤殑绌烘牸鏁
      var str = selCat.options[selCat.selectedIndex].text;
      var temp = str.replace(/^\s+/g, '');
      var lengOfSpace = str.length - temp.length;
      if(response.parent_id != 0)
      {
          lengOfSpace += 4;
      }
      for (i = 0; i < lengOfSpace; i++)
      {
          opt.innerHTML = '&nbsp;' + opt.innerHTML;
      }

      for (i = 0; i < selCat.length; i++)
      {
          if(selCat.options[i].value == response.parent_id)
          {
              if(i == selCat.length)
              {
                  if (Browser.isIE)
                  {
                      selCat.add(opt);
                  }
                  else
                  {
                      selCat.appendChild(opt);
                  }
              }
              else
              {
                  selCat.insertBefore(opt, selCat.options[i + 1]);
              }
              //opt.selected = true;
              break;
          }

      }

      return;
  }

    function goCatPage()
    {
        if(confirm(go_shopcate_page))
        {
            window.location.href='shopcate.php?act=add';
        }
        else
        {
            return;
        }
    }
    
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>