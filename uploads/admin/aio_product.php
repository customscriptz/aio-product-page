<?php
/**
 * @copyright Custom Scriptz
 * http://customscriptz.com | diego@customscriptz.com
 * Cannot be sold or freely distributed
 */

include('aio_product_functions.php');

$time = time();
if (file_exists('aio.htaccess'))
{
	@rename('.htaccess', '.htaccess.' . $time);
	@rename('aio.htaccess', '.htaccess');
}

if (file_exists('includes/aio.htaccess'))
{
	@rename('includes/.htaccess', 'includes/.htaccess.' . $time);
	@rename('includes/aio.htaccess', 'includes/.htaccess');
}

if (file_exists('includes/aio.htaccess'))
{
	@rename('includes/.htaccess', 'includes/.htaccess.' . $time);
	@rename('includes/aio.htaccess', 'includes/.htaccess');
}

if (file_exists('includes/modules/aio_product'))
{
	@rename('includes/modules/product', 'includes/modules/product_' . $time);
	@rename('includes/modules/aio_product', 'includes/modules/product');
}

$languages = zen_get_languages();
for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
	if (file_exists('includes/languages/' . $languages[$i]['directory'] . '/aio_product.php'))
	{
		@rename('includes/languages/' . $languages[$i]['directory'] . '/product.php', 'includes/languages/' . $languages[$i]['directory'] . '/product.php' . $time);
		@rename('includes/languages/' . $languages[$i]['directory'] . '/aio_product.php', 'includes/languages/' . $languages[$i]['directory'] . '/product.php');
	}
}
		
$currentDir = '/';

if (defined('PROVIDER_DIR'))
{
	$provider = $db->Execute("SELECT manufacturers_dir FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = " . $_SESSION['provider_id']);
	$currentDir .= $provider->fields['manufacturers_dir'] . '/';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="includes/css/style.css">
<link rel="stylesheet" type="text/css" href="includes/css/base.css">
<link rel="stylesheet" type="text/css" href="includes/javascript/jqueryFileTree/jqueryFileTree.css">
<script type="text/javascript" src="includes/javascript/jquery/jquery.js"></script>
<script type="text/javascript" src="includes/javascript/jquery/jquery-ui.js"></script>
<script type="text/javascript" src="includes/javascript/jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="includes/javascript/swfupload/swfupload.js"></script>
<script type="text/javascript" src="includes/javascript/swfupload/jquery.swfupload.js"></script>
<script type="text/javascript" src="includes/javascript/jqueryFileTree/jqueryFileTree.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.4.1.js"></script>
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
if (typeof _editor_url == "string") HTMLArea.replaceAll();
 }
 // -->
</script>
<style type="text/css">
</style>
<script type="text/javascript">
<?php if (defined('PROVIDER_DIR')) { ?>
var url = '<?php echo (ENABLE_SSL_ADMIN == 'true' ? HTTPS_SERVER . DIR_WS_HTTPS_PROVIDER : HTTP_SERVER . DIR_WS_PROVIDER)?>aio_product_page.php';
<?php } else { ?>
var url = '<?php echo (ENABLE_SSL_ADMIN == 'true' ? HTTPS_SERVER . DIR_WS_HTTPS_ADMIN : HTTP_SERVER . DIR_WS_ADMIN)?>aio_product_page.php';
<?php } ?>
<?php if (defined('PROVIDER_DIR')) { ?>
var downloadDir = '<?php echo DIR_FS_DOWNLOAD . $provider->fields['manufacturers_dir']; ?>';
var providerDir = '<?php echo $provider->fields['manufacturers_dir']; ?>';
<?php } else { ?>
var downloadDir = '<?php echo DIR_FS_DOWNLOAD?>';
var providerDir = '';
<?php } ?>

function toggleChecked(status) {
	$('.checkbox').each(function() {
		$(this).attr('checked', status);
	});
}

function reloadAttributeList(block) {
	if (typeof(block) == 'undefined') {
		$('.attributes').block();
	}

	$.ajax({
		url: url + '?a=reload_attribute_list&pID=<?php echo $_GET['pID']; ?>',
		dataType: 'json',
		success: function(result) {
			$('.attributes').unblock();

			if (result.status == 'success')
			{
				$('#attributelist tbody').fadeOut(function() {
					$(this).html(result.html).fadeIn();
				});
			}
		}
	});
}

function deleteImage(filename, btn, imgnumber) {
	var question = confirm('<?php echo sprintf(TAB_TEXT_DELETE_IMAGE_WARNING, "' + filename + '"); ?>');
	if (question)
	{
		$(btn).fadeOut();

		var tr = $(btn).closest('tr');
		$(tr).fadeTo('normal', 0.5, function() {
			$.ajax({
				url: url + '?a=deleteimage',
				type: 'POST',
				data: ({ PATH: '<?php echo DIR_FS_CATALOG_IMAGES; ?>', IMG_DIR: $('#img_dir').val(), FILENAME: filename }),
				success: function()
				{
					$(tr).fadeOut(function() {
						$(this).remove();
						reloadImageList(false);

						if (imgnumber == 1) {
							$('.deleteImagePrimary').fadeOut();

							$('#products_image_manual').val('');
							$('#imagename').html('<?php echo NONE; ?>');

							//$('input[name=img_dir]').attr('id', 'img_dir_hidden').attr('name', 'img_dir_hidden');
							//$('select[name=img_dir_select]').attr('id', 'img_dir').attr('name', 'img_dir').delay(750).fadeIn();
						}
					});
				}
			});
		});
	}
}

