<?php
/**
 * @copyright Custom Scriptz
 * http://customscriptz.com | diego@customscriptz.com
 * Cannot be sold or freely distributed
 */

/**
 * Delete dir recursively
 *
 * @param string $dir Dir to delete
 * @return boolean If it was successfully or not.
 */

function rrmdir($dir)
{
    $result = true;
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    if (!rrmdir($dir . "/" . $object)) $result = false;
                }else if (!unlink($dir . "/" . $object))
                    $result = false;
            }
        }

        reset($objects);
        if (!rmdir($dir)) $result = false;
    } else {
        $result = false;
    }

    return $result;
}


/**
 * Get File or Dir Size
 *
 * @param string Full Path for Dir or File
 * @return string Size Formatted
 */

function getFileDirSize($item)
{
    $size = 0;
    if (is_dir($item)) {
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($item)) as $file) {
            $size += $file->getSize();
        }
    }else {
        $size = filesize($item);
    }

    $units = array(' Bytes', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2) . $units[$i];
}


/**
 * Return the mime type of the file
 *
 * @param $filename Full path for filename
 * @return string File mimetype
 */

function get_mime_content_type($filename)
{
    $mime_types = array(

        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

    $ext = strtolower(@array_pop(@explode('.', $filename)));
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    }elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    }else {
        return 'application/octet-stream';
    }
}

/**
 * Return a list of files for file manager tab
 *
 * @param string $dir Dir to list
 * @return string HTML with file list
 */

function fileManagerFiles($dir = '')
{
	global $db;
	
	$providerDir = '';
	if (defined('PROVIDER_DIR'))
	{
		$provider = $db->Execute("SELECT manufacturers_dir FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = " . $_SESSION['provider_id']);
		$providerDir = $provider->fields['manufacturers_dir'] . '/';
		if (strpos($dir, $providerDir) !== false)
			$dir = str_replace($providerDir, '', $dir);
	}	
	
	if (substr($dir, 0, 1) == '.' || strpos($dir, '..') !== false) $dir = '/';
	$dir = substr(DIR_FS_DOWNLOAD . $providerDir, 0, strlen(DIR_FS_DOWNLOAD . $providerDir) - 1) . ($dir ? $dir : '/');
	$dir_clean = str_replace(DIR_FS_DOWNLOAD, '', $dir);
	if (!is_dir($dir)) $dir = DIR_FS_DOWNLOAD . $providerDir;
	$files = scandir($dir);
	natcasesort($files);

	if ($dir != DIR_FS_DOWNLOAD) {
		$html = '<tr class="dir noFile noSelect">';
		$html .= '<td class="ui-widget-content">&nbsp;</td>';
		$html .= '<td class="ui-widget-content"><input type="hidden" value="' . str_replace(substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1), '', dirname($dir)) . '/" class="dirpath" /><img src="images/up.png" alt="Up One Level" />&nbsp;/' . $dir_clean . '..</td>';
		$html .= '<td class="ui-widget-content">' . getFileDirSize($dir) . '</td>';
		$html .= '<td class="ui-widget-content">Dir Size</td>';
		$html .= '</tr>';
	} else if (count($files) > 2) {
		$html = '<tr class="dirSize noHover noFile noSelect">';
		$html .= '<td class="ui-widget-content">&nbsp;</td>';
		$html .= '<td class="ui-widget-content">/</td>';
		$html .= '<td class="ui-widget-content">' . getFileDirSize($dir) . '</td>';
		$html .= '<td class="ui-widget-content">Dir Size</td>';
		$html .= '</tr>';
	}

	if (count($files) > 2) {
		/* The 2 accounts for . and .. */
		// All dirs
		foreach($files as $file) {
			if (file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) && $file[0] != '.') {
				$dirname = dirname($dir . $file);
				$html .= '<tr class="dir noSelect">';
				$html .= '<td class="ui-widget-content" align="center"><input type="checkbox" class="checkbox" id="' . htmlentities($file) . '" value="' . htmlentities($file) . '" /></td>';
				$html .= '<td class="ui-widget-content">
					<input type="hidden" value="' . htmlentities($file) . '" class="itemname" />
					<input type="hidden" value="' . str_replace(substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1), '', $dirname) . '/' . $file . '/" class="dirpath" />
					<img src="images/filetype-icons/dir.png" alt="" class="icon" />&nbsp;' . htmlentities($file) . '</td>';
				$html .= '<td class="ui-widget-content">' . getFileDirSize($dir . $file) . '</td>';
				$html .= '<td class="ui-widget-content" align="center">&nbsp;</td>';
				$html .= '</tr>';
			}
		}
		// All files
		foreach($files as $file) {
			if (file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file) && $file[0] != '.') {
				$ext = preg_replace('/^.*\./', '', $file);
				$icon = (file_exists('images/filetype-icons/' . $ext . '.png') ? $ext : 'misc');
				$html .= '<tr class="noSelect">';
				$html .= '<td class="ui-widget-content" align="center"><input type="checkbox" class="checkbox" id="' . htmlentities($file) . '" value="' . htmlentities($file) . '" /></td>';
				$html .= '<td class="ui-widget-content">
					<input type="hidden" value="' . htmlentities($file) . '" class="itemname" />
					<img src="images/filetype-icons/' . $icon . '.png" alt="' . strtoupper($icon) . '" class="icon" />&nbsp;' . htmlentities($file) . '</td>';
				$html .= '<td class="ui-widget-content">' . getFileDirSize($dir . $file) . '</td>';
				$html .= '<td class="ui-widget-content">' . get_mime_content_type($dir . $file) . '</td>';
				$html .= '</tr>';
			}
		}
	} else {
		$html .= '<tr class="noFile noSelect">';
		$html .= '<td>&nbsp;</td>';
		$html .= '<td colspan="3">No Files</td>';
		$html .= '</tr>';
	}

	$dir = str_replace(DIR_FS_DOWNLOAD, '/', $dir) . (substr($dir, - 1) != '/' ? '/' : '');

	return array($dir, $html);
}

