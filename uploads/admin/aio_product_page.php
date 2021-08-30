<?php
/**
 * @copyright Custom Scriptz
 * http://customscriptz.com | diego@customscriptz.com
 * Cannot be sold or freely distributed
 */

function HandleReturn($status, $message, $filename = '', $die = true)
{
	$result = array();
	$result['status'] = $status;
	$result['message'] = '<span class="' . $status . '">' . $message . ($filename ? ' (' . $filename . ')' : '') . '</span>';
	$result['filename'] = $filename;
	$result['html'] = '';

	echo json_encode($result);
	if ($die) exit(0);
}

$action = (isset($_GET['a']) ? $_GET['a'] : '');
switch ($action) {
	case 'upload_image':
		$result = array();
		// Code for Session Cookie workaround
		if (isset($_POST["PHPSESSID"])) {
			session_id($_POST["PHPSESSID"]);
		} else if (isset($_GET["PHPSESSID"])) {
			session_id($_GET["PHPSESSID"]);
		}

		if (!isset($_POST['SECURITYTOKEN'])) HandleReturn('error', 'Invalid Security Key');
		// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, - 1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
			header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
			HandleReturn('error', 'POST exceeded maximum allowed size.');
			exit(0);
		}
		// Settings
		$img_dir = (isset($_POST['img_dir']) ? $_POST['img_dir'] . '/' : '');
		$save_path = $_POST['UPLOADPATH'] . $img_dir; // The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)
		$upload_name = "Filedata";
		$max_file_size_in_bytes = 2147483647; // 2GB in bytes
		$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-'; // Characters allowed in the file name (in a Regular Expression format)

		// Other variables
		$MAX_FILENAME_LENGTH = 260;
		$file_name = '';
		$file_extension = '';
		$uploadErrors = array(0 => "There is no error, the file uploaded with success",
		    1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
		    2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
		    3 => "The uploaded file was only partially uploaded",
		    4 => "No file was uploaded",
		    6 => "Missing a temporary folder"
		    );
		// Validate the upload
		if (!isset($_FILES[$upload_name])) {
			HandleReturn('error', 'No upload found in \$_FILES for ' . $upload_name);
		} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
			HandleReturn('error', $uploadErrors[$_FILES[$upload_name]["error"]]);
		} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
			HandleReturn('error', 'Upload failed is_uploaded_file test.');
		} else if (!isset($_FILES[$upload_name]['name'])) {
			HandleReturn('error', 'File has no name.');
		}
		// Validate the file size (Warning: the largest files supported by this code is 2GB)
		$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
		if (!$file_size || $file_size > $max_file_size_in_bytes) {
			HandleReturn('error', 'File exceeds the maximum allowed size');
		}

		if ($file_size <= 0) {
			HandleReturn('error', 'File size outside allowed lower bound');
		}
		// Validate file name (for our purposes we'll just remove invalid characters)
		$file_name = preg_replace('/[^' . $valid_chars_regex . ']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
		if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
			HandleReturn('error', 'Invalid file name', $file_name);
		}

		$overwritten = false;
		$file_name = str_replace(' ', '_', $file_name);
		// Validate that we won't over-write an existing file
		if (file_exists($save_path . $file_name) && isset($_POST['overwrite']) && $_POST['overwrite'] == '0' && !isset($_POST['file'])) {
			HandleReturn('error', 'File with this name already exists', $file_name);
		} elseif (file_exists($save_path . $file_name) && isset($_POST['overwrite']) && $_POST['overwrite'] == '1') {
			$overwritten = true;
			@unlink($save_path . $file_name);
		}
		
		// Process the file
		/*
		   At this point we are ready to process the valid file. This sample code shows how to save the file. Other tasks
		   could be done such as creating an entry in a database or generating a thumbnail.

		   Depending on your server OS and needs you may need to set the Security Permissions on the file after it has
		   been saved.
		*/

		if (isset($_POST['file']) && $_POST['file'] != null && $_POST['file'] != 'undefined') {
			$base = substr($_POST['file'], 0, strrpos($_POST['file'], '.'));
			// Check for additional matching images
			if ($dir = @dir($save_path)) {
				while ($file = $dir->read()) {
					if (!is_dir($save_path . $file)) {
						if (preg_match("/^" . $base . "/i", $file) == '1') {
							if ($base . preg_replace("/^$base/", '', $file) == $file) {
								$matches[] = $file;
							}
						}
					}
				}

				if (isset($matches) && sizeof($matches) > 1) {
					sort($matches);
				}

				$dir->close();
			}

			$uploaded_filename = $file_name;
			$uploaded_extension = substr($uploaded_filename, strlen($uploaded_filename) - 4);

			$existingfile_filename = $_POST['file'];
			$existingfile_name = substr($existingfile_filename, 0, strlen($existingfile_filename) - 4);
			$existingfile_extension = substr($existingfile_filename, strlen($existingfile_filename) - 4);

			if (file_exists($save_path . $existingfile_filename)) {
				if (count($matches) > 1) $imgnumber = count($matches);
				else $imgnumber = 1;

				$imagenumber = '_' . ($imgnumber < 10 ? '0' . $imgnumber : $imgnumber);
				$file_name = $existingfile_name . $imagenumber . $uploaded_extension;
			} else {
				$file_name = $existingfile_name . $uploaded_extension;
			}
		}

		if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path . $file_name)) {
			HandleReturn('error', 'File could not be saved', $file_name);
		}

		$uploaded_filename = $file_name;
		$uploaded_extension = substr($file_name, -3);
		if ($uploaded_extension=="jpg" || $uploaded_extension=="jpeg")
			$src = imagecreatefromjpeg($save_path . $file_name);
		else if ($uploaded_extension=="png")
			$src = imagecreatefrompng($save_path . $file_name);
		else if ($uploaded_extension=="gif")
			$src = imagecreatefromgif($save_path . $file_name);

		list($width,$height)=getimagesize($save_path . $file_name);
		if (($width > 1000 || $height > 1000) && isset($src))
		{
			if ($width > 1000)
			{
				$newwidth=700;
				$newheight=($height/$width)*$newwidth;
				$tmp=imagecreatetruecolor($newwidth,$newheight);

				imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
				$filename = $save_path . $file_name;

				imagejpeg($tmp,$filename,75);

				imagedestroy($src);
				imagedestroy($tmp);
			}
			else
			{
				$newheight=700;
				$newwidth=($height/$width)*$newheight;
				$tmp=imagecreatetruecolor($newwidth,$newheight);

				imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
				$filename = $save_path . $file_name;

				imagejpeg($tmp,$filename,75);

				imagedestroy($src);
				imagedestroy($tmp);
			}
		}
		
		if ($overwritten) {
			HandleReturn('caution', 'Image uploaded successfully, but overwritten', $file_name);
		} else {
			HandleReturn('success', 'Image uploaded successfully', $file_name);
		}
		break;
	case 'upload_file':
		$result = array();
	// sleep(5);
	// Code for Session Cookie workaround
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	} else if (isset($_GET["PHPSESSID"])) {
		session_id($_GET["PHPSESSID"]);
	}
	// if (!isset($_POST['SECURITYTOKEN'])) HandleReturn('error', 'Invalid Security Key');
	// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
	        $POST_MAX_SIZE = ini_get('post_max_size');
	$unit = strtoupper(substr($POST_MAX_SIZE, - 1));
	$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

	if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
		header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
		HandleReturn('error', 'POST exceeded maximum allowed size.');
		exit(0);
	}
	// Settings
	$save_path = $_POST['UPLOADPATH'] . ($_POST['path'] ? $_POST['path'] : '/'); // The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)
	$upload_name = "Filedata";
	$max_file_size_in_bytes = 2147483647; // 2GB in bytes
	$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-'; // Characters allowed in the file name (in a Regular Expression format)

	// Other variables
	$MAX_FILENAME_LENGTH = 260;
	$file_name = '';
	$file_extension = '';
	$uploadErrors = array(0 => "There is no error, the file uploaded with success",
	            1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
	            2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
	            3 => "The uploaded file was only partially uploaded",
	            4 => "No file was uploaded",
	            6 => "Missing a temporary folder"
	            );
	// Validate the upload
	if (!isset($_FILES[$upload_name])) {
		HandleReturn('error', 'No upload found in \$_FILES for ' . $upload_name);
	} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
		HandleReturn('error', $uploadErrors[$_FILES[$upload_name]["error"]]);
	} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
		HandleReturn('error', 'Upload failed is_uploaded_file test.');
	} else if (!isset($_FILES[$upload_name]['name'])) {
		HandleReturn('error', 'File has no name.');
	}
	// Validate the file size (Warning: the largest files supported by this code is 2GB)
	$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
	if (!$file_size || $file_size > $max_file_size_in_bytes) {
		HandleReturn('error', 'File exceeds the maximum allowed size');
	}

	if ($file_size <= 0) {
		HandleReturn('error', 'File size outside allowed lower bound');
	}
	// Validate file name (for our purposes we'll just remove invalid characters)
	$file_name = preg_replace('/[^' . $valid_chars_regex . ']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
	if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
		HandleReturn('error', 'Invalid file name', $file_name);
	}

	$overwritten = false;
	$file_name = str_replace(' ', '_', $file_name);
	// Validate that we won't over-write an existing file
	if (file_exists($save_path . $file_name) && isset($_POST['overwrite']) && $_POST['overwrite'] == '0' && !isset($_POST['file'])) {
		HandleReturn('error', 'File with this name already exists', $file_name);
	} elseif (file_exists($save_path . $file_name) && isset($_POST['overwrite']) && $_POST['overwrite'] == '1') {
		$overwritten = true;
		@unlink($save_path . $file_name);
	}
	// Process the file
	/*
	   At this point we are ready to process the valid file. This sample code shows how to save the file. Other tasks
	   could be done such as creating an entry in a database or generating a thumbnail.

	   Depending on your server OS and needs you may need to set the Security Permissions on the file after it has
	   been saved.
	*/

	if (isset($_POST['file']) && $_POST['file'] != null && $_POST['file'] != 'undefined') {
		$base = substr($_POST['file'], 0, strrpos($_POST['file'], '.'));
		// Check for additional matching images
		if ($dir = @dir($save_path)) {
			while ($file = $dir->read()) {
				if (!is_dir($save_path . $file)) {
					if (preg_match("/^" . $base . "/i", $file) == '1') {
						if ($base . preg_replace("/^$base/", '', $file) == $file) {
							$matches[] = $file;
						}
					}
				}
			}

			if (sizeof($matches) > 1) {
				sort($matches);
			}

			$dir->close();
		}

		$uploaded_filename = $file_name;
		$uploaded_extension = substr($uploaded_filename, strlen($uploaded_filename) - 4);

		$existingfile_filename = $_POST['file'];
		$existingfile_name = substr($existingfile_filename, 0, strlen($existingfile_filename) - 4);
		$existingfile_extension = substr($existingfile_filename, strlen($existingfile_filename) - 4);

		if (file_exists($save_path . $existingfile_filename)) {
			if (count($matches) > 1) $imgnumber = count($matches);
			else $imgnumber = 1;

			$imagenumber = '_' . ($imgnumber < 10 ? '0' . $imgnumber : $imgnumber);
			$file_name = $existingfile_name . $imagenumber . $uploaded_extension;
		}else {
			$file_name = $existingfile_name . $uploaded_extension;
		}
	}

	if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path . $file_name)) {
		HandleReturn('error', 'File could not be saved', $file_name);
	}else if ($overwritten) {
		HandleReturn('caution', 'File uploaded successfully, but overwritten', $file_name);
	}
	HandleReturn('success', 'File uploaded successfully', $file_name);

	die();
	break;
}

