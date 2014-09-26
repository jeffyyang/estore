<!-- $Id: agency_info.htm 14216 2008-03-10 02:27:21Z testyang $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'validator.js,../js/transport.js,../js/region.js')); ?>
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp&key=262BZ-6OFHJ-25UFG-FD2KP-C6MNZ-QCBCO"></script>
<div class="main-div">
<form method="post" action="suppliers.php" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_name']; ?></td>
    <td><input type="text" name="suppliers_name" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_name']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_brand']; ?></td>
    <td><select name="brand_id" onchange="hideBrandDiv()" ><option value="0"><?php echo $this->_var['lang']['select_please']; ?><?php echo $this->html_options(array('options'=>$this->_var['brand_list'],'selected'=>$this->_var['suppliers']['brand_id'])); ?>
    </select>
      <?php if ($this->_var['is_add']): ?>
      <a href="javascript:void(0)" title="<?php echo $this->_var['lang']['rapid_add_brand']; ?>" onclick="rapidBrandAdd()" class="special" ><?php echo $this->_var['lang']['rapid_add_brand']; ?></a>
      <span id="brand_add" style="display:none;">
      <input class="text" size="15" name="addedBrandName" />
       <a href="javascript:void(0)" onclick="addBrand()" class="special" ><?php echo $this->_var['lang']['button_submit']; ?></a>
       <a href="javascript:void(0)" onclick="return goBrandPage()" title="<?php echo $this->_var['lang']['brand_manage']; ?>" class="special" ><?php echo $this->_var['lang']['brand_manage']; ?></a>
       <a href="javascript:void(0)" onclick="hideBrandDiv()" title="<?php echo $this->_var['lang']['hide']; ?>" class="special" ><<</a>
       </span>
       <?php endif; ?>
    </td>
  </tr>   
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_category']; ?></td>
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
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_posters']; ?></td>
    <td>
      <!-- 商户相册 -->
      <table width="90%" id="gallery-table" style="display:block" align="center">
        <!-- 相册列表 -->
        <tr>
          <td>
            <?php $_from = $this->_var['img_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('i', 'img');if (count($_from)):
    foreach ($_from AS $this->_var['i'] => $this->_var['img']):
?>
            <div id="gallery_<?php echo $this->_var['img']['img_id']; ?>" style="float:left; text-align:center; border: 1px solid #DADADA; margin: 4px; padding:2px;">
              <a href="javascript:;" onclick="if (confirm('<?php echo $this->_var['lang']['drop_img_confirm']; ?>')) dropImg('<?php echo $this->_var['img']['img_id']; ?>')">[-]</a><br />
              <a href="goods.php?act=show_image&img_url=<?php echo $this->_var['img']['img_url']; ?>" target="_blank">
              <img src="../<?php if ($this->_var['img']['thumb_url']): ?><?php echo $this->_var['img']['thumb_url']; ?><?php else: ?><?php echo $this->_var['img']['img_url']; ?><?php endif; ?>" <?php if ($this->_var['thumb_width'] != 0): ?>width="<?php echo $this->_var['thumb_width']; ?>"<?php endif; ?> <?php if ($this->_var['thumb_height'] != 0): ?>height="<?php echo $this->_var['thumb_height']; ?>"<?php endif; ?> border="0" />
              </a><br />
              <input type="text" value="<?php echo htmlspecialchars($this->_var['img']['img_desc']); ?>" size="15" name="old_img_desc[<?php echo $this->_var['img']['img_id']; ?>]" />
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <!-- 添加图片 -->
        <tr>
          <td>
            <a href="javascript:;" onclick="addImg(this)">[+]</a>
            <?php echo $this->_var['lang']['img_desc']; ?> <input type="text" name="img_desc[]" size="20" />
            <?php echo $this->_var['lang']['img_url']; ?> <input type="file" name="img_url[]" />
            <input type="text" size="40" value="<?php echo $this->_var['lang']['img_file']; ?>" style="color:#aaa;" onfocus="if (this.value == '<?php echo $this->_var['lang']['img_file']; ?>'){this.value='http://';this.style.color='#000';}" name="img_file[]"/>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_comm_rank']; ?></td>
    <td><input type="text" name="comm_rank" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_comm_rank']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
<!--   <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_envi_rank']; ?></td>
    <td><input type="text" name="envi_rank" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_envi_rank']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_serv_rank']; ?></td>
    <td><input type="text" name="serv_rank" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_serv_rank']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>  -->
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_shop_price']; ?></td>
    <td><input type="text" name="serv_rank" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_shop_price']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr> 
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_phone']; ?></td>
    <td><input type="text" name="office_phone" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_phone']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>   
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_mobile']; ?></td>
    <td><input type="text" name="mobile_phone" maxlength="60" value="<?php echo $this->_var['suppliers']['suppliers_mobile']; ?>" /><?php echo $this->_var['lang']['require_field']; ?></td>
  </tr>    
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_address']; ?></td>
    <td><textarea  name="suppliers_address" cols="60" rows="3"  ><?php echo $this->_var['suppliers']['suppliers_address']; ?></textarea></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_region']; ?></td>
    <td>
          <!-- 省份      
          <span class="label"><?php echo $this->_var['lang']['label_province']; ?></span>
          <select name="province" id="selProvinces" onChange="region.changed(this, 2, 'selCities')" >
            <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
            <?php echo $this->html_options(array('options'=>$this->_var['province_list'],'selected'=>$this->_var['suppliers']['brand_id'])); ?>
          </select> 
          -->
          <span class="label"><?php echo $this->_var['lang']['label_city']; ?></span>
          <select name="city" id="selCities" onChange="region.isAdmin = true;region.changed(this, 3, 'selDistricts')" >
            <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
            <?php echo $this->html_options(array('options'=>$this->_var['city_list'],'selected'=>$this->_var['suppliers']['region_cities'])); ?>
          </select>
          <span class="label"><?php echo $this->_var['lang']['label_district']; ?></span>
          <select name="district" id="selDistricts" >
            <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
          </select>
          <span class="label"><?php echo $this->_var['lang']['label_place']; ?></span>
          <select name="place" id="selPlace" >
            <option value=""><?php echo $this->_var['lang']['select_please']; ?></option>
          </select>
          <?php if ($this->_var['is_add']): ?>
            <a href="javascript:void(0)" title="<?php echo $this->_var['lang']['rapid_add_place']; ?>" onclick="rapidPlaceAdd()" class="special" ><?php echo $this->_var['lang']['rapid_add_place']; ?></a>
            <span id="place_add" style="display:none;">
            <input class="text" size="15" name="addedPlaceName" />
             <a href="javascript:void(0)" onclick="addPlace()" class="special" ><?php echo $this->_var['lang']['button_submit']; ?></a>
             <a href="javascript:void(0)" onclick="return goPlacePage()" title="<?php echo $this->_var['lang']['place_manage']; ?>" class="special" ><?php echo $this->_var['lang']['place_manage']; ?></a>
             <a href="javascript:void(0)" onclick="hidePlaceDiv()" title="<?php echo $this->_var['lang']['hide']; ?>" class="special" ><<</a>
             </span>
           <?php endif; ?>
           <?php echo $this->_var['lang']['require_field']; ?>            
    </td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_location']; ?></td>
    <td><div id="container" style="width:500px; height:300px"></div></td>
  </tr>        
  <tr>
    <td class="label"><?php echo $this->_var['lang']['label_suppliers_desc']; ?></td>
    <td><textarea  name="suppliers_desc" cols="60" rows="6"  ><?php echo $this->_var['suppliers']['suppliers_desc']; ?></textarea></td>
  </tr>
  <tr>
    <td class="label">
    <a href="javascript:showNotice('noticeAdmins');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['form_notice']; ?>"></a><?php echo $this->_var['lang']['label_admins']; ?></td>
    <td><?php $_from = $this->_var['suppliers']['admin_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'admin');if (count($_from)):
    foreach ($_from AS $this->_var['admin']):
?>
      <input type="checkbox" name="admins[]" value="<?php echo $this->_var['admin']['user_id']; ?>" <?php if ($this->_var['admin']['type'] == "this"): ?>checked="checked"<?php endif; ?> />
      <?php echo $this->_var['admin']['user_name']; ?><?php if ($this->_var['admin']['type'] == "other"): ?>(*)<?php endif; ?>&nbsp;&nbsp;
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?><br />
    <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeAdmins"><?php echo $this->_var['lang']['notice_admins']; ?></span></td>
  </tr>
</table>

<table align="center">
  <tr>
    <td colspan="2" align="center">
      <input type="submit" class="button" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
      <input type="reset" class="button" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
      <input type="hidden" name="act" value="<?php echo $this->_var['form_action']; ?>" />
      <input type="hidden" name="id" value="<?php echo $this->_var['suppliers']['suppliers_id']; ?>" />
      <input type="hidden" name="agency_id" value="<?php echo $this->_var['suppliers']['agency_id']; ?>" />
    </td>
  </tr>
</table>
</form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>

<script language="JavaScript">
<!--
document.forms['theForm'].elements['suppliers_name'].focus();

onload = function()
{
  init();
  // 开始检查订单
  startCheckOrder();
}

var init = function() {
    var map = new qq.maps.Map(document.getElementById('container'));
    var myLatLng = new qq.maps.LatLng(39.916527,116.397128);
    map.panTo(myLatLng);
}

/**
 * 检查表单输入的数据
 */
function validate()
{
    validator = new Validator("theForm");
    validator.required("suppliers_name",  no_suppliers_name);
    return validator.passed();
}

  /**
   * 连锁品牌管理
   */
  function rapidBrandAdd(conObj)
  {
      var brand_div = document.getElementById("brand_add");

      if(brand_div.style.display != '')
      {
          var brand =document.forms['theForm'].elements['addedBrandName'];
          brand.value = '';
          brand_div.style.display = '';
      }
  }

  function hideBrandDiv()
  {
      var brand_add_div = document.getElementById("brand_add");
      if(brand_add_div.style.display != 'none')
      {
          brand_add_div.style.display = 'none';
      }
  }

  function goBrandPage()
  {
      if(confirm(go_brand_page))
      {
          window.location.href='brand.php?act=add';
      }
      else
      {
          return;
      }
  }
  function addBrand()
  {
      var brand = document.forms['theForm'].elements['addedBrandName'];
      if(brand.value.replace(/^\s+|\s+$/g, '') == '')
      {
          alert(brand_cat_not_null);
          return;
      }

      var params = 'brand=' + brand.value;
      Ajax.call('brand.php?is_ajax=1&act=add_brand', params, addBrandResponse, 'GET', 'JSON');
  }

  function addBrandResponse(result)
  {
      if (result.error == '1' && result.message != '')
      {
          alert(result.message);
          return;
      }

      var brand_div = document.getElementById("brand_add");
      brand_div.style.display = 'none';

      var response = result.content;

      var selCat = document.forms['theForm'].elements['brand_id'];
      var opt = document.createElement("OPTION");
      opt.value = response.id;
      opt.selected = true;
      opt.text = response.brand;

      if (Browser.isIE)
      {
          selCat.add(opt);
      }
      else
      {
          selCat.appendChild(opt);
      }

      return;
  }

  /**
   * 商圈管理
   */
  function rapidPlaceAdd(conObj)
  {
      var brand_div = document.getElementById("place_add");

      if(brand_div.style.display != '')
      {
          var brand =document.forms['theForm'].elements['addedPlaceName'];
          brand.value = '';
          brand_div.style.display = '';
      }
  }

  function hidePlaceDiv()
  {
      var brand_add_div = document.getElementById("place_add");
      if(brand_add_div.style.display != 'none')
      {
          brand_add_div.style.display = 'none';
      }
  }

  function goPlacePage()
  {
      if(confirm(go_brand_page))
      {
          window.location.href='place.php?act=add';
      }
      else
      {
          return;
      }
  }
  function addPlace()
  {
      var place = document.forms['theForm'].elements['addedPlaceName'];
      var district = document.forms['theForm'].elements['district'];

      if(place.value.replace(/^\s+|\s+$/g, '') == '')
      {
          alert(brand_cat_not_null);
          return;
      }


      var params = 'district=' + district.value + '&place=' + place.value;
      alert(params);
      
      Ajax.call('place.php?is_ajax=1&act=add_place', params, addPlaceResponse, 'GET', 'JSON');
  }

  function addPlaceResponse(result)
  {
      if (result.error == '1' && result.message != '')
      {
          alert(result.message);
          return;
      }

      var brand_div = document.getElementById("place_add");
      brand_div.style.display = 'none';

      var response = result.content;

      var selCat = document.forms['theForm'].elements['place_id'];
      var opt = document.createElement("OPTION");
      opt.value = response.id;
      opt.selected = true;
      opt.text = response.brand;

      if (Browser.isIE)
      {
          selCat.add(opt);
      }
      else
      {
          selCat.appendChild(opt);
      }

      return;
  }
  /*快速管理商圈*/


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
          alert(category_cat_not_null);
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
        if(confirm(go_category_page))
        {
            window.location.href='category.php?act=add';
        }
        else
        {
            return;
        }
    }

  /**
   * 添加海报图片
   */
  function addImg(obj)
  {
      var src  = obj.parentNode.parentNode;
      var idx  = rowindex(src);
      var tbl  = document.getElementById('gallery-table');
      var row  = tbl.insertRow(idx + 1);
      var cell = row.insertCell(-1);
      cell.innerHTML = src.cells[0].innerHTML.replace(/(.*)(addImg)(.*)(\[)(\+)/i, "$1removeImg$3$4-");
  }

  /**
   * 删除海报图片
   */
  function removeImg(obj)
  {
      var row = rowindex(obj.parentNode.parentNode);
      var tbl = document.getElementById('gallery-table');

      tbl.deleteRow(row);
  }

  /**
   * 删除图片
   */
  function dropImg(imgId)
  {
    Ajax.call('goods.php?is_ajax=1&act=drop_image', "img_id="+imgId, dropImgResponse, "GET", "JSON");
  }

  function dropImgResponse(result)
  {
      if (result.error == 0)
      {
          document.getElementById('gallery_' + result.content).style.display = 'none';
      }
  }


//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>