/**
 * Return only dirs for file move
 *
 * @param string $dir Full path for dir
 * @param array $itemsToMove Items to Move
 * @return string HTML with dir list
 */

function fileManagerDirsOnly($dir = '', $itemsToMove = array())
{
	global $db;
	
	$providerDir = '';
	if (defined('PROVIDER_DIR'))
	{
		$provider = $db->Execute("SELECT manufacturers_dir FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = " . $_SESSION['provider_id']);
		$providerDir = $provider->fields['manufacturers_dir'] . '/';
		if (strpos($dir, $providerDir) !== false)
			$dir = str_replace($providerDir, '', $dir);
	}	
	
	if (substr($dir, 0, 1) == '.' || strpos($dir, '..') !== false) $dir = '/';
	$dir = substr(DIR_FS_DOWNLOAD . $providerDir, 0, strlen(DIR_FS_DOWNLOAD . $providerDir) - 1) . ($dir ? $dir : '/');
	$dir_clean = str_replace(DIR_FS_DOWNLOAD, '', $dir);
	if (!is_dir($dir)) $dir = DIR_FS_DOWNLOAD . $providerDir;

	if ($dir != DIR_FS_DOWNLOAD) {
		$html = '<tr class="dir noFile noSelect">';
		$html .= '<td class="ui-widget-content"><input type="hidden" value="' . str_replace(substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1), '', dirname($dir)) . '/" class="dirpath" />..</td>';
		$html .= '</tr>';
	}
	
	foreach ($itemsToMove as $item) {
		$moveItems[] = $dir . $item;
	}

	$dircount = 0;
	
	
	$files = scandir($dir);
	natcasesort($files);
	
	// All dirs
	foreach($files as $file) {
		if (!in_array($file, array('.', '..')) && is_dir($dir . $file) && !in_array($dir . $file, $moveItems)) {
			$dircount++;
			$dirname = dirname($dir . $file);

			$file = str_replace(DIR_FS_DOWNLOAD, '', $file);
			$html .= '<tr class="dir noSelect">';
			$html .= '<td class="ui-widget-content">
				<input type="hidden" value="' . htmlentities($file) . '" class="itemname" />
				<input type="hidden" value="' . str_replace(substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1), '', $dirname) . '/' . $file . '/" class="dirpath" />
				<img src="images/filetype-icons/dir.png" alt="" class="icon" />&nbsp;' . str_replace(substr(DIR_FS_DOWNLOAD, 0, strlen(DIR_FS_DOWNLOAD) - 1), '', $dir) . $file . '</td>';
			$html .= '</tr>';
		}
	}

	/*foreach($moveItems as $item)
	{
		if (is_dir($item) && !in_array($item, $moveItems))
		{
			$item = str_replace(substr(DIR_FS_DOWNLOAD . $providerDir, 0, strlen(DIR_FS_DOWNLOAD . $providerDir)), '', $item);
			$html .= '<tr class="dir noSelect">';
			$html .= '<td class="ui-widget-content">
				<img src="images/filetype-icons/dir.png" alt="" class="icon" />&nbsp;' . htmlentities($item) . '</td>';
			$html .= '</tr>';
		}
	}*/

	if (!$dircount) {
		$html .= '<tr class="noFile noSelect">';
		$html .= '<td>No Directories Found</td>';
		$html .= '</tr>';
	}

	$dir = str_replace(DIR_FS_DOWNLOAD, '/', $dir) . (substr($dir, - 1) != '/' ? '/' : '');

	return array($dir, $html);
}