require('includes/application_top.php');

switch ($action) {
	case 'imagelist':
		echo getImageList($_POST['FILENAME'], $_POST['IMG_DIR'], $_POST['IMG_URL']);
		exit(0);
		break;

	case 'deleteimage':
		@unlink($_POST['PATH'] . $_POST['IMG_DIR'] . $_POST['FILENAME']);
		exit(0);
		break;

	case 'filetree':
		$_POST['dir'] = urldecode($_POST['dir']);
		if (file_exists($_POST['dir'])) {
			if (substr($_POST['dir'], -1) != '/')
				$_POST['dir'] .= '/';
			
			$files = scandir($_POST['dir']);
			natcasesort($files);
			if (count($files) > 2) {							
				/* The 2 accounts for . and .. */
				echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
				// All dirs
				foreach($files as $file) {
					if ($file == '.' || $file == '..')
						continue;
					
					if (file_exists($_POST['dir'] . $file) && is_dir($_POST['dir'] . $file) && $file[0] != '.') {
						echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
					}
				}
				// All files
				foreach($files as $file) {
					if ($file == '.' || $file == '..')
						continue;
					
					if (file_exists($_POST['dir'] . $file) && !is_dir($_POST['dir'] . $file) && $file[0] != '.') {
						$ext = preg_replace('/^.*\./', '', $file);
						echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
					}
				}
				echo "</ul>";
			}
		}
		die();
		break;
	case 'add_new_attribute':
		$result = array();
		$option_name = $_POST['option_name'];
		$option_value = $_POST['option_value'];
		$order = $_POST['order'];
		$expiry_days = $_POST['expiry_days'];
		$download_count = $_POST['download_count'];
		$file = str_replace(DIR_FS_DOWNLOAD, '', $_POST['file']);
		$product_id = $_POST['pID'];

		$result['status'] = 'success';
		$result['message'] = '';
		$result['html'] = '<tr><td colspan="4" align="center" class="ui-widget-content">' . TAB_TEXT_NO_ATTRIBUTES_DEFINED . '</td></tr>';

		if ($product_id) {
			for ($i = 0; $i < sizeof($option_value); $i++) {
				$check_duplicate = $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . "
											   where products_id ='" . $product_id . "'
											   and options_id = '" . $option_name . "'
											   and options_values_id = '" . $option_value[$i] . "'");

				if ($check_duplicate->RecordCount() > 0) {
					$result['status'] = 'error';
					$result['message'] = ATTRIBUTE_WARNING_DUPLICATE . ' - ' . zen_options_name($option_name) . ' : ' . zen_values_name($option_value[$i]);
					break;
				} else {
					$products_options_array = $db->Execute("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $option_name . "'");
					$values_id = zen_db_prepare_input((($products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT) or ($products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $option_value[$i]);

					$products_id = zen_db_prepare_input($product_id);
					$options_id = zen_db_prepare_input($option_name);

					$products_options_sort_order = zen_db_prepare_input($order);

					if (zen_not_null($order)) {
						$products_options_sort_order = zen_db_prepare_input($order);
					} else {
						$sort_order_query = $db->Execute("select products_options_values_sort_order from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . $option_value[$i] . "'");
						$products_options_sort_order = $sort_order_query->fields['products_options_values_sort_order'];
					}

					$db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_attributes_id, products_id, options_id, options_values_id, products_options_sort_order)
							  values (0,
									  '" . (int)$products_id . "',
									  '" . (int)$options_id . "',
									  '" . (int)$values_id . "',
									  '" . (int)zen_db_input($products_options_sort_order) . "')");

					if (DOWNLOAD_ENABLED == 'true') {
						$products_attributes_id = $db->Insert_ID();

						$products_attributes_filename = zen_db_prepare_input($file);
						$products_attributes_maxdays = (int)zen_db_prepare_input($expiry_days);
						$products_attributes_maxcount = (int)zen_db_prepare_input($download_count);

						if (zen_not_null($products_attributes_filename)) {
							$db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
								  (products_attributes_id, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount)
								  values (" . (int)$products_attributes_id . ",
										  '" . zen_db_input($products_attributes_filename) . "',
										  '" . zen_db_input($products_attributes_maxdays) . "',
										  '" . zen_db_input($products_attributes_maxcount) . "')");
						}
					}
				}
			}

			$result['html'] = getAttributesList($product_id);
		}else {
			$result['html'] = getAttributesList(0);
		}

		die(json_encode($result));
		break;
	case 'reload_attribute_list':
		$result = array();
		$product_id = $_GET['pID'];
		
		$result['status'] = 'success';
		$result['message'] = '';
		$result['html'] = getAttributesList($product_id);

		die(json_encode($result));
		break;

	case 'save_attribute':	
		$product_id = (int)$_GET['pID'];
		$attribute_id = (int)$_POST['attribute_id'];
		$order = (int)$_POST['products_options_sort_order'];
		$maxdays = (int)$_POST['products_attributes_maxdays'];
		$maxcount = (int)$_POST['products_attributes_maxcount'];
		$db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . " SET products_options_sort_order = " . $order . " WHERE products_attributes_id = " . $attribute_id);
		$db->Execute("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " SET products_attributes_maxdays = " . $maxdays . ", products_attributes_maxcount = " . $maxcount . " WHERE products_attributes_id = " . $attribute_id);
		die();
		break;

	case 'delete_attribute':
		$product_id = (int)$_GET['pID'];
		$attribute_id = (int)$_POST['attribute_id'];
		$db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_attributes_id = " . $attribute_id);
		$db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE products_attributes_id = " . $attribute_id);
		die();
		break;

	case 'delete_allattributes':
		$product_id = (int)$_GET['pID'];
		$attributes = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = " . $product_id);

		while (!$attributes->EOF) {
			$attribute_id = (int)$attributes->fields['products_attributes_id'];

			$db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE products_attributes_id = " . $attribute_id);
			$attributes->MoveNext();
		}

		$db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = " . $product_id);
		die();
		break;

	case 'reload_filemanager':
		$result = array();
		$dir = htmlentities($_GET['dir']);
		$return = fileManagerFiles($dir);

		$result['status'] = 'success';
		$result['message'] = '';
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;

	case 'reload_dirlist':
		$result = array();
		$dir = htmlentities($_GET['dir']);
		$items = explode('|', htmlentities($_GET['items']));
		$return = fileManagerDirsOnly($dir, $items);

		$result['status'] = 'success';
		$result['message'] = '';
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;

	case 'create_dir':
		$result = array();
		$newdir = htmlentities($_GET['newdir']);
		$currentdir = htmlentities($_GET['currentdir']);

		$result['status'] = 'success';
		$result['message'] = '';

		$dir = substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1) . $currentdir;
		if (is_dir($dir . $newdir)) {
			$result['status'] = 'error';
			$result['message'] = 'A dir with the name <strong>' . $newdir . '</strong> already exist.';
		} else if (!@mkdir($dir . $newdir)) {
			$result['status'] = 'error';
			$result['message'] = 'Could not create dir ' . $dir . $newdir;
		}

		$return = fileManagerFiles($currentdir);
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;

	case 'delete_item':
		$result = array();
		$items = explode('|', htmlentities($_GET['item']));
		$currentdir = htmlentities($_GET['currentdir']);

		$result['status'] = 'success';
		$result['message'] = '';

		$dir = substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1) . $currentdir;
		foreach ($items as $item) {
			if (!$item || $dir == DIR_FS_DOWNLOAD . $item || $dir == DIR_FS_DOWNLOAD . '/' . $item) continue;
			if (!file_exists($dir . $item)) {
				$result['status'] = 'error';
				$result['message'] = 'The file <strong>' . $dir . $item . '</strong> doesn\'t exist.';
				break;
			} else if (is_dir($dir . $item)) {
				if (!rrmdir($dir . $item)) {
					$result['status'] = 'error';
					$result['message'] = 'Could not delete1 <strong>' . $dir . $item . '</strong>';
					break;
				}
			} else if (!@unlink($dir . $item)) {
				$result['status'] = 'error';
				$result['message'] = 'Could not delete2 <strong>' . $dir . $item . '</strong>';
				break;
			}
		}

		$return = fileManagerFiles($currentdir);
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;

	case 'rename_item':
		$result = array();
		$item = htmlentities($_GET['item']);
		$newname = htmlentities($_GET['newname']);
		$currentdir = htmlentities($_GET['currentdir']);

		$result['status'] = 'success';
		$result['message'] = '';

		$dir = substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1) . $currentdir;
		if (!$newname) {
			$result['status'] = 'error';
			$result['message'] = 'Please, enter the new filename.';
		} else if (!file_exists($dir . $item)) {
			$result['status'] = 'error';
			$result['message'] = 'The file <strong>' . $dir . $item . '</strong> doesn\'t exist.';
		} else if (file_exists($dir . $newname)) {
			$result['status'] = 'error';
			$result['message'] = 'A file with the name <strong>' . $newname . '</strong> alreay exist.';
		} else if (!@rename($dir . $item, $dir . $newname)) {
			$result['status'] = 'error';
			$result['message'] = 'Could not rename <strong>' . $dir . $item . '</strong> to <strong>' . $dir . $newname . '</strong>';
		}

		$return = fileManagerFiles($currentdir);
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;

	case 'move_items':
		$result = array();
		$items = explode('|', htmlentities($_GET['items']));
		$from = htmlentities($_GET['from']);
		$to = htmlentities($_GET['to']);

		$result['status'] = 'success';
		$result['message'] = '';

		$main = substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1);

		if ($from == $to) {
			$result['status'] = 'error';
			$result['message'] = 'Sorry, you can\'t move items to the same directory.';
		} else {
			$dirs = array();
			$files = array();
			$files_existing = array();

			foreach ($items as $item) {
				if (!$item) continue;
				if (is_dir($main . $from . $item)) {
					if (file_exists($main . $to . $item)) {
						$result['status'] = 'error';
						$result['message'] .= 'A dir called <strong>' . $item . '</strong> already exist at <strong>' . $to . '</strong><br />';
					} else {
						$dirs[] = $item;
					}
				} else if (file_exists($main . $from . $item)) {
					if (file_exists($main . $to . $item)) {
						$result['status'] = 'error';
						$result['message'] .= 'A file called <strong>' . $item . '</strong> already exist at <strong>' . $to . '</strong><br />';
					} else {
						$files[] = $item;
					}
				}
			}

			foreach ($dirs as $dir) {
				$return = dirmove($main . $from . $dir, $main . substr($to, 0, strlen($to) - 1));
			}

			if (count($return) > 0) {
				$result['status'] = 'error';
				foreach ($return as $msg) {
					$result['message'] .= $msg . '<br />';
				}
			}

			foreach ($files as $file) {
				if (!@rename($main . $from . $file, $main . $to . $file)) {
					$result['status'] = 'error';
					$result['message'] .= 'Could not rename <strong>' . $dir . $item . '</strong> to <strong>' . $dir . $newname . '</strong><br />';
				}
			}
		}

		if ($result['message']) $result['message'] = substr($result['message'], 0, strlen($result['message']) - 6);

		$return = fileManagerFiles($currentdir);
		$result['html'] = $return[1];
		$result['dir'] = $return[0];

		die(json_encode($result));
		break;
}