function deleteImagePrimary(btn) {
	var question = confirm('<?php echo TAB_TEXT_DELETE_PRIMARY_IMAGE_WARNING; ?>');
	if (question)
	{
		$('.deleteImagePrimary').fadeOut();

		$.ajax({
			url: url + '?a=deleteimage',
			type: 'POST',
			data: ({ PATH: '<?php echo DIR_FS_CATALOG_IMAGES; ?>', IMG_DIR: $('#img_dir').val(), FILENAME: $('#products_image_manual').val() }),
			success: function()
			{
				$('#products_image_manual').val('');
				$('#imagename').html('<?php echo NONE; ?>');
				/*$('.img_dir').fadeOut('fast', function() {
					$(this).html('').show();
				});

				$('input[name=img_dir]').attr('id', 'img_dir_hidden').attr('name', 'img_dir_hidden');
				$('select[name=img_dir_select]').attr('id', 'img_dir').attr('name', 'img_dir').delay(1000).fadeIn();*/
				reloadImageList(false);
			}
		});
	}
}

function reloadImageList(animate) {
	$.ajax({
		url: url + '?a=imagelist',
		type: 'POST',
		data: ({ IMG_DIR: $('#img_dir').val(), FILENAME: $('#products_image_manual').val(), IMG_URL: '<?php echo (strtolower(ENABLE_SSL_CATALOG) == 'true' ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_WS_IMAGES : HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES); ?>'}),
		dataType: 'html',
		success: function(html)
		{
			if (animate) {
				$('#imagelist tbody').fadeOut('fast', function() {
					$('#imagelist tbody').html(html).fadeIn('fast').removeAttr('style');
				}).removeAttr('style');
			}
			else
				$('#imagelist tbody').html(html).removeAttr('style').removeAttr('style');
		}
	});
}

function update_option() {
    // enable hourglass
    document.body.style.cursor = "wait";

    // set initial values
	var SelectedOption = $('#OptionName').val();

<?php echo zen_js_option_values_list('SelectedOption', 'theField'); ?>

	$('#OptionValue').val($("#OptionValue option:first").val());

    // turn off hourglass
    document.body.style.cursor = "default";
}

function validateNewAttribute(obj) {
	var OptionName = $('#OptionName').val();
	var OptionValue = '';

	$("#OptionValue option:selected").each(function () {
		OptionValue += $(this).val() + '-';
    });

	if (OptionValue) OptionValue = OptionValue.substr(OptionValue, OptionValue.length - 1);

	var Order = $('input[name=products_options_sort_order]').val();
	var ExpiryDays = $('input[name=products_attributes_maxdays]').val();
	var DownloadCount = $('input[name=products_attributes_maxcount]').val();
	var File = $('input[name=file_selected]').val();

	if (OptionName && OptionValue && Order && ExpiryDays && DownloadCount<?php if (DOWNLOAD_ENABLED == 'true') { ?> && File<?php } ?>) {
		$('#addAttribute').button('enable');
	} else {
		$('#addAttribute').button('disable');
	}

	$('button, a.button, input[type=submit]').button('refresh');
}

function deleteAllAttributes() {
	var question = confirm("Are you sure that you want to delete ALL attributes?\nWARNING: This cannot be undone");
	if (question) {
		$('.attributes').block();
		$.ajax({
			url: url + '?a=delete_allattributes&pID=<?php echo $_GET['pID']; ?>',
			success: function(result) {
				reloadAttributeList(false);
			}
		});
	}
}

$(function(){
	$.extend($.fn.disableTextSelect = function() {
		return this.each(function(){
			if($.browser.mozilla){//Firefox
				$(this).css('MozUserSelect','none');
			}else if($.browser.msie){//IE
				$(this).bind('selectstart',function(){return false;});
			}else{//Opera, etc.
				$(this).mousedown(function(){return false;});
			}
		});
	});
	$('.noSelect').disableTextSelect();//No text selection on elements with a class of 'noSelect'
});

function reloadFileManager(dir) {
	manageLoading();

	var dirpath = $('#currentdir').val();
	if (typeof dir == 'undefined') dir = '/';
	if (typeof dirpath != 'undefined') dir = dirpath;

	$.ajax({
		url: url + '?a=reload_filemanager&dir=' + dir,
		dataType: 'json',
		success: function(result) {
			manageLoading(result);
			reloadFileTree();
		}
	});
}

function reloadDirList(dir) {
	$('#dirlist tbody').html('<tr class="noFile"><td class="ui-widget-content">Loading...</td></tr>');

	var tomove = '';

	$('#tomove li').each(function() {
		if ($(this).html() != '') tomove += $(this).html() + '|';
	});

	$.ajax({
		url: url + '?a=reload_dirlist&dir=' + dir + '&items=' + tomove,
		dataType: 'json',
		success: function(result) {
			if (result.status == 'error') {
				showAlert(result.message);
			} else {
				$('#movedirto').html(result.dir);
				$('#dirlist tbody').html(result.html);
			}
		}
	});
}

function moveItems(moveFrom, moveTo)
{
	manageLoading();

	var items = '';
	$('#tomove li').each(function() {
		if ($(this).html() != '') items += $(this).html() + '|';
	});

	$.ajax({
		url: url + '?a=move_items&from=' + moveFrom + '&to=' + moveTo + '&items=' + items,
		dataType: 'json',
		success: function(result) {
			if (result.status == 'error') {
				showAlert(result.message);
			}
			reloadFileManager();
		}
	});
}

function showAlert(msg) {
	$('.alert').html(msg).dialog('open');
}

function createDir() {
	var newdir = $('#dirname').val().trim();
	var currentdir = $('#loadeddir').val().trim();

	if (newdir != '')
	{
		$('.fm_newdir').dialog('close');
		$('#dirname').val('');

		manageLoading();

		$.ajax({
			url: url + '?a=create_dir&newdir=' + newdir + '&currentdir=' + currentdir,
			dataType: 'json',
			success: function(result) {
				manageLoading(result);
				reloadFileTree();
			}
		});
	}
	else
	{
		showAlert('You must enter a dir name!');
	}
}