/**
 * List of Images for Images Tab
 *
 * @param string $image Image name
 * @param string $dir Directory of the image, otherwise get Zen Cart default path
 * @param string $url URL for image file
 * @return string HTML with image list
 */

function getImageList($image, $dir = '', $url = '')
{
	$products_image = substr($image, 0, strpos($image, '.'));
	$image_ext = substr($image, strpos($image, '.') + 1);
	$files = @scandir(DIR_FS_CATALOG_IMAGES . $dir);

	$html = '';
	$file = 0;
	if (count($files) > 0 && is_array($files) && strpos($image, '.') !== false && isset($image_ext) && strlen($image_ext) > 2) {
		foreach ($files as $key => $image) {
			$image = substr($image, 0, strpos($image, '.'));
			if (strpos($image, $products_image) === false) {
				unset($files[$key]);
			}
		}

		sort($files);

		$countFiles = count($files);
		$imgnumber = ($countFiles == 1 ? 1 : 2);
		foreach ($files as $image) {
			$html .= '<tr id="' . substr($image, 0, -4) . '"><td align="center" class="ui-widget-content">';
			if ($countFiles == 1 || $file > 0) $html .= '<ul class="icons"><li class="ui-state-default ui-corner-all" onclick="deleteImage(\'' . $image . '\', this, ' . $imgnumber . '); return false"><span class="ui-icon ui-icon-trash"></span></li></ul>';
			else $html .= '&nbsp;';
			$html .= '<td align="center" class="ui-widget-content"><span style="font-size: 10px">' . $image . '</span></td>';
			$html .= '<td align="center" class="ui-widget-content"><a href="' . $url . $dir . $image . '?' . time() . '" target="_blank"><img src="' . $url . $dir . $image . '" width="100px" alt="" /></a></td>' . "\n";
			$file++;
			$imgnumber++;
		}
	}

	if (!$html) $html = '<tr><td colspan="3" align="center" class="ui-widget-content">No images found</td></tr>';

	$imgcount = '<input type="hidden" name="imgcount" value="' . (int)$file . '" />';
	return $html . $imgcount;
}

/**
 * List of Attributes for Attributes Tab
 *
 * @param int $products_id ID for the product to list the attributes
 * @return string HTML with the attributes list
 */

function getAttributeslist($products_id)
{
	global $db;

	$html = '';
	if ((int)$products_id > 0) {
		$attributes = $db->Execute("SELECT pa.products_attributes_id, po.products_options_name, pov.products_options_values_name, pa.products_options_sort_order, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
								  FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
								  LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON (pa.products_attributes_id = pad.products_attributes_id)
								  LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (pa.options_id = po.products_options_id)
								  LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.options_values_id = pov.products_options_values_id)
								  WHERE po.language_id = '" . $_SESSION['languages_id'] . "'
								  AND pov.language_id = '" . $_SESSION['languages_id'] . "'
								  AND pa.products_id = '" . $products_id . "'
								  ORDER BY pa.products_options_sort_order, po.products_options_name, pov.products_options_values_name");
		while (!$attributes->EOF) {
			$validFile = 'invalid';
			if (file_exists(DIR_FS_DOWNLOAD . $attributes->fields['products_attributes_filename'])) $validFile = 'valid';
			$html .= '
				<tr id="' . $attributes->fields['products_attributes_id'] . '" class="inactive">
					<td>' . $attributes->fields['products_attributes_id'] . '</td>
					<td>' . $attributes->fields['products_options_name'] . '</td>
					<td>' . $attributes->fields['products_options_values_name'] . '</td>
					<td align="center"><input type="hidden" name="products_options_sort_order_previous" value="' . $attributes->fields['products_options_sort_order'] . '" /><input type="text" name="products_options_sort_order" readonly="readonly" value="' . $attributes->fields['products_options_sort_order'] . '" size="2" style="text-align:center" /></td>
					<td align="center"><input type="hidden" name="products_attributes_maxdays_previous" value="' . $attributes->fields['products_attributes_maxdays'] . '" /><input type="text" name="products_attributes_maxdays" readonly="readonly" value="' . $attributes->fields['products_attributes_maxdays'] . '" size="2" style="text-align:center" /></td>
					<td align="center"><input type="hidden" name="products_attributes_maxcount_previous" value="' . $attributes->fields['products_attributes_maxcount'] . '" /><input type="text" name="products_attributes_maxcount" readonly="readonly" value="' . $attributes->fields['products_attributes_maxcount'] . '" size="2" style="text-align:center" /></td>
					<td>' . $attributes->fields['products_attributes_filename'] . '</td>
					<td align="center"><img src="images/' . $validFile . '.png" alt="" /></td>
					<td align="center" style="width: 46px">
						<ul class="icons">
							<li class="ui-state-default ui-corner-all icon-save hidden"><span class="ui-icon ui-icon-disk"></span></li>
							<li class="ui-state-default ui-corner-all icon-cancel hidden"><span class="ui-icon ui-icon-cancel"></span></li>
							<li class="ui-state-default ui-corner-all icon-edit"><span class="ui-icon ui-icon-pencil"></span></li>
							<li class="ui-state-default ui-corner-all icon-delete"><span class="ui-icon ui-icon-trash"></span></li>
						</ul>
					</td>
				<tr>
			';
			$attributes->MoveNext();
		}
	}

	if (!$html) $html = '<tr><td colspan="9" align="center" class="ui-widget-content">' . TAB_TEXT_NO_ATTRIBUTES_DEFINED . '</td></tr>';

	return $html;
}

