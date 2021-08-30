<?php
/**
 * @copyright Custom Scriptz
 * http://customscriptz.com | diego@customscriptz.com
 * Cannot be sold or freely distributed
 */

    require('includes/application_top.php');

    unset($module); // linkpoint conflict
    $module['id'] = 'aio_productpage';
    $module['name'] = 'AIO';
    $module['version'] = '1.5';
    $module['release_date'] = '2012-06-11';
    $module['secret_key'] = 'VkK47WM9eMNmse7DyZ8M8ry5TB34traq5ZteDxungd6gGU7AGc';
    $module['method'] = 'whmcs';
    $module['main_page'] = 'categories.php';

    if (!$_GET['action']) {
        zen_redirect(zen_href_link(FILENAME_CATEGORIES));
    }

/*************************************/
/****** Install Procedure - START ******/
/*************************************/
    $customScriptz->customScriptz($module);

    /* Run update routine */
    if ($_GET['action'] == 'updateDB') {
        if (file_exists('aio_product2.php')) {
            if (@rename('.htaccess', 'backup.htaccess')) {
                @rename('aio.htaccess', '.htaccess');
            }
            if (@rename('includes/.htaccess', 'includes/backup.htaccess')) {
                @rename('includes/aio.htaccess', 'includes/.htaccess');
            }
            if (@rename('product.php', 'product.bak.php')) {
                @rename('aio_product.php', 'product.php');
            }
            if (@rename('includes/modules/product', 'includes/modules/product_bak')) {
                @rename('includes/modules/aio_product', 'includes/modules/product');
            }
            if (@rename('includes/languages/english/product.php', 'includes/languages/english/product.bak.php')) {
                @rename('includes/languages/english/aio_product.php', 'includes/languages/english/product.php');
            }
        }
    }

    $customScriptz->validateLicense();
    if ($_SESSION['validation_error']) {
        $customScriptz->redirect('action=installModule');
    }

/*************************************/
/****** Install Procedure - END ******/
/*************************************/

    require(DIR_WS_MODULES . 'prod_cat_header_code.php');

    $action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
    switch ($action) {
        case 'setflag':
            if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
                if (isset($_GET['pID'])) {
                    zen_set_product_status($_GET['pID'], $_GET['flag']);
                }
            }

            zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
            break;

        case 'delete_product_confirm':
            $delete_linked = 'true';
            if ($_POST['delete_linked'] == 'delete_linked_no') {
                $delete_linked = 'false';
            } else {
                $delete_linked = 'true';
            }
            $product_type = zen_get_products_type($_POST['products_id']);
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/delete_product_confirm.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/delete_product_confirm.php');
            } else {
                require(DIR_WS_MODULES . 'delete_product_confirm.php');
            }
            break;
        case 'move_product_confirm':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/move_product_confirm.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/move_product_confirm.php');
            } else {
                require(DIR_WS_MODULES . 'move_product_confirm.php');
            }
            break;
        case 'insert_product_meta_tags':
        case 'update_product_meta_tags':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product_meta_tags.php');
            } else {
                require(DIR_WS_MODULES . 'update_product_meta_tags.php');
            }
            break;
        case 'insert_product':
        case 'update_product':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/update_product.php');
            } else {
                require(DIR_WS_MODULES . 'update_product.php');
            }
            break;
        case 'copy_to_confirm':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/copy_to_confirm.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/copy_to_confirm.php');
            } else {
                require(DIR_WS_MODULES . 'copy_to_confirm.php');
            }
            break;
        case 'new_product_preview_meta_tags':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview_meta_tags.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview_meta_tags.php');
            } else {
                require(DIR_WS_MODULES . 'new_product_preview_meta_tags.php');
            }
            break;
        case 'new_product_preview':
            if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php')) {
                require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/new_product_preview.php');
            } else {
                require(DIR_WS_MODULES . 'new_product_preview.php');
            }
            break;
    }
}

    $products_options_types_list = array();
    $products_options_type_array = $db->Execute("select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " order by products_options_types_id");
while (!$products_options_type_array->EOF) {
    $products_options_types_list[$products_options_type_array->fields['products_options_types_id']] = $products_options_type_array->fields['products_options_types_name'];
    $products_options_type_array->MoveNext();
}

function draw_optiontype_pulldown($name, $default = '')
{
    global $products_options_types_list;
    $values = array();
    foreach ($products_options_types_list as $id => $text) {
        $values[] = array('id' => $id, 'text' => $text);
    }
    return zen_draw_pull_down_menu($name, $values, $default);
}

function translate_type_to_name($opt_type)
{
    global $products_options_types_list;
    return $products_options_types_list[$opt_type];
}

    // check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
        $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
    }
} else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}

function zen_js_option_values_list($selectedName, $fieldName)
{
    global $db, $show_value_numbers;
    $attributes_sql = "SELECT povpo.products_options_id, povpo.products_options_values_id, po.products_options_name, po.products_options_sort_order,
                       pov.products_options_values_name, pov.products_options_values_sort_order
                       FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povpo, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                       WHERE povpo.products_options_id = po.products_options_id
                       AND povpo.products_options_values_id = pov.products_options_values_id
                       AND pov.language_id = po.language_id
                       AND po.language_id = " . $_SESSION['languages_id'] . "
                       ORDER BY po.products_options_id, po.products_options_name, pov.products_options_values_name";
    // "
    // ORDER BY po.products_options_name, pov.products_options_values_sort_order";
    $attributes = $db->Execute($attributes_sql);

    $counter = 1;
    $val_count = 0;
    $value_string = '  // Build conditional Option Values Lists' . "\n";
    $last_option_processed = null;
    while (!$attributes->EOF) {
        $products_options_values_name = str_replace('-', '\-', $attributes->fields['products_options_values_name']);
        $products_options_values_name = str_replace('(', '\(', $products_options_values_name);
        $products_options_values_name = str_replace(')', '\)', $products_options_values_name);
        $products_options_values_name = str_replace('"', '\"', $products_options_values_name);
        $products_options_values_name = str_replace('&quot;', '\"', $products_options_values_name);
        $products_options_values_name = str_replace('&frac12;', '1/2', $products_options_values_name);

        if ($counter == 1) {
            $value_string .= '  if (' . $selectedName . ' == "' . $attributes->fields['products_options_id'] . '") {' . "\n";
            $value_string .= '  	$(\'#OptionValue\').html(\'';
        } elseif ($last_option_processed != $attributes->fields['products_options_id']) {
            $value_string .= '\'); ' . "\n  " . '} else if (' . $selectedName . ' == "' . $attributes->fields['products_options_id'] . '") {' . "\n\t$('#OptionValue').html('";
            $val_count = 0;
        }
        $value_string .= '<option value="' . $attributes->fields['products_options_values_id'] . '">' . addslashes($products_options_values_name) . ($attributes->fields['products_options_values_id'] == 0 ? '/UPLOAD FILE' : '') . ' [ #' . $attributes->fields['products_options_values_id'] . ' ]</option>';

        $last_option_processed = $attributes->fields['products_options_id'];
        $val_count++;
        $counter++;
        $attributes->MoveNext();
    }
    if ($counter > 1) {
        $value_string .= '\');' . "\n" . '  }' . "\n";
    }
    return $value_string;
}