function manageLoading(result) {
	if (typeof result == 'undefined')
	{
		$('#fm_delete, #fm_move, #fm_rename, #fm_newdir, #fm_upload').button('disable');
		$('button, a.button, input[type=submit]').button('refresh');
		$('#filemanager tbody').html('<tr class="noFile"><td class="ui-widget-content">&nbsp;</td><td colspan="3" class="ui-widget-content">Loading...</td></tr>');
	}
	else
	{
		if (result.status == 'error') showAlert(result.message);
		if (result.dir) $('#currentdir, #loadeddir').val(result.dir);
		if (result.dir) $('.currentdir').html(result.dir);

		$('#checkUncheckAll').removeAttr('checked');
		$('#filemanager tbody').html(result.html);
		$('#fm_newdir, #fm_upload').button('enable');
		$('.noSelect').disableTextSelect();
		$('button, a.button, input[type=submit]').button('refresh');
	}
}

function deleteItem() {
	var currentDir = $('#loadeddir').val().trim();
	var selectedItem = '';
	$('#todelete li').each(function() {
		if ($(this).html() != '') selectedItem += $(this).html() + '|';
	});

	if (selectedItem != '')
	{
		$('.fm_delete').dialog('close');
		manageLoading();

		$.ajax({
			url: url + '?a=delete_item&item=' + selectedItem + '&currentdir=' + currentDir,
			dataType: 'json',
			success: function(result) {
				manageLoading(result);
				reloadFileTree();
			}
		});
	}
}

function renameItem() {
	var selectedItem = $('#torename').html();
	var newName = $('#newname').val().trim();
	var currentDir = $('#loadeddir').val().trim();

	if (newName == '')
	{
		showAlert('You must enter a new name.');
	}
	else  if (selectedItem == newName)
	{
		showAlert('You must enter a different name.');
	}
	else  if (selectedItem != '' && newName != '')
	{
		$('.fm_rename').dialog('close');
		manageLoading();

		$.ajax({
			url: url + '?a=rename_item&item=' + selectedItem + '&newname=' + newName + '&currentdir=' + currentDir,
			dataType: 'json',
			success: function(result) {
				manageLoading(result);
				reloadFileTree();
			}
		});
	}
}

function reloadFileTree() {
	$('.fileTree').html('Loading...');
	$('.fileTree').fileTree({ root: downloadDir, script: url + '?a=filetree' }, function(file) {
		$('#file_selected').val(file);
	});
}

