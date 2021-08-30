<?php
/**
 * @copyright Custom Scriptz
 * http://customscriptz.com | diego@customscriptz.com
 * Cannot be sold or freely distributed
 */


$checkfields = $db->metaColumns(TABLE_PRODUCTS);
if (!$checkfields['PRODUCTS_KEYWORDS']->type)
	$db->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_keywords VARCHAR(100) NULL DEFAULT NULL");

$manufacturers_id = '';
if (isset($_SESSION['provider_id'])) $manufacturers_id = $_SESSION['provider_id'];

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
    $parameters = array('products_name' => '',
                       'products_description' => '',
                       'products_url' => '',
                       'products_id' => '',
                       'products_quantity' => '1',
                       'products_model' => '',
                       'products_image' => '',
                       'products_price' => '',
                       'products_virtual' => DEFAULT_PRODUCT_PRODUCTS_VIRTUAL,
                       'products_weight' => '',
                       'products_date_added' => date('Y-m-d'),
                       'products_last_modified' => '',
                       'products_date_available' => '',
                       'products_status' => '1',
                       'products_tax_class_id' => DEFAULT_PRODUCT_TAX_CLASS_ID,
                       'manufacturers_id' => $manufacturers_id,
                       'products_quantity_order_min' => '',
                       'products_quantity_order_units' => '',
                       'products_priced_by_attribute' => '',
                       'product_is_free' => '',
                       'product_is_call' => '',
                       'products_quantity_mixed' => '',
                       'product_is_always_free_shipping' => DEFAULT_PRODUCT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING,
                       'products_qty_box_status' => PRODUCTS_QTY_BOX_STATUS,
                       'products_quantity_order_max' => '',
                       'products_sort_order' => '0',
                       'products_discount_type' => '0',
                       'products_discount_type_from' => '0',
                       'products_price_sorter' => '0',
                       'master_categories_id' => ''
                       );

    $pInfo = new objectInfo($parameters);

    if (isset($_GET['pID']) && empty($_POST)) {
      $product = $db->Execute("select pd.products_name, pd.products_description, pd.products_url,
                                      p.products_id, p.products_quantity, p.products_model,
                                      p.products_image, p.products_price, p.products_virtual, p.products_weight,
                                      p.products_date_added, p.products_last_modified,
                                      date_format(p.products_date_available, '%Y-%m-%d') as
                                      products_date_available, p.products_status, p.products_tax_class_id,
                                      p.manufacturers_id,
                                      p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute,
                                      p.product_is_free, p.product_is_call, p.products_quantity_mixed,
                                      p.product_is_always_free_shipping, p.products_qty_box_status, p.products_quantity_order_max,
                                      p.products_sort_order,
                                      p.products_discount_type, p.products_discount_type_from,
                                      p.products_price_sorter, p.master_categories_id
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                              where p.products_id = '" . (int)$_GET['pID'] . "'
                              and p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

      $pInfo->objectInfo($product->fields);
    } elseif (zen_not_null($_POST)) {
      $pInfo->objectInfo($_POST);
      $products_name = $_POST['products_name'];
      $products_description = $_POST['products_description'];
      $products_url = $_POST['products_url'];
    }


if (isset($_SESSION['provider_id']))
{
	$manufacturers = $db->Execute("select manufacturers_id, manufacturers_name
									   from " . TABLE_MANUFACTURERS . " where manufacturers_id = '". $_SESSION['provider_id'] ."' order by manufacturers_name");

	$manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'],
								   'text' => $manufacturers->fields['manufacturers_name']);
}
else
{
	$manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
	$manufacturers = $db->Execute("select manufacturers_id, manufacturers_name
	                                   from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
	while (!$manufacturers->EOF) {
		$manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'],
		                               'text' => $manufacturers->fields['manufacturers_name']);
		$manufacturers->MoveNext();
	}
}

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class = $db->Execute("select tax_class_id, tax_class_title
                                     from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while (!$tax_class->EOF) {
      $tax_class_array[] = array('id' => $tax_class->fields['tax_class_id'],
                                 'text' => $tax_class->fields['tax_class_title']);
      $tax_class->MoveNext();
    }

    $languages = zen_get_languages();

    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
    switch ($pInfo->products_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
        break;
    }
// set to out of stock if categories_status is off and new product or existing products_status is off
    if (zen_get_categories_status($current_category_id) == '0' and $pInfo->products_status != '1') {
      $pInfo->products_status = 0;
      $in_status = false;
      $out_status = true;
    }

// Virtual Products
    if (!isset($pInfo->products_virtual)) $pInfo->products_virtual = PRODUCTS_VIRTUAL_DEFAULT;
    switch ($pInfo->products_virtual) {
      case '0': $is_virtual = false; $not_virtual = true; break;
      case '1': $is_virtual = true; $not_virtual = false; break;
      default: $is_virtual = false; $not_virtual = true;
    }
// Always Free Shipping
    if (!isset($pInfo->product_is_always_free_shipping)) $pInfo->product_is_always_free_shipping = DEFAULT_PRODUCT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING;
    switch ($pInfo->product_is_always_free_shipping) {
      case '0': $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = true; $special_product_is_always_free_shipping = false; break;
      case '1': $is_product_is_always_free_shipping = true; $not_product_is_always_free_shipping = false; $special_product_is_always_free_shipping = false; break;
      case '2': $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = false; $special_product_is_always_free_shipping = true; break;
      default: $is_product_is_always_free_shipping = false; $not_product_is_always_free_shipping = true; $special_product_is_always_free_shipping = false;
    }
// products_qty_box_status shows
    if (!isset($pInfo->products_qty_box_status)) $pInfo->products_qty_box_status = PRODUCTS_QTY_BOX_STATUS;
    switch ($pInfo->products_qty_box_status) {
      case '0': $is_products_qty_box_status = false; $not_products_qty_box_status = true; break;
      case '1': $is_products_qty_box_status = true; $not_products_qty_box_status = false; break;
      default: $is_products_qty_box_status = true; $not_products_qty_box_status = false;
    }
// Product is Priced by Attributes
    if (!isset($pInfo->products_priced_by_attribute)) $pInfo->products_priced_by_attribute = '0';
    switch ($pInfo->products_priced_by_attribute) {
      case '0': $is_products_priced_by_attribute = false; $not_products_priced_by_attribute = true; break;
      case '1': $is_products_priced_by_attribute = true; $not_products_priced_by_attribute = false; break;
      default: $is_products_priced_by_attribute = false; $not_products_priced_by_attribute = true;
    }
// Product is Free
    if (!isset($pInfo->product_is_free)) $pInfo->product_is_free = '0';
    switch ($pInfo->product_is_free) {
      case '0': $in_product_is_free = false; $out_product_is_free = true; break;
      case '1': $in_product_is_free = true; $out_product_is_free = false; break;
      default: $in_product_is_free = false; $out_product_is_free = true;
    }
// Product is Call for price
    if (!isset($pInfo->product_is_call)) $pInfo->product_is_call = '0';
    switch ($pInfo->product_is_call) {
      case '0': $in_product_is_call = false; $out_product_is_call = true; break;
      case '1': $in_product_is_call = true; $out_product_is_call = false; break;
      default: $in_product_is_call = false; $out_product_is_call = true;
    }
// Products can be purchased with mixed attributes retail
    if (!isset($pInfo->products_quantity_mixed)) $pInfo->products_quantity_mixed = '0';
    switch ($pInfo->products_quantity_mixed) {
      case '0': $in_products_quantity_mixed = false; $out_products_quantity_mixed = true; break;
      case '1': $in_products_quantity_mixed = true; $out_products_quantity_mixed = false; break;
      default: $in_products_quantity_mixed = true; $out_products_quantity_mixed = false;
    }

// set image overwrite
  $on_overwrite = true;
  $off_overwrite = false;
// set image delete
  $on_image_delete = false;
  $off_image_delete = true;

  // images dir
  function getdirs($maindir, &$dir_info)
  {
	  $dir = @dir($maindir);
	  while ($file = $dir->read()) {
		if (is_dir($maindir . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
		  $dir_info[] = array('id' => str_replace(DIR_FS_CATALOG_IMAGES, '', $maindir) . $file . '/', 'text' => str_replace(DIR_FS_CATALOG_IMAGES, '', $maindir) . $file);
		  getdirs($maindir . $file . '/', $dir_info);
		}
	  }
	  $dir->close();
	  sort($dir_info);
  }
  
  $dir_info = array(
	array('id' => '', 'text' => 'Main Dir')
  );
  getdirs(DIR_FS_CATALOG_IMAGES, $dir_info);
  
  if (strpos($pInfo->products_image, '/') !== false) $default_directory = substr($pInfo->products_image, 0, strpos($pInfo->products_image, '/') + 1);

?>
<script language="javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;

  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;

  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//--></script>
    <?php
//  echo $type_admin_handler;
echo zen_draw_form('new_product', $type_admin_handler , 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ( (isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : ''), 'post', 'enctype="multipart/form-data"');
    ?>

<br />

<div style="float:left">
	<span class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></span>
</div>
<div style="float:right">
    <button onclick="new_product.submit()"><?php echo IMAGE_PREVIEW; ?></button>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ( (isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : '')) . '" class="button">' . IMAGE_CANCEL . '</a>'; ?>
</div>

<br style="clear:both" />
<br />

<div id="tabs">
	<ul>
		<?php $tab = 1; for ($i=0, $n=sizeof($languages); $i<$n; $i++) { ?>
		<li><a href="#tabs-<?php echo $tab; ?>"><img border="0" src="images/flags/<?php echo $languages[$i]['code']; ?>.png" alt="" /> <?php echo $languages[$i]['name']; ?></a></li>
		<?php $tab++; } ?>
		<li><a href="#tabs-<?php echo $tab++; ?>"><?php echo TAB_TITLE_PRODUCT_IMAGES; ?></a></li>
		<li><a href="#tabs-<?php echo $tab++; ?>"><?php echo TAB_TITLE_PRODUCT_OPTIONS; ?></a></li>
		<li><a href="#tabs-<?php echo $tab++; ?>"><?php echo TAB_TITLE_PRODUCT_ATTRIBUTES; ?></a></li>
		<li><a href="#tabs-<?php echo $tab++; ?>"><?php echo TAB_TITLE_FILE_MANAGER; ?></a></li>
	</ul>

	<!-- LANGUAGES -->
	<?php $tab = 1; for ($i=0, $n=sizeof($languages); $i<$n; $i++) { ?>
	<div id="tabs-<?php echo $tab; ?>">
		<table>
			<tr>
				<td align="right"><img border="0" src="images/flags/<?php echo $languages[$i]['code']; ?>.png" alt="" /></td>
				<td><?php echo $languages[$i]['name']; ?></td>
			<tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_NAME; ?></td>
				<td><?php echo zen_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? htmlspecialchars(stripslashes($products_name[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE) : htmlspecialchars(zen_get_products_name($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE)), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_name')); ?></td>
			<tr>
			<tr>
				<td valign="top" align="right"><?php echo TEXT_PRODUCTS_DESCRIPTION; ?></td>
				<td><?php
					if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
						$oFCKeditor = new FCKeditor('products_description[' . $languages[$i]['id'] . ']') ;
						$oFCKeditor->Value = (isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($pInfo->products_id, $languages[$i]['id']);
						$oFCKeditor->Width  = '99%' ;
						$oFCKeditor->Height = '350' ;
		//                $oFCKeditor->Config['ToolbarLocation'] = 'Out:xToolbar' ;
		//                $oFCKeditor->Create() ;
						$output = $oFCKeditor->CreateHtml() ;  echo $output;
				  } else { // using HTMLAREA or just raw "source"
					echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '30', (isset($products_description[$languages[$i]['id']])) ? htmlspecialchars(stripslashes($products_description[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE) : htmlspecialchars(zen_get_products_description($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'style="width: 100%"');
				  } ?>
				</td>
			<tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_URL . '<br /><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
				<td><?php echo zen_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? $products_url[$languages[$i]['id']] : zen_get_products_url($pInfo->products_id, $languages[$i]['id'])), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_url')); ?></td>
			<tr>
		</table>
	</div>
	<?php $tab++; } ?>
	<!-- /LANGUAGES -->

	<!-- IMAGES -->
	<div id="tabs-<?php echo $tab++; ?>">
		<div class="imageList">
			<table width="100%" id="imagelist" cellpadding="0" cellspacing="0" class="ui-widget-content">
				<thead class="ui-state-default">
					<tr>
						<td colspan="3" align="center"><strong><?php echo TAB_TEXT_PRODUCT_IMAGES; ?></strong></td>
					</tr>
					<tr>
						<td align="center"><?php echo TEXT_ACTIONS; ?></td>
						<td align="center"><?php echo TEXT_NAME; ?></td>
						<td align="center"><?php echo TEXT_IMAGE; ?></td>
					</tr>
					<tr>
						<td colspan="3" align="center"><a href="#" onclick="reloadImageList(true); return false"><?php echo TEXT_RELOAD_IMAGE_LIST; ?></a></td>
					</tr>
				</thead>
				<tbody>
					<?php
						$url = (strtolower(ENABLE_SSL_CATALOG) == 'true' ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_WS_IMAGES : HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES);
						$image = str_replace($default_directory, '', $pInfo->products_image);
						echo getImageList($image, $default_directory, $url);
						//if (!$default_directory) $default_directory = 'TeriH/';
					?>
				</tbody>
			</table>
		</div>
		<div class="uploadPanel">
			<table width="100%">
			    <colgroup>
					<col width="250px" />
				</colgroup>
				<tr>
					<td colspan="2" align="center"><strong><?php echo TEXT_UPLOAD_IMAGES; ?></strong></td>
				</tr>
				<tr>
					<td align="right"><?php echo TAB_TEXT_PRODUCT_DEFAULT_IMAGE; ?></td>
					<td><span style="float: left"><?php echo zen_draw_input_field('products_image_manual', $image, 'id="products_image_manual"') ?></span><?php echo ($pInfo->products_image !='' ? '<ul class="icons" style="float:left;margin-left:3px"><li class="ui-state-default ui-corner-all deleteImagePrimary" onclick="deleteImagePrimary(this); return false"><span class="ui-icon ui-icon-trash"></span></li>' : ''); ?></td>
				</tr>
				<tr>
					<td align="right"><?php echo TEXT_PRODUCTS_IMAGE_DIR; ?></td>
					<td>
						<?php echo zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'id="img_dir"'); ?>
					</td>
				</tr>
				<tr>
					<td align="right"><?php echo TAB_TEXT_UPLOAD_OVERWRITE; ?></td>
					<td><div class="radio"><?php echo zen_draw_radio_field('overwrite_img', '1', false, '', 'id="overwrite_img_yes"') . '<label for="overwrite_img_yes">' . TEXT_YES . '</label>' . zen_draw_radio_field('overwrite_img', '0', true, '', 'id="overwrite_img_no"') . '<label for="overwrite_img_no">' . TEXT_NO . '</label>'; ?></div></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<div id="swfupload-image">
							<span id="imagesUploadButton"></span>
							<ol class="upload_log"></ol>
						</div>

						<div id="image_error_list">

						</div>
					</td>
				</tr>
			</table>
		</div>
		<br style="clear:both" />
	</div>
	<!-- /IMAGES -->

	<!-- PRODUCT OPTIONS -->
	<div id="tabs-<?php echo $tab++; ?>">
		<?php
			// hidden fields not changeable on products page
			echo zen_draw_hidden_field('master_categories_id', $pInfo->master_categories_id);
			echo zen_draw_hidden_field('products_discount_type', $pInfo->products_discount_type);
			echo zen_draw_hidden_field('products_discount_type_from', $pInfo->products_discount_type_from);
			echo zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter);
		?>
		<table>
			<tr>
				<td align="right"><?php echo TEXT_MASTER_CATEGORIES_ID; ?></td>
				<td>
<?php
// show when product is linked
if (zen_get_product_is_linked($_GET['pID']) == 'true' and $_GET['pID'] > 0) {
?>
				<?php
					// echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id);
					echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
					echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($_GET['pID']), $pInfo->master_categories_id); ?>
<?php } else { ?>
				<?php echo TEXT_INFO_ID . ($_GET['pID'] > 0 ? $pInfo->master_categories_id  . ' ' . zen_get_category_name($pInfo->master_categories_id, $_SESSION['languages_id']) : $current_category_id  . ' ' . zen_get_category_name($current_category_id, $_SESSION['languages_id'])); ?>
<?php } ?>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><?php echo str_replace('<br />', '', TEXT_INFO_MASTER_CATEGORIES_ID); ?></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . ($out_status == true ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('products_status', '1', $in_status, '', 'id="products_status_yes"') . '<label for="products_status_yes">' . TEXT_PRODUCT_AVAILABLE . '</label>' . zen_draw_radio_field('products_status', '0', $out_status, '', 'id="products_status_no"') . '<label for="products_status_no">' . TEXT_PRODUCT_NOT_AVAILABLE . '</label>'; ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?><br /><small><?php echo TEXT_DATE_FORMAT; ?></small></td>
				<td><?php echo zen_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="datepicker"'); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_DATE_ADDED; ?><br /><small><?php echo TEXT_DATE_FORMAT; ?></small></td>
				<td><?php echo zen_draw_input_field('products_date_added', date('Y-m-d', strtotime($pInfo->products_date_added)), 'class="datepicker"'); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
				<td><?php echo zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCT_IS_FREE; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('product_is_free', '1', ($in_product_is_free==1), '', 'id="product_is_free_yes"') . '<label for="product_is_free_yes">' . TEXT_YES . '</label>' . zen_draw_radio_field('product_is_free', '0', ($in_product_is_free==0), '', 'id="product_is_free_no"') . '<label for="product_is_free_no">' . TEXT_NO . '</label>' . ($pInfo->product_is_free == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCT_IS_CALL; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('product_is_call', '1', ($in_product_is_call==1), '', 'id="product_is_call_yes"') . '<label for="product_is_call_yes">' . TEXT_YES . '</label>' . zen_draw_radio_field('product_is_call', '0', ($in_product_is_call==0), '', 'id="product_is_call_no"') . '<label for="product_is_call_no">' . TEXT_NO . '</label>' . ($pInfo->product_is_call == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('products_priced_by_attribute', '1', $is_products_priced_by_attribute, '', 'id="products_priced_by_attribute_yes"') . '<label for="products_priced_by_attribute_yes">' . TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE . '</label>' . zen_draw_radio_field('products_priced_by_attribute', '0', $not_products_priced_by_attribute, '', 'id="products_priced_by_attribute_no"') . '<label for="products_priced_by_attribute_no">' . TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE . '</label>' . ($pInfo->products_priced_by_attribute == 1 ? '<span class="errorText">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
				<td><?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
				<td><?php echo zen_draw_input_field('products_price', $pInfo->products_price, 'onKeyUp="updateGross()"'); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
				<td><?php echo zen_draw_input_field('products_price_gross', $pInfo->products_price, 'OnKeyUp="updateNet()"'); ?></td>
			 </tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_VIRTUAL; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('products_virtual', '1', $is_virtual, '', 'id="products_virtual_yes"') . '<label for="products_virtual_yes">' . TEXT_PRODUCT_IS_VIRTUAL . '</label>' . zen_draw_radio_field('products_virtual', '0', $not_virtual, '', 'id="products_virtual_no"') . '<label for="products_virtual_no">' . TEXT_PRODUCT_NOT_VIRTUAL . '</label>' . ($pInfo->products_virtual == 1 ? '<br /><span class="errorText">' . TEXT_VIRTUAL_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td valign="top"><?php echo TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING; ?></td>
				<td valign="top"><div class="radio"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '1', $is_product_is_always_free_shipping, '', 'id="product_is_always_free_shipping_yes"') . '<label for="product_is_always_free_shipping_yes">' . TEXT_PRODUCT_IS_ALWAYS_FREE_SHIPPING . '</label>' . zen_draw_radio_field('product_is_always_free_shipping', '0', $not_product_is_always_free_shipping, '', 'id="product_is_always_free_shipping_no"') . '<label for="product_is_always_free_shipping_no">' . TEXT_PRODUCT_NOT_ALWAYS_FREE_SHIPPING  . '</label>' . zen_draw_radio_field('product_is_always_free_shipping', '2', $special_product_is_always_free_shipping, '', 'id="product_is_always_free_shipping_special"') . '<label for="product_is_always_free_shipping_special">' . TEXT_PRODUCT_SPECIAL_ALWAYS_FREE_SHIPPING . '</label>' . ($pInfo->product_is_always_free_shipping == 1 ? '<span class="errorText">' . TEXT_FREE_SHIPPING_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_QTY_BOX_STATUS; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('products_qty_box_status', '1', $is_products_qty_box_status, '', 'id="products_qty_box_status_on"') . '<label for="products_qty_box_status_on">' . TEXT_PRODUCTS_QTY_BOX_STATUS_ON . '</label>' . zen_draw_radio_field('products_qty_box_status', '0', $not_products_qty_box_status, '', 'id="products_qty_box_status_off"') . '<label for="products_qty_box_status_off">' . TEXT_PRODUCTS_QTY_BOX_STATUS_OFF . '</label>' . ($pInfo->products_qty_box_status == 0 ? '<span class="errorText">' . TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT . '</span>' : ''); ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_QUANTITY_MIN_RETAIL; ?></td>
				<td><?php echo zen_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min == 0 ? 1 : $pInfo->products_quantity_order_min)); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL; ?></td>
				<td><?php echo zen_draw_input_field('products_quantity_order_max', $pInfo->products_quantity_order_max); ?>&nbsp;&nbsp;<?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL; ?></td>
				<td><?php echo zen_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units == 0 ? 1 : $pInfo->products_quantity_order_units)); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_MIXED; ?></td>
				<td><div class="radio"><?php echo zen_draw_radio_field('products_quantity_mixed', '1', $in_products_quantity_mixed, '', 'id="products_quantity_mixed_yes"') . '<label for="products_quantity_mixed_yes">' . TEXT_YES . '</label>' . zen_draw_radio_field('products_quantity_mixed', '0', $out_products_quantity_mixed, '', 'id="products_quantity_mixed_no"') . '<label for="products_quantity_mixed_no">' . TEXT_NO . '</label>'; ?></div></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
				<td><?php echo zen_draw_input_field('products_quantity', $pInfo->products_quantity); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
				<td><?php echo zen_draw_input_field('products_model', htmlspecialchars(stripslashes($pInfo->products_model), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS, 'products_model')); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
				<td><?php echo zen_draw_input_field('products_weight', $pInfo->products_weight); ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo TEXT_PRODUCTS_SORT_ORDER; ?></td>
				<td><?php echo zen_draw_input_field('products_sort_order', $pInfo->products_sort_order); ?></td>
			</tr>
		</table>
	</div>
	<!-- /PRODUCT OPTIONS -->

	<!-- ATTRIBUTES -->
	<div id="tabs-<?php echo $tab++; ?>">
		<div class="attributes">
		<?php if ($_GET['pID']) { ?>
			<form id="attributes" name="attributes">
			<table width="100%" id="newattribute" cellpadding="0" cellspacing="0" class="ui-widget-content">
				<colgroup>
					<col width="20%" />
					<col width="20%" />
				</colgroup>
				<thead class="ui-state-default">
					<tr>
						<td colspan="7" align="center"><?php echo TEXT_NEW_ATTRIBUTE; ?></td>
					</tr>
					<tr>
						<td align="center"><?php echo TABLE_HEADING_OPT_NAME; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_VALUE; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_SORT_ORDER; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_MAX_DAYS; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_MAX_COUNT; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_FILE; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_ACTION; ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="ui-widget-content" align="center" valign="top">
							<select name="options_id" id="OptionName" onChange="update_option(this.form)" size="<?php echo ($action != 'delete_attribute' ? "15" : "1"); ?>">
							<?php
								$options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . "
																where language_id = '" . $_SESSION['languages_id'] . "'
																order by products_options_sort_order, products_options_name");

								while (!$options_values->EOF) {
								  echo '              <option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '&nbsp;&nbsp;&nbsp;[' . translate_type_to_name($options_values->fields['products_options_type']) . '] &nbsp; [ #' . $options_values->fields['products_options_id'] . ' ] </option>' . "\n";
								  $options_values->MoveNext();
								}
							?>
							</select>
						</td>
						<td class="ui-widget-content" align="center" valign="top">
							<select name="values_id[]" id="OptionValue" multiple="multiple" size="15">

							</select>
						</td>
						<td class="ui-widget-content" align="center" valign="top"><?php echo zen_draw_input_field('products_options_sort_order', '0', 'style="text-align: center" size="3"'); ?></td>
						<td class="ui-widget-content" align="center" valign="top"><?php echo zen_draw_input_field('products_attributes_maxdays', DOWNLOAD_MAX_DAYS, 'style="text-align: center" size="3"'); ?></td>
						<td class="ui-widget-content" align="center" valign="top"><?php echo zen_draw_input_field('products_attributes_maxcount', DOWNLOAD_MAX_COUNT, 'style="text-align: center" size="3"'); ?></td>
						<td class="ui-widget-content" align="center" valign="top">
							<?php echo zen_draw_hidden_field('file_selected', '', 'id="file_selected"'); ?>
							<div class="fileTree"></div>
						</td>
						<td class="ui-widget-content" align="center" valign="top">
							<button id="addAttribute" onclick="return false"><?php echo TEXT_ADD; ?></button><br /><br />
							<button onclick="reloadFileTree(); return false;"><?php echo TEXT_RELOAD; ?></button>						
						</td>
					</tr>
				</tbody>
			</table>
			</form>
			<br />

			<table width="100%" id="attributelist" cellpadding="0" cellspacing="0" class="ui-widget-content">
				<colgroup>
					<col width="2%" />
				</colgroup>
				<thead class="ui-state-default">
					<tr>
						<td colspan="9" align="center"><?php echo TEXT_ATTRIBUTES_LIST; ?></td>
					</tr>
					<tr>
						<td><?php echo TABLE_HEADING_ID; ?></td>
						<td><?php echo TABLE_HEADING_OPT_NAME; ?></td>
						<td><?php echo TABLE_HEADING_OPT_VALUE; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_SORT_ORDER; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_MAX_DAYS; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_MAX_COUNT; ?></td>
						<td><?php echo TABLE_HEADING_OPT_FILE; ?></td>
						<td align="center"><?php echo TABLE_HEADING_VALID; ?></td>
						<td align="center"><?php echo TABLE_HEADING_OPT_ACTION; ?></td>
					</tr>
					<tr>
						<td colspan="9" align="center"><a href="#" onclick="reloadAttributeList(); return false;"><?php echo TEXT_RELOAD_ATTRIBUTES_LIST; ?></a> | <a href="#" onclick="deleteAllAttributes(); return false"><?php echo TEXT_DELETE_ALL_ATTRIBUTES; ?></a></td>
					</tr>
				</thead>
				<tbody>
					<?php echo getAttributesList((int)$_GET['pID']); ?>
				</tbody>
			</table>
		<?php } else { ?>
			<?php echo TEXT_SAVE_PRODUCT_FIRST; ?>
		<?php } ?>
		</div>
		<br style="clear:both" />
	</div>
	<!-- /ATTRIBUTES -->

	<!-- FILE MANAGER -->
	<div id="tabs-<?php echo $tab++; ?>">
		<div class="filemanager">
			<div class="alert" title="Alert">

			</div>

			<div class="fm_delete" title="Delete">
				<?php echo TEXT_DELETE_ITEMS; ?><br />
					<div style="overflow:auto; max-height: 300px; margin: 10px;">
						<ul id="todelete">

						</ul>
					</div>
					<br />
				<strong><?php echo TEXT_WARNING_UNDONE; ?></strong>
			</div>

			<div class="fm_move" title="Move">
				<strong>Items to Move</strong>
					<div style="overflow:auto; max-height: 180px; margin: 10px;">
						<ul id="tomove">

						</ul>
					</div>


				<strong>Move items from: <span id="movedirfrom">/</span></strong><br /><br />
				<strong>Move items to: <span id="movedirto">/</span></strong><br /><br />
					<table width="100%" id="dirlist" cellpadding="0" cellspacing="0" class="ui-widget-content">
					<thead class="ui-state-default">
						<tr>
							<td align="center">Dir List</td>
						</tr>
					</thead>
					<tbody>
							<tr class="noFile">
								<td class="ui-widget-content">Loading...</td>
							</tr>
					</tbody>
				</table>
					<br />
				<strong>Warning: This cannot be undone.</strong>
			</div>

			<div class="fm_confirm_cancel" title="Cancel All Uploads">
				Doing this will cancel all current uploads.<br />
				Are you sure that you want to proceed?<br /><br />
				<em>Clicking 'Yes', might freeze your browser for some seconds until the task is complete. Please, be patient.<em>
			</div>

			<div class="fm_rename" title="Rename">
				<table>
					<tr>
						<td align="right">Rename:</td>
						<td><span id="torename"></span></td>
					</tr>
					<tr>
						<td align="right">To:</td>
						<td><input type="text" id="newname" value="" size="50" /></td>
					</tr>
				</table>
			</div>

			<div class="fm_newdir" title="New Folder">
				<table>
					<tr>
						<td align="right">Create New Folder at:</td>
						<td><span class="currentdir"><?php echo $currentDir; ?></span></td>
					</tr>
					<tr>
						<td align="right">New Folder Name:</td>
						<td><input type="text" id="dirname" value="" size="50" /></td>
					</tr>
				</table>
			</div>

			<button id="fm_newdir" onclick="return false">New Folder</button> <button id="fm_upload" onclick="return false">Upload</button> <button id="fm_delete" disabled="disabled" onclick="return false">Delete</button> <button id="fm_move" disabled="disabled" onclick="return false">Move</button> <button id="fm_rename" disabled="disabled" onclick="return false">Rename</button> <button onclick="reloadFileManager(); return false">Reload</button>
			&nbsp;&nbsp;&nbsp;&nbsp;Current Folder: <input type="text" size="48" id="currentdir" value="<?php echo $currentDir; ?>" /> <input type="hidden" size="50" id="loadeddir" value="<?php echo $currentDir; ?>" /> <button id="fm_go" onclick="return false">Go</button><br /><br />

			<div class="fm_upload" title="Upload">
				<fieldset>
					<legend>Upload</legend>
					<table width="100%">
						<colgroup>
							<col width="25%" />
							<col width="75%" />
						</colgroup>
						<tr>
							<td align="right">Upload Files to:</td>
							<td><span class="currentdir"><?php echo $currentDir; ?></span></td>
						</tr>
						<tr>
							<td align="right">Overwrite if exist?</td>
							<td><div class="radio"><?php echo zen_draw_radio_field('overwrite_fm', '1', false, '', 'id="overwrite_fm_yes"') . '<label for="overwrite_fm_yes">' . TEXT_YES . '</label>' . zen_draw_radio_field('overwrite_fm', '0', true, '', 'id="overwrite_fm_no"') . '<label for="overwrite_fm_no">' . TEXT_NO . '</label>'; ?></div></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<span id="fmUploadButton"></span><div style="display: inline; margin-left: 68px"><button onclick="return false" id="cancel_fm_uploads" disabled="disabled">Cancel All Uploads</button></div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<fieldset>
									<legend>Upload Queue</legend>
										<div style="max-height: 500px; overflow: auto;">
											<div id="swfupload-filemanager">
												<ol class="upload_log"></ol>
											</div>

											<div id="fm_error_list">

											</div>
										</div>
								</fieldset>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="text-align: center">
								<br /><button onclick="return false" id="uploadClose">Close</button>
							</td>
						</tr>
					</table>
				</fieldset>
				<br />
			</div>

			<table width="100%" id="filemanager" cellpadding="0" cellspacing="0" class="ui-widget-content">
				<colgroup>
					<col width="10%" />
					<col width="50%" />
				</colgroup>
				<thead class="ui-state-default">
					<tr>
						<td colspan="7" align="center">File List</td>
					</tr>
					<tr>
						<td align="center"><input type="checkbox" onclick="toggleChecked(this.checked)" id="checkUncheckAll" /></td>
						<td>Name</td>
						<td>Size</td>
						<td>Type</td>
					</tr>
				</thead>
				<tbody>
						<?php
							$files = fileManagerFiles();
							echo $files[1];
						?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /FILE MANAGER -->
</div>
	<br />
	<div style="float:right">
        <button onclick="new_product.submit()"><?php echo IMAGE_PREVIEW; ?></button>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ( (isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : '')) . '" class="button">' . IMAGE_CANCEL . '</a>'; ?>
	</div>
	<script language="javascript">updateGross();</script>
</form>
