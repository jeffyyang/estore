<!-- $Id: agency_info.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'validator.js,../js/transport.js,../js/region.js')); ?>
<div class="main-div">
<form method="post" action="agency.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_agency_name']; ?></td>
    <td><input type="text" name="agency_name" maxlength="60" value="<?php echo $this->_var['agency']['agency_name']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_agency_desc']; ?></td>
    <td><textarea  name="agency_desc" cols="60" rows="4"  ><?php echo $this->_var['agency']['agency_desc']; ?></textarea></td>
  </tr>
  <tr>
    <td class="label">
    <a href="javascript:showNotice('noticeAdmins');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['label_admins']; ?></td>
    <td><?php $_from = $this->_var['agency']['admin_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'admin');if (count($_from)):
    foreach ($_from AS $this->_var['admin']):
?>
      <input type="checkbox" name="admins[]" value="<?php echo $this->_var['admin']['user_id']; ?>" <?php if ($this->_var['admin']['type'] == "this"): ?>checked="checked"<?php endif; ?> />
      <?php echo $this->_var['admin']['user_name']; ?><?php if ($this->_var['admin']['type'] == "other"): ?>(*)<?php endif; ?>&nbsp;&nbsp;
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?><br />
    <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeAdmins"><?php echo $this->_var['lang']['notice_admins']; ?></span></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_regions']; ?></td>
    <td id="regionCell"><?php $_from = $this->_var['agency']['region_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'region');if (count($_from)):
    foreach ($_from AS $this->_var['region']):
?>
        <input type="checkbox" name="regions[]" value="<?php echo $this->_var['region']['region_id']; ?>" checked="true" />
        <?php echo $this->_var['region']['region_name']; ?>&nbsp;&nbsp;
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> </td>
  </tr>
</table>
<hr>
<table cellspacing="1" cellpadding="3" width="100%">
  <caption><strong><?php echo $this->_var['lang']['add_region']; ?></strong></caption>
  <tr>
    <td width="10%">&nbsp;</td>
    <td><?php echo $this->_var['lang']['label_country']; ?></td>
    <td><?php echo $this->_var['lang']['label_province']; ?></td>
    <td><?php echo $this->_var['lang']['label_city']; ?></td>
    <td><?php echo $this->_var['lang']['label_district']; ?></td>
    <td width="10">&nbsp;</td>
    <td width="10%">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><select name="country" id="selCountries" onChange="region.changed(this, 1, 'selProvinces')" size="10">
      <?php $_from = $this->_var['countries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');$this->_foreach['fe_country'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_country']['total'] > 0):
    foreach ($_from AS $this->_var['country']):
        $this->_foreach['fe_country']['iteration']++;
?>
        <option value="<?php echo $this->_var['country']['region_id']; ?>" <?php if (($this->_foreach['fe_country']['iteration'] <= 1)): ?>selected<?php endif; ?>><?php echo htmlspecialchars($this->_var['country']['region_name']); ?></option>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </select></td>
    <td><select name="province" id="selProvinces" onChange="region.changed(this, 2, 'selCities')" size="10">
      <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
    </select></td>
    <td><select name="city" id="selCities" onChange="region.changed(this, 3, 'selDistricts')" size="10">
      <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
    </select></td>
    <td><select name="district" id="selDistricts" size="10">
      <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
    </select></td>
    <td><input type="button" value="+" class="button" onclick="addRegion()" /></td>
    <td>&nbsp;</td>
  </tr>
</table>

<table align="center">
  <tr>
    <td colspan="2" align="center">
      <input type="submit" class="button" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
      <input type="reset" class="button" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_action']; ?>" />
      <input type="hidden" name="id" value="<?php echo $this->_var['agency']['agency_id']; ?>" />
    </td>
  </tr>
</table>
</form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>

<script language="JavaScript">
<!--
region.isAdmin = true;
document.forms['theForm'].elements['agency_name'].focus();
onload = function()
{
    var selCountry = document.forms['theForm'].elements['country'];
    if (selCountry.selectedIndex >= 0)
    {
        region.loadProvinces(selCountry.options[selCountry.selectedIndex].value);
    }
    // 开始检查订单
    startCheckOrder();
}
/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("agency_name",  no_agencyname);
    return validator.passed();
}

/**
 * 添加一个区域
 */
function addRegion()
{
    var selCountry  = document.forms['theForm'].elements['country'];
    var selProvince = document.forms['theForm'].elements['province'];
    var selCity     = document.forms['theForm'].elements['city'];
    var selDistrict = document.forms['theForm'].elements['district'];
    var regionCell  = document.getElementById("regionCell");

    if (selDistrict.selectedIndex > 0)
    {
        regionId = selDistrict.options[selDistrict.selectedIndex].value;
        regionName = selDistrict.options[selDistrict.selectedIndex].text;
    }
    else
    {
        if (selCity.selectedIndex > 0)
        {
            regionId = selCity.options[selCity.selectedIndex].value;
            regionName = selCity.options[selCity.selectedIndex].text;
        }
        else
        {
            if (selProvince.selectedIndex > 0)
            {
                regionId = selProvince.options[selProvince.selectedIndex].value;
                regionName = selProvince.options[selProvince.selectedIndex].text;
            }
            else
            {
                if (selCountry.selectedIndex >= 0)
                {
                    regionId = selCountry.options[selCountry.selectedIndex].value;
                    regionName = selCountry.options[selCountry.selectedIndex].text;
                }
                else
                {
                    return;
                }
            }
        }
    }

    // 检查该地区是否已经存在
    exists = false;
    for (i = 0; i < document.forms['theForm'].elements.length; i++)
    {
      if (document.forms['theForm'].elements[i].type=="checkbox" && document.forms['theForm'].elements[i].name.substr(0, 6) == 'region')
      {
        if (document.forms['theForm'].elements[i].value == regionId)
        {
          exists = true;
          alert(region_exists);
          break;
        }
      }
    }

    // 创建checkbox
    if (!exists)
    {
      regionCell.innerHTML += "<input type='checkbox' name='regions[]' value='" + regionId + "' checked='true' /> " + regionName + "&nbsp;&nbsp;";
    }
}
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>