$.fn.selectRange = function(start, end) {
    return this.each(function() {
        if(this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if(this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

$(function() {
	$('button').live('click', function() {
		$(this).removeClass('ui-state-focus');
	});

	$(window).bind('beforeunload', function(){
		var upload = $.swfupload.getInstance('#swfupload-filemanager');
		if (typeof upload != 'undefined')
		{
			var stats = upload.getStats();
			if (stats.files_queued > 0)	return "Doing this will cancel all current uploads.\nAre you sure that you want to proceed?";
		}
	});

	$('.alert').dialog({
		autoOpen: false,
		resizable: false,
		width: 500,
		modal: true,
		buttons: {
			"Ok": function() {
				$(this).dialog('close');
			}
		}
	});

	$('.fm_confirm_cancel').dialog({
		autoOpen: false,
		resizable: false,
		width: 500,
		modal: true,
		buttons: {
			"Yes": function() {
				var upload = $.swfupload.getInstance('#swfupload-filemanager');
				upload.stopUpload();

				$('#swfupload-filemanager .upload_log li').each(function() {
					upload.cancelUpload($(this).attr('id'));
					$(this).fadeOut(function() {
						$(this).remove();
					});
				});

				$(this).dialog('close');
			},
			"No": function() {
				$(this).dialog('close');
			}
		}
	});

	/*$('.fm_upload').dialog({
		position: 'top',
		autoOpen: false,
		resizable: false,
		maxHeight: 500,
		width: 700,
		modal: true,
		buttons: {
			"Cancel All Uploads": function() {
				$('.fm_confirm_cancel').dialog('open');
			},
			"Close": function() {
				$(this).dialog('close');
			}
		}
	});*/

	$('#cancel_fm_uploads').live('click', function() {
		var upload = $.swfupload.getInstance('#swfupload-filemanager');
		var stats = upload.getStats();
			if (stats.files_queued > 0)
			{
				$('.fm_confirm_cancel').dialog('open');
				return false;
			}
	});

	$('.fm_newdir').dialog({
		autoOpen: false,
		resizable: false,
		height: 150,
		width: 600,
		modal: true,
		buttons: {
			"Ok": function() {
				createDir();
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		}
	});

	$('.fm_delete').dialog({
		autoOpen: false,
		resizable: false,
		width: 500,
		modal: true,
		buttons: {
			"Ok": function() {
				deleteItem();
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		}
	});

	$('.fm_move').dialog({
		position: 'top',
		autoOpen: false,
		resizable: false,
		width: 500,
		modal: true,
		buttons: {
			"Ok": function() {
				if ($('#movedirfrom').html() == $('#movedirto').html())
				{
					showAlert('Sorry, you can\'t move items to the same directory.');
				}
				else
				{
					moveItems($('#movedirfrom').html(), $('#movedirto').html());
					$(this).dialog('close');
				}
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		},
		open: function() {
			$('#movedirfrom').html($('#loadeddir').val());
			$('#movedirto').html('/');
			reloadDirList('/');
		}
	});

	$('.fm_rename').dialog({
		autoOpen: false,
		resizable: false,
		height: 150,
		width: 500,
		modal: true,
		buttons: {
			"Ok": function() {
				renameItem();
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		}
	});

	$('#newname').live('focus', function() {
		var newname = $(this).val();
		var pos = newname.lastIndexOf('.');
		var length = newname.length;

		if (pos <= 0) pos = length;
		$(this).selectRange(0, pos);
	});

	$('#fm_newdir').live('click', function() {
		$('.fm_newdir').dialog('open');
	});

	$('#fm_upload').live('click', function() {
		$('#fm_error_list').html('');
		$('.fm_upload').dialog('open');
	});

	$('#fm_delete').live('click', function() {
		$('.fm_delete').dialog('open');
	});

	$('#fm_move').live('click', function() {
		$('.fm_move').dialog('open');
	});

	$('#fm_rename').live('click', function() {
		$('.fm_rename').dialog('open');
	});

	$('#filemanager input[type=checkbox]').live('click', function() {
		$('#fm_delete, #fm_move').button('disable');

		var checked = '';
		$('#filemanager tbody input[type=checkbox]:checked').each(function() {
			if ($(this).val() != '') checked += '<li>' + $(this).val() + '<li>';
		});

		$('#todelete, #tomove').html(checked);

		if (checked != '')
			$('#fm_delete, #fm_move').button('enable');
	});

	$('#filemanager tbody tr').live('click', function() {
		$('#fm_delete, #fm_move, #fm_rename').button('disable');

		$('#filemanager tbody tr').each(function() {
			$(this).removeClass('selected');
		});

		if (!$(this).hasClass('noFile')) $(this).addClass('selected');

		var selectedItem = $(this).find('.itemname').val();
		if (selectedItem != '' && !$(this).hasClass('noFile'))
		{
			$('#fm_rename').button('enable');
			$('#torename').html(selectedItem);
			$('#newname').val(selectedItem);

			var checked = '';
			$('#filemanager tbody input[type=checkbox]:checked').each(function() {
				if ($(this).val() != '') checked += '<li>' + $(this).val() + '<li>';
			});
			$('#todelete, #tomove').html(checked);
			if (checked != '')
				$('#fm_delete, #fm_move').button('enable');
		}
	});

	$('#filemanager tbody tr.dir').live('dblclick', function() {
		var dirpath = $(this).find('.dirpath').val();
		$('#currentdir, #loadeddir').val(dirpath);
		$('.currentdir').html(dirpath);
		reloadFileManager(dirpath);
	});

	$('#dirlist tbody tr').live('click', function() {
		$('#dirlist tbody tr').each(function() {
			$(this).removeClass('selected');
		});

		if (!$(this).hasClass('noFile')) $(this).addClass('selected');

		var selectedItem = $(this).find('.dirpath').val();
		if (selectedItem != '' && !$(this).hasClass('noFile'))
		{
			$('#movedirto').html(selectedItem);
		}
	});

	$('#dirlist tbody tr').live('dblclick', function() {
		var dirpath = $(this).find('.dirpath').val();
		$('#movedirto').html(dirpath);
		reloadDirList(dirpath);
	});

	$('#fm_go').live('click', function() {
		var dirpath = $('#currentdir').val();
		if (dirpath == '')
		{
			dirpath = '/';
			$('#currentdir, #loadeddir').val('/');
			$('.currentdir').html(downloadDir . dirpath);
		}
		reloadFileManager(dirpath);
	});

	$('.ui-state-default').live('click', function() { $(this).removeClass('ui-state-hover') });
	$('#uploadClose').live('click', function() {
		$('.fm_upload').slideUp('slow');
	});

	$('#fm_upload').live('click', function() {
		$('.fm_upload').slideDown('slow');
	});

	$('input').keypress(function (e) {
		var k = e.keyCode || e.which;
		if (k == 13) {
			return false;
		}
	});

	$('#dirname').keypress(function (e) {
		var k = e.keyCode || e.which;
		if (k == 13) {
			createDir();
		}
	});

	$('#newname').keypress(function (e) {
		var k = e.keyCode || e.which;
		if (k == 13) {
			renameItem();
		}
	});

	$('#currentdir').keypress(function (e) {
		var k = e.keyCode || e.which;
		if (k == 13) {
			var dirpath = $('#currentdir').val();
			if (dirpath == '')
			{
				dirpath = '/';
				$('#currentdir, #loadeddir').val('/');
				$('.currentdir').html(downloadDir . dirpath);
			}
			reloadFileManager(dirpath);
		}
	});

	var selectedDir = $("#img_dir option:selected");
	$("#img_dir").click(function(){
		selectedDir = $("#img_dir option:selected");
	});

	reloadFileTree();

	$('li.ui-state-default').live('mouseenter', function(){ $(this).addClass('ui-state-hover'); }).live('mouseleave', function(){ $(this).removeClass('ui-state-hover'); });

	$('.icon-edit').live('click', function() {
		$(this).closest('tr').find('input[type=text]').removeAttr('readonly');
		var tr = '#' + $(this).closest('tr').removeClass('inactive').attr('id');
		$(tr + ' .icon-edit, ' + tr + ' .icon-delete').addClass('hidden');
		$(tr + ' .icon-save, ' + tr + ' .icon-cancel').removeClass('hidden');
	});

	$('.icon-cancel, .icon-save').live('click', function(e) {
		$(this).closest('tr').find('input[type=text]').attr('readonly', true);
		var pAttribute = $(this).closest('tr').attr('id');
		var tr = '#' + $(this).closest('tr').addClass('inactive').attr('id');
		$(tr + ' .icon-edit, ' + tr + ' .icon-delete').removeClass('hidden');
		$(tr + ' .icon-save, ' + tr + ' .icon-cancel').addClass('hidden');

		var order = $(tr + ' input[name="products_options_sort_order"]').val();
		var maxday = $(tr + ' input[name="products_attributes_maxdays"]').val();
		var maxcount = $(tr + ' input[name="products_attributes_maxcount"]').val();

		var order_previous = $(tr + ' input[name="products_options_sort_order_previous"]').val();
		var maxday_previous = $(tr + ' input[name="products_attributes_maxdays_previous"]').val();
		var maxcount_previous = $(tr + ' input[name="products_attributes_maxcount_previous"]').val();

		if ($(this).hasClass('icon-save'))
		{
			$('.attributes').block();
			$.ajax({
				url: url + '?a=save_attribute&pID=<?php echo $_GET['pID']; ?>',
				type: 'POST',
				data: ({ products_options_sort_order: order, products_attributes_maxdays: maxday, products_attributes_maxcount: maxcount, attribute_id: pAttribute }),
				success: function(result) {
					reloadAttributeList(false);
				}
			});
		}
		else
		{
			$(tr + ' input[name="products_options_sort_order"]').val(order_previous);
			$(tr + ' input[name="products_attributes_maxdays"]').val(maxday_previous);
			$(tr + ' input[name="products_attributes_maxcount"]').val(maxcount_previous);
		}
	});

	$('.icon-delete').live('click', function(e) {
		$(this).closest('tr').find('input[type=text]').attr('readonly', true);
		var pAttribute = $(this).closest('tr').attr('id');
		var question = confirm("Are you sure that you want to delete this attribute?\nWARNING: This cannot be undone");
		if (question)
		{
			$('.attributes').block();
			$.ajax({
				url: url + '?a=delete_attribute&pID=<?php echo $_GET['pID']; ?>',
				type: 'POST',
				data: ({ attribute_id: pAttribute }),
				success: function(result) {
					reloadAttributeList(false);
				}
			});
		}
	});

	$('#tabs').tabs();
	$('button, a.button, input[type=submit]').button();
	$('#fm_newdir, #fm_upload').button('enable');
	$('.radio').buttonset();
	$(".datepicker").datepicker({
		dateFormat: 'yy-mm-dd'
	});

	$('#img_dir').change(function() {
		if ($('#products_image_manual').val()) {
			var question = confirm('<?php echo TAB_TEXT_CHANGE_DIR_WARNING?>');
			if (!question)
			{
				selectedDir.attr("selected", true);
			}
		}
	});

	//Filemanager Upload
	$('#swfupload-filemanager').swfupload({
		upload_url: url + '?a=upload_file',
		post_params: {"PHPSESSID": "<?php echo session_id(); ?>", 'SECURITYTOKEN': '<?php echo $_SESSION['securityToken']?>', 'UPLOADPATH': '<?php echo substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1)?>'},

		file_types : "*.*",
		file_types_description : "All Files",
		file_upload_limit : 0,

		flash_url : "includes/javascript/swfupload/swfupload.swf",
		debug: <?php echo (isset($_GET['debug']) ? 'true' : 'false'); ?>,

		// Button settings
		button_placeholder_id : "fmUploadButton",
		button_width: 61,
		button_height: 22,
		button_image_url : "includes/javascript/swfupload/XPButtonUploadText_61x22.png",
		button_cursor: SWFUpload.CURSOR.HAND
	})
	.bind('fileQueued', function(event, file) {
		var listitem='<li id="'+file.id+'" >'+
			'<em>'+file.name+'</em> ('+Math.round(file.size/1024)+' KB) <span class="progressvalue"></span>'+
			'<div class="progress bar'+file.id+'"></div>'+
			'<p class="status"><?php echo TAB_TEXT_UPLOAD_PENDING; ?></p>'+
			'<span class="cancel">&nbsp;</span>'+
			'</li>';
		$('.upload_log').append(listitem);

		$('.bar'+file.id).progressbar();

		$('li#'+file.id+' .cancel').live('click', function(){ //Remove from queue on cancel click
			var swfu = $.swfupload.getInstance('#swfupload-filemanager');
			swfu.cancelUpload(file.id);
			$('li#'+file.id).slideUp('fast', function() {
				$(this).remove();
			});
		});

		$('#fm_error_list').html('');

		/*$('select[name=img_dir]').attr('id', 'img_dir_select').attr('name', 'img_dir_select').fadeOut();
		var selecteddir = $('select[name=img_dir_select]').val();
		$('input[name=img_dir_hidden]').attr('id', 'img_dir').attr('name', 'img_dir').val(selecteddir);
		if ($('.img_dir').html() == '') {
			$('.img_dir').fadeOut(function() {
				if (selecteddir == '') selecteddir = '<?php echo TAB_TEXT_UPLOAD_MAIN_DIRECTORY; ?>';
				$(this).html(selecteddir).fadeIn();
			});
		}*/

		// start the upload since it's queued
		$(this).swfupload('startUpload');
	})
	.bind('fileQueueError', function(event, file, errorCode, message) {
		showAlert('<?php echo sprintf(TAB_TEXT_UPLOAD_SIZE_LIMIT, "'+file.name+'"); ?>');
	})
	.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued) {
		//$('.queuestatus').text('Files Selected: <span class="selectedFiles">'+numFilesSelected+'</span> / Queued Files: <span class="queueFiles">'+numFilesQueued+'</span>');
	})
	.bind('uploadStart', function(event, file) {
		$('#cancel_fm_uploads').button('enable');

		var swfu = $.swfupload.getInstance('#swfupload-filemanager');
			swfu.addFileParam(file.id, 'overwrite', $('.fm_upload input:radio[name=overwrite_fm]:checked').val());
			swfu.addFileParam(file.id, 'path', $('#loadeddir').val().trim());

		$('.upload_log li#'+file.id).find('p.status').text('<?php echo TAB_TEXT_UPLOAD_UPLOADING; ?>');
		$('.upload_log li#'+file.id).find('span.progressvalue').text('0%');
		//$('.upload_log li#'+file.id).find('span.cancel').hide();
	})
	.bind('uploadProgress', function(event, file, bytesLoaded) {
		//Show Progress
		var percentage=Math.round((bytesLoaded/file.size)*100);
		$('.bar'+file.id).progressbar('value', percentage);
		$('.upload_log li#'+file.id).find('span.progressvalue').text(percentage+'%');
	})
	.bind('uploadSuccess', function(event, file, serverData){
		var result = $.parseJSON(serverData);

		$('.bar'+file.id).progressbar('value', 100);

		var item=$('.upload_log li#'+file.id);
		item.find('span.progressvalue').text('100%');
		item.find('p.status').html(result.message);
		item.queue(function(next) {
			$(this).delay(3000).fadeOut(function() {
				$(this).remove();
			});

			next();
		});

		if (result.status != 'success') {
			$('#fm_error_list').append('<ol>'+result.message+'</ol>');
		}

		if (result.status != 'error') {
			if ($('#products_image_manual').val() == '') $('#products_image_manual').val(result.filename);

			filename = $('#products_image_manual').val();
			$('#imagename').html(filename);
		}
	})
	.bind('uploadComplete', function(event, file){
		var swfu = $.swfupload.getInstance('#swfupload-filemanager');

		if (swfu.getStats().files_queued == 0) {
			var dirpath = $('#loadeddir').val();
			reloadFileManager(dirpath);
			$('#cancel_fm_uploads').button('disable');
		}

		$(this).swfupload('startUpload');
	});

	//Images Upload
	$('#swfupload-image').swfupload({
		upload_url: url + '?a=upload_image',
		post_params: {"PHPSESSID": "<?php echo session_id(); ?>", 'SECURITYTOKEN': '<?php echo $_SESSION['securityToken']?>', 'UPLOADPATH': '<?php echo DIR_FS_CATALOG_IMAGES?>'},

		file_types : "*.jpg;*.png;*.gif",
		file_types_description : "<?php echo TAB_TEXT_UPLOAD_IMAGE_FILES; ?>",
		file_upload_limit : 0,

		flash_url : "includes/javascript/swfupload/swfupload.swf",
		debug: <?php echo (isset($_GET['debug']) ? 'true' : 'false'); ?>,

		// Button settings
		button_placeholder_id : "imagesUploadButton",
		button_width: 61,
		button_height: 22,
		button_image_url : "includes/javascript/swfupload/XPButtonUploadText_61x22.png",
		button_cursor: SWFUpload.CURSOR.HAND
	})
	.bind('fileQueued', function(event, file) {
		var listitem='<li id="'+file.id+'" >'+
			'<em>'+file.name+'</em> ('+Math.round(file.size/1024)+' KB) <span class="progressvalue"></span>'+
			'<div class="progress bar'+file.id+'"></div>'+
			'<p class="status"><?php echo TAB_TEXT_UPLOAD_PENDING; ?></p>'+
			'<span class="cancel">&nbsp;</span>'+
			'</li>';
		$('.upload_log').append(listitem);

		$('.bar'+file.id).progressbar();

		$('li#'+file.id+' .cancel').bind('click', function(){ //Remove from queue on cancel click
			var swfu = $.swfupload.getInstance('#swfupload-image');
			swfu.cancelUpload(file.id);
			$('li#'+file.id).slideUp('fast');
		});

		$('#image_error_list').html('');

		/*$('select[name=img_dir]').attr('id', 'img_dir_select').attr('name', 'img_dir_select').fadeOut();
		var selecteddir = $('select[name=img_dir_select]').val();
		$('input[name=img_dir_hidden]').attr('id', 'img_dir').attr('name', 'img_dir').val(selecteddir);
		if ($('.img_dir').html() == '') {
			$('.img_dir').fadeOut(function() {
				if (selecteddir == '') selecteddir = '<?php echo TAB_TEXT_UPLOAD_MAIN_DIRECTORY; ?>';
				$(this).html(selecteddir).fadeIn();
			});
		}*/

		// start the upload since it's queued
		$(this).swfupload('startUpload');
	})
	.bind('fileQueueError', function(event, file, errorCode, message) {
		showAlert('<?php echo sprintf(TAB_TEXT_UPLOAD_SIZE_LIMIT, "'+file.name+'"); ?>');
	})
	.bind('fileDialogComplete', function(event, numFilesSelected, numFilesQueued) {
		//$('.queuestatus').text('Files Selected: <span class="selectedFiles">'+numFilesSelected+'</span> / Queued Files: <span class="queueFiles">'+numFilesQueued+'</span>');
	})
	.bind('uploadStart', function(event, file) {
		var img_dir = $('#img_dir').val();
		if (img_dir != '') img_dir = img_dir.substring(0, img_dir.length - 1);

		var swfu = $.swfupload.getInstance('#swfupload-image');
			swfu.addFileParam(file.id, 'file', $('#products_image_manual').val());
			swfu.addFileParam(file.id, 'img_dir', img_dir);
			swfu.addFileParam(file.id, 'overwrite', $('input:radio[name=overwrite_img]:checked').val());

		$('.upload_log li#'+file.id).find('p.status').text('<?php echo TAB_TEXT_UPLOAD_UPLOADING; ?>');
		$('.upload_log li#'+file.id).find('span.progressvalue').text('0%');
		//$('.upload_log li#'+file.id).find('span.cancel').hide();
	})
	.bind('uploadProgress', function(event, file, bytesLoaded){

		//Show Progress
		var percentage=Math.round((bytesLoaded/file.size)*100);
		$('.bar'+file.id).progressbar('value', percentage);
		$('.upload_log li#'+file.id).find('span.progressvalue').text(percentage+'%');
	})
	.bind('uploadSuccess', function(event, file, serverData){
		var result = $.parseJSON(serverData);

		$('.bar'+file.id).progressbar('value', 100);

		var item=$('.upload_log li#'+file.id);
		item.find('span.progressvalue').text('100%');
		item.find('p.status').html(result.message);
		item.queue(function(next) {
			$(this).delay(3000).fadeOut(function() {
				$(this).remove();
			});

			next();
		});

		if (result.status != 'success') {
			$('#image_error_list').append('<ol>'+result.message+'</ol>');
		}

		if (result.status != 'error') {
			if ($('#products_image_manual').val() == '') $('#products_image_manual').val(result.filename);

			filename = $('#products_image_manual').val();
			$('#imagename').html(filename);
		}
	})
	.bind('uploadComplete', function(event, file){
		var swfu = $.swfupload.getInstance('#swfupload-image');
		if (swfu.getStats().files_queued == 0) reloadImageList(true);

		$(this).swfupload('startUpload');
	});

	$('input[name=products_options_sort_order], input[name=products_attributes_maxdays], input[name=products_attributes_maxcount]').live('keyup', function() {
		validateNewAttribute();
	});

	$('#OptionName, #OptionValue').live('change', function() {
		validateNewAttribute();
	});

	$('#OptionName').val($("#OptionName option:first").val());

	$('#addAttribute').live('click', function() {
		var OptionName = $('#OptionName').val();
		var OptionValue = '';

		$("#OptionValue option:selected").each(function () {
			OptionValue += $(this).val() + '-';
		});

		if (OptionValue) OptionValue = OptionValue.substr(OptionValue, OptionValue.length - 1);

		var Order = $('input[name=products_options_sort_order]').val();
		var ExpiryDays = $('input[name=products_attributes_maxdays]').val();
		var DownloadCount = $('input[name=products_attributes_maxcount]').val();
		var File = $('input[name=file_selected]').val();

		if (OptionName && OptionValue && Order && ExpiryDays && DownloadCount<?php if (DOWNLOAD_ENABLED == 'true') { ?> && File<?php } ?>) {
			$('.attributes').block();

			$.ajax({
				url: url + '?a=add_new_attribute',
				type: 'POST',
				data: ({ option_name: OptionName, option_value: $("#OptionValue").val(), order: Order, expiry_days: ExpiryDays, download_count: DownloadCount, file: File, pID: <?php echo ($_GET['pID'] ? $_GET['pID'] : 0); ?> }),
				dataType: 'json',
				success: function(result) {

					if (result.status == 'success')
					{
						$('#attributelist tbody').fadeOut(function() {
							$(this).html(result.html).fadeIn();
						});
						$('.attributes').unblock();
					}
					else
					{
						showAlert(result.message);
						reloadAttributeList(false);
					}
				}
			});
		} else {
			showAlert('Please, make sure that you entered all necessary details and selected a file');
		}
	});

	update_option();

	$('input[name=products_image_manual]').live('keyup', function() {
		reloadImageList(true);
	});
});
</script>
<?php if ($action != 'new_product_meta_tags' && $editor_handler != '') include ($editor_handler); ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top">
<?php
if ($action == 'new_product' or $action == 'new_product_meta_tags') {
	if ($action == 'new_product_meta_tags') {
		require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info_metatags.php');
	} else {
		require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/collect_info.php');
	}
} elseif ($action == 'new_product_preview' or $action == 'new_product_preview_meta_tags') {
	if ($action == 'new_product_preview_meta_tags') {
		require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info_meta_tags.php');
	} else {
		require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/preview_info.php');
	}
} else {
	require(DIR_WS_MODULES . 'category_product_listing.php');

	$heading = array();
	$contents = array();
	switch ($action) {
		case 'new_category':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form('newcategory', FILENAME_CATEGORIES, 'action=insert_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"'));
			$contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);

			$category_inputs_string = '';
			$languages = zen_get_languages();
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name'));
			}

			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_NAME . $category_inputs_string);
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_IMAGE . '<br />' . zen_draw_file_field('categories_image'));

			$dir = @dir(DIR_FS_CATALOG_IMAGES);
			$dir_info[] = array('id' => '', 'text' => TAB_TEXT_UPLOAD_MAIN_DIRECTORY);
			while ($file = $dir->read()) {
				if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
					$dir_info[] = array('id' => $file . '/', 'text' => $file);
				}
			}
			$dir->close();

			$default_directory = substr($cInfo->categories_image, 0, strpos($cInfo->categories_image, '/') + 1);
			$contents[] = array('text' => TEXT_CATEGORIES_IMAGE_DIR . ' ' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));

			$contents[] = array('text' => '<br />' . TEXT_SORT_ORDER . '<br />' . zen_draw_input_field('sort_order', '', 'size="4"'));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'edit_category':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form('categories', FILENAME_CATEGORIES, 'action=update_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"') . zen_draw_hidden_field('categories_id', $cInfo->categories_id));
			$contents[] = array('text' => TEXT_EDIT_INTRO);

			$category_inputs_string = '';
			$languages = zen_get_languages();
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', zen_get_category_name($cInfo->categories_id, $languages[$i]['id']), zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name'));
			}
			$contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);
			$contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_IMAGE . '<br />' . zen_draw_file_field('categories_image'));

			$dir = @dir(DIR_FS_CATALOG_IMAGES);
			$dir_info[] = array('id' => '', 'text' => TAB_TEXT_UPLOAD_MAIN_DIRECTORY);
			while ($file = $dir->read()) {
				if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
					$dir_info[] = array('id' => $file . '/', 'text' => $file);
				}
			}
			$dir->close();

			$default_directory = substr($cInfo->categories_image, 0, strpos($cInfo->categories_image, '/') + 1);
			$contents[] = array('text' => TEXT_CATEGORIES_IMAGE_DIR . ' ' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
			$contents[] = array('text' => '<br>' . zen_info_image($cInfo->categories_image, $cInfo->categories_name));
			$contents[] = array('text' => '<br>' . $cInfo->categories_image);

			$contents[] = array('text' => '<br />' . TEXT_EDIT_SORT_ORDER . '<br />' . zen_draw_input_field('sort_order', $cInfo->sort_order, 'size="2"'));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'delete_product':
			if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/sidebox_delete_product.php')) {
				require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/sidebox_delete_product.php');
			} else {
				require(DIR_WS_MODULES . 'sidebox_delete_product.php');
			}
			break;
		case 'move_product':
			if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/sidebox_move_product.php')) {
				require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/sidebox_move_product.php');
			} else {
				require(DIR_WS_MODULES . 'sidebox_move_product.php');
			}
			break;
		case 'copy_to':
			$copy_attributes_delete_first = '0';
			$copy_attributes_duplicates_skipped = '0';
			$copy_attributes_duplicates_overwrite = '0';
			$copy_attributes_include_downloads = '1';
			$copy_attributes_include_filename = '1';

			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
			// WebMakers.com Added: Split Page
			if (empty($pInfo->products_id)) {
				$pInfo->products_id = $pID;
			}

			$contents = array('form' => zen_draw_form('copy_to', $type_admin_handler, 'action=copy_to_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id));
			$contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
			$contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_PRODUCT . '<br /><b>' . $pInfo->products_name . ' ID#' . $pInfo->products_id . '</b>');
			$contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><b>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</b>');
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES . '<br />' . zen_draw_pull_down_menu('categories_id', zen_get_category_tree(), $current_category_id));
			$contents[] = array('text' => '<br />' . TEXT_HOW_TO_COPY . '<br />' . zen_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br />' . zen_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE);

			$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
			// only ask about attributes if they exist
			if (zen_has_product_attributes($pInfo->products_id, 'false')) {
				$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_ONLY);
				$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_yes', true) . ' ' . TEXT_COPY_ATTRIBUTES_YES . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_no') . ' ' . TEXT_COPY_ATTRIBUTES_NO);
				// future          $contents[] = array('align' => 'center', 'text' => '<br />' . ATTRIBUTES_NAMES_HELPER . '<br />' . zen_draw_separator('pixel_trans.gif', '1', '10'));
				$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
			}
			// only ask if product has discounts
			if (zen_has_product_discounts($pInfo->products_id) == 'true') {
				$contents[] = array('text' => '<br />' . TEXT_COPY_DISCOUNTS_ONLY);
				$contents[] = array('text' => '<br />' . TEXT_COPY_DISCOUNTS . '<br />' . zen_draw_radio_field('copy_discounts', 'copy_discounts_yes', true) . ' ' . TEXT_COPY_DISCOUNTS_YES . '<br />' . zen_draw_radio_field('copy_discounts', 'copy_discounts_no') . ' ' . TEXT_COPY_DISCOUNTS_NO);
				$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
			} else {
				$contents[] = array('text' => '<br />' . 'NO DISCOUNTS');
			}

			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_copy.gif', IMAGE_COPY) . ' <a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			$contents[] = array('text' => '</form>');

			$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
			$contents[] = array('text' => '<form action="' . FILENAME_PRODUCTS_TO_CATEGORIES . '.php' . '?products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id . '" method="post">' . zen_hide_session_id());
			$contents[] = array('align' => 'center', 'text' => '<input type="submit" value="' . BUTTON_PRODUCTS_TO_CATEGORIES . '"></form>');

			break;
			// attribute features
		case 'attribute_features':
			$copy_attributes_delete_first = '0';
			$copy_attributes_duplicates_skipped = '0';
			$copy_attributes_duplicates_overwrite = '0';
			$copy_attributes_include_downloads = '1';
			$copy_attributes_include_filename = '1';
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<br />' . '<strong>' . TEXT_PRODUCTS_ATTRIBUTES_INFO . '</strong>' . '<br />');

			$contents[] = array('align' => 'center', 'text' => '<br />' . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><br />' .
			    '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attributes_preview' . '&products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a>' .
			    '&nbsp;&nbsp;' . '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES) . '</a>' .
			    '<br /><br />');
			$contents[] = array('align' => 'left', 'text' => '<br />' . '<strong>' . TEXT_PRODUCT_ATTRIBUTES_DOWNLOADS . '</strong>' . zen_has_product_attributes_downloads($pInfo->products_id) . zen_has_product_attributes_downloads($pInfo->products_id, true));
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_DELETE . '<strong>' . zen_get_products_name($pInfo->products_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_attributes' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=update_attributes_sort_order' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=attribute_features_copy_to_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=attribute_features_copy_to_category' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');

			$contents[] = array('align' => 'center', 'text' => '<br /><a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
			// attribute copier to product
		case 'attribute_features_copy_to_product':
			$_GET['products_update_id'] = '';
			// excluded current product from the pull down menu of products
			$products_exclude_array = array();
			$products_exclude_array[] = $pInfo->products_id;

			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');
			$contents = array('form' => zen_draw_form('products', FILENAME_CATEGORIES, 'action=update_attributes_copy_to_product&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id) . zen_draw_hidden_field('products_update_id', $_GET['products_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']));
			$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE);
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_draw_products_pull_down('products_update_id', '', $products_exclude_array, true) . '<br /><br />' . zen_image_submit('button_copy_to.gif', IMAGE_COPY_TO) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
			// attribute copier to product
		case 'attribute_features_copy_to_category':
			$_GET['categories_update_id'] = '';

			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');
			$contents = array('form' => zen_draw_form('products', FILENAME_CATEGORIES, 'action=update_attributes_copy_to_category&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id) . zen_draw_hidden_field('categories_update_id', $_GET['categories_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']));
			$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE);
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_draw_products_pull_down_categories('categories_update_id', '', '', true) . '<br /><br />' . zen_image_submit('button_copy_to.gif', IMAGE_COPY_TO) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
	} // switch
	if ((zen_not_null($heading)) && (zen_not_null($contents))) {
		echo '            <td width="25%" valign="top">' . "\n";

		$box = new box;
		echo $box->infoBox($heading, $contents);

		echo '            </td>' . "\n";
	}

?>

          </tr>
          <tr>
<?php
	// Split Page
	if ($products_query_numrows > 0) {
	if (empty($pInfo->products_id)) {
		$pInfo->products_id = $pID;
	}

?>
            <td class="smallText" align="right"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_RESULTS_CATEGORIES, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS) . '<br>' . $products_split->display_links($products_query_numrows, MAX_DISPLAY_RESULTS_CATEGORIES, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>

<?php
}
// Split Page
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
}

?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