/**
 * Recursively copy a directory and it's contents
 *
 * @param string $source Full path to copy from
 * @param string $dest Full path to copy to
 * @param array $result
 * @return boolean true
 */

function dircopy($source, $dest, $result = array()){
	$source_clean = '/' . str_replace(DIR_FS_DOWNLOAD, '', $source);
	$dest_clean = '/' . str_replace(DIR_FS_DOWNLOAD, '', $dest);
	
	$dirname = substr($source,strrpos($source,'/')+1);
	if(is_dir($source)) if (!$dir_handle=opendir($source)) $result[] = 'Cannot open dir <strong>' . $source_clean . '</strong>';

	if (!mkdir($dest.'/'.$dirname, 0777)) {
		$result[] = 'Cannot create dir <strong>' . $dest_clean . '/' . $dirname . '</strong>';
	}
	while($file=readdir($dir_handle))
	{
		if($file!="." && $file!="..")
		{
			if (!copy($source."/".$file, $dest."/".$dirname."/".$file)) $result[] = 'Cannot copy file from <strong>' . $source_clean."/".$file . '</strong> to <strong>' . $dest_clean."/".$dirname."/".$file.'</strong>';
			else
			{
				$dest1 = $dest."/".$dirname;
				dircopy($source."/".$file, $dest1, $result);
			}
		}
	}
	closedir($dir_handle);
	return $result;
}

/**
 * Recursively move a directory and it's contents
 *
 * @param string $source Full path to copy from
 * @param string $dest Full path to copy to
 * @return boolean true
 */

function dirmove($source, $dest, $result = array()){
	$source_clean = '/' . str_replace(DIR_FS_DOWNLOAD, '', $source);
	$dest_clean = '/' . str_replace(DIR_FS_DOWNLOAD, '', $dest);
	
	$dirname = substr($source,strrpos($source,'/')+1);
	if(is_dir($source)) if (!$dir_handle=opendir($source)) $result[] = 'Cannot open dir <strong>' . $source_clean . '</strong>';
	if (!mkdir($dest.'/'.$dirname, 0777)) {
		$result[] = 'Cannot create dir <strong>' . $dest_clean . '/' . $dirname . '</strong>';
	}
	
	while($file=readdir($dir_handle))
	{
		if($file!="." && $file!="..")
		{
			if(!is_dir($source."/".$file))
			{
				if (!copy($source."/".$file, $dest."/".$dirname."/".$file)) $result[] = 'Cannot copy file from <strong>' . $source_clean."/".$file . '</strong> to <strong>' . $dest_clean."/".$dirname."/".$file.'</strong>';
				if (!unlink($source."/".$file)) $result[] = 'Cannot delete file <strong>'.$source_clean.'/'.$file.'</strong>';

			}
			else
			{
				$dest1 = $dest."/".$dirname;
				dirmove($source."/".$file, $dest1, $result);
			}
		}
	}
	closedir($dir_handle);
	if (!rmdir($source)) $result[] = 'Cannot delete dir <strong>' . $source_clean . '</strong>';
	return $result;
}