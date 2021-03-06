<?php

/**
 * Project: Welcompose
 * File: object.class.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *  
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Media_Object object.
 * 
 * @return object
 */
function Media_Object ()
{ 
	if (Media_Object::$instance == null) {
		Media_Object::$instance = new Media_Object(); 
	}
	return Media_Object::$instance;
}

class Media_Object {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Adds object to the object table. Takes a field=>value array with
 * object data as first argument. Returns insert id. 
 * 
 * @throws Media_ObjectException
 * @param array Row data
 * @return int Object id
 */
public function addObject ($sqlData)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Media_ObjectException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new object will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(WCOM_DB_MEDIA_OBJECTS, $sqlData);
}

/**
 * Updates object. Takes the object id as first argument, a
 * field=>value array with the new object data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_ObjectException
 * @param int Object id
 * @param array Row data
 * @return int Affected rows
*/
public function updateObject ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_ObjectException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_MEDIA_OBJECTS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes object from the object table. Takes the object id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Media_ObjectException
 * @param int Object id
 * @return int Amount of affected rows
 */
public function deleteObject ($id)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_MEDIA_OBJECTS, $where, $bind_params);
}

/**
 * Selects one object. Takes the object id as first argument.
 * Returns array with object information.
 * 
 * @throws Media_ObjectException
 * @param int Object id
 * @return array
 */
public function selectObject ($id)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_objects`.`id` AS `id`,
			`media_objects`.`project` AS `project`,
			`media_objects`.`description` AS `description`,
			`media_objects`.`tags` AS `tags`,
			`media_objects`.`file_name` AS `file_name`,
			`media_objects`.`file_name_on_disk` AS `file_name_on_disk`,
			`media_objects`.`file_mime_type` AS `file_mime_type`,
			`media_objects`.`file_width` AS `file_width`,
			`media_objects`.`file_height` AS `file_height`,
			`media_objects`.`file_size` AS `file_size`,
			`media_objects`.`preview_name_on_disk` AS `preview_name_on_disk`,
			`media_objects`.`preview_mime_type` AS `preview_mime_type`,
			`media_objects`.`preview_width` AS `preview_width`,
			`media_objects`.`preview_height` AS `preview_height`,
			`media_objects`.`preview_size` AS `preview_size`,
			`media_objects`.`date_modified` AS `date_modified`,
			`media_objects`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_MEDIA_OBJECTS." AS `media_objects`
		WHERE
			`media_objects`.`id` = :id
		AND
			`media_objects`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more objects. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>types, array, optional: Return only objects that belong to the given
 * generic types</li>
 * <li>tags, array, optional: Returns objects with given tags</li>
 * <li>timeframe, string, optional: Returns objects from given timeframe</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * </ul>
 * 
 * @throws Media_ObjectException
 * @param array Select params
 * @return array
 */
public function selectObjects ($params = array())
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// define some vars
	$id = null;
	$timeframe = null;	
	$tags = null;
	$order_macro = null;
	$types = array();
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ObjectException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'tags':
			case 'order_macro':
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'start':
			case 'limit':
			case 'id':
					$$_key = (int)$_value;
				break;
			case 'types':
					$$_key = (array)$_value;
				break;
			default:
				throw new Media_ObjectException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`media_objects`.`name`',
		'DATE_ADDED' => '`media_objects`.`date_added`',
		'DATE_MODIFIED' => '`media_objects`.`date_modified`'
	);
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// load Media_Tag
	$TAG = load('Media:Tag');
	
	// prepare query
	$sql = "
		SELECT
			`media_objects`.`id` AS `id`,
			`media_objects`.`project` AS `project`,
			`media_objects`.`description` AS `description`,
			`media_objects`.`tags` AS `tags`,
			`media_objects`.`file_name` AS `file_name`,
			`media_objects`.`file_name_on_disk` AS `file_name_on_disk`,
			`media_objects`.`file_mime_type` AS `file_mime_type`,
			`media_objects`.`file_width` AS `file_width`,
			`media_objects`.`file_height` AS `file_height`,
			`media_objects`.`file_size` AS `file_size`,
			`media_objects`.`preview_name_on_disk` AS `preview_name_on_disk`,
			`media_objects`.`preview_width` AS `preview_width`,
			`media_objects`.`preview_height` AS `preview_height`,
			`media_objects`.`preview_size` AS `preview_size`,
			`media_objects`.`date_modified` AS `date_modified`,
			`media_objects`.`date_added` AS `date_added`,
			`media_tags`.`id` AS `tag_id`,
			`media_tags`.`word` AS `tag_word`,
			`media_tags`.`occurrences` AS `occurrences`
		FROM
			".WCOM_DB_MEDIA_OBJECTS." AS `media_objects`
		LEFT JOIN
			".WCOM_DB_MEDIA_OBJECTS2MEDIA_TAGS." AS `media_objects2media_tags`
		  ON
			`media_objects`.`id` = `media_objects2media_tags`.`object`
		LEFT JOIN
			".WCOM_DB_MEDIA_TAGS." AS `media_tags`
		  ON
			`media_objects2media_tags`.`tag` = `media_tags`.`id`
		WHERE
			`media_objects`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);

	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `media_objects`.`id` = :id ";
		$bind_params['id'] = $id;
	}
		
	// add where clauses
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`media_objects`.`date_added`',
			$timeframe);
	}
	if (!empty($tags)) {
		$sql .= " AND ".$HELPER->_sqlLikeFromArray('`media_tags`.`word`',
			$TAG->_prepareTagStringForQuery($tags));
	}
	if (!empty($types) && is_array($types)) {
		$sql .= " AND ".$this->sqlForGenericTypes('`media_objects`.`file_mime_type`', $types);
	}
	
	// aggregate result set
	$sql .= " GROUP BY `media_objects`.`id` ";
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}
	
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Counts objects saved in the media object table.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>types, array, optional: Return only objects that belong to the given
 * generic types</li>
 * <li>tags, array, optional: Returns objects with given tags</li>
 * <li>timeframe, string, optional: Returns objects from given timeframe</li>
 * </ul>
 * 
 * @throws Media_ObjectException
 * @param array Count params
 * @return array
 */
public function countObjects ($params = array())
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// define some vars
	$timeframe = null;
	$tags = null;
	$types = array();
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ObjectException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'tags':
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'types':
					$$_key = (array)$_value;
				break;
			default:
				throw new Media_ObjectException("Unknown parameter $_key");
		}
	}
	
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// load Media_Tag
	$TAG = load('Media:Tag');
	
	// prepare query
	$sql = "
		SELECT
			COUNT(DISTINCT `media_objects`.`id`)
		FROM
			".WCOM_DB_MEDIA_OBJECTS." AS `media_objects`
		LEFT JOIN
			".WCOM_DB_MEDIA_OBJECTS2MEDIA_TAGS." AS `media_objects2media_tags`
		  ON
			`media_objects`.`id` = `media_objects2media_tags`.`object`
		LEFT JOIN
			".WCOM_DB_MEDIA_TAGS." AS `media_tags`
		  ON
			`media_objects2media_tags`.`tag` = `media_tags`.`id`
		WHERE
			`media_objects`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`media_objects`.`date_added`',
			$timeframe);
	}
	if (!empty($tags)) {
		$sql .= " AND ".$HELPER->_sqlLikeFromArray('`media_tags`.`word`',
			$TAG->_prepareTagStringForQuery($tags));
	}
	if (!empty($types) && is_array($types)) {
		$sql .= " AND ".$this->sqlForGenericTypes('`media_objects`.`file_mime_type`', $types);
	}
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Moves object to store. Takes the real name (~ file name on user's disk)
 * as first argument, the path to the uploaded file as second argument. Returns
 * the new name on disk which could be former name on edit action or newly build
 * (uniqid + real name) on initial upload.
 *
 * @throws Media_ObjectException
 * @param string File name
 * @param string Path to uploaded file
 * @param string Current file name on disk
 * @return string File name on disk
 */
public function moveObjectToStore ($name, $path)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Media_ObjectException("Input for parameter name is expected to be a non-empty scalar value");
	}
	if (empty($path) || !is_scalar($path)) {
		throw new Media_ObjectException("Input for parameter path is expected to be a non-empty scalar value");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// prepare file
	$file_name = $name;
		
	// prepare target path
	$target_path = $this->getPathToObject($file_name);
	
	// move file
	move_uploaded_file($path, $target_path);
	
	// apply new chmod if we're supposed to do so
	if (!empty($this->base->_conf['media']['chmod']) && is_numeric($this->base->_conf['media']['chmod'])) {
		@chmod($target_path, octdec($this->base->_conf['media']['chmod']));
	}
	
	// return file name
	return $file_name;
}

/**
 * Removes object form store. Takes the id of the object as
 * first argument. Returns bool.
 *
 * @throws Media_ObjectException
 * @param int Global file id
 * @return bool
 */
public function removeObjectFromStore ($object)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_ObjectException("Input for parameter object is expected to be numeric");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// get object
	$file = $this->selectObject($object);
	
	// if the object is empty, we can skip here
	if (empty($file)) {
		return false;
	}
	
	// prepare path to file on disk
	$path = $this->getPathToObject($file['file_name_on_disk']);
	
	// unlink object
	if (file_exists($path)) {
		if (unlink($path)) {
			// update object in database
			$sqlData = array(
				'file_name' => null,
				'file_name_on_disk' => null,
				'file_mime_type' => null,
				'file_width' => null,
				'file_height' => null,
				'file_size' => null
			);
			$this->updateObject($file['id'], $sqlData);
			
			return true;
		}
	}
	
	return false;
}

/**
 * Creates thumbnail of given image. Takes the original name (as saved on
 * user's disk) of the object as first argument and the filename of the object
 * on the server as second argument. The arguments three and four define the
 * maximum width and height of the thumbnail. The image will be scaled keeping
 * the aspect ratio of the original image.
 * 
 * If the image is smaller than $width or $height and you don't like that, the
 * image will be placed on the middle of an empty canvas with the size of
 * $width x $height if you pass boolean true as fifth argument. The color of the
 * canvas can be defined using a hexadecimal color code as sixth argument
 * (e.g. ffffff for white or ff0000 for red).
 *
 * Returns array with complete array information:
 * 
 * <ul>
 * <li>name: Name of the thumbnail on the server's disk</li>
 * <li>width: Width of the thumbnail</li>
 * <li>height: Height of the thumbnail</li>
 * <li>type: MIME type the thumbnail</li>
 * <li>size: Filesize of the thumbnail in bytes</li>
 * </ul> 
 *
 * @throws Media_ObjectException
 * @param string Original image name
 * @param string Image name on server
 * @param int Maximal thumbnail width
 * @param int Maximal thumbnail height
 * @param bool Fill the image up
 * @param bool Canvas color
 * @return array
 */
public function createImageThumbnail ($orig_name, $object_name, $width, $height, $fill_up = false, $hex = null)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($orig_name) || !is_scalar($orig_name)) {
		throw new Media_ObjectException("orig_name is supposed to be a non-empty scalar value");
	}
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("object_name is supposed to be a non-empty scalar value");
	}
	if (empty($width) || !is_numeric($width)) {
		throw new Media_ObjectException("width is supposed to be a non-empty numeric value");
	}
	if (empty($height) || !is_numeric($height)) {
		throw new Media_ObjectException("height is supposed to be a non-empty numeric value");	
	}
	if (!is_bool($fill_up)) {
		throw new Media_ObjectException("fill_up is supposed to be a boolean value");
	}
	if ($fill_up === true && empty($hex)) {
		throw new Media_ObjectException("to fill up an image a canvas color is required");
	}
	if (!is_null($hex) && !Base_Cnc::filterRequest($hex, WCOM_REGEX_HEX)) {
		throw new Media_ObjectException("hex is supposed to be empty or a hexadecimal value");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	$path = $this->getPathToObject($object_name);
	
	// get image size
	list($width_orig, $height_orig, $type) = @getimagesize($path);
	
	// let's look at the type. if it's 1, 2 or three, go ahead. if not, we kan skip here.
	if ($type != 1 && $type != 2 && $type != 3) {
		return false;
	}
	
	// create new image size
	$width_resized = $width;
	$height_resized = $height;
	$ratio_orig = $width_orig / $height_orig;
	if ($width / $height > $ratio_orig) {
		$width_resized = $height * $ratio_orig;
	} else {
		$height_resized = $width / $ratio_orig;
	}
	
	// create new canvas
	$image_p = imagecreatetruecolor($width_resized, $height_resized);
	
	// import iamge
	switch ($type) {
		case 1:
				$image = imagecreatefromgif($path);
			break;
		case 2:
				$image = imagecreatefromjpeg($path);
			break;
		case 3:
				$image = imagecreatefrompng($path);
			break;
	}
	
	// resize image
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_resized, $height_resized,
		$width_orig, $height_orig);
	
	// fill the image up to the maximum size if desired
	if ($fill_up === true) {
		// create canvas with the maximum size
		$filled_image = imagecreatetruecolor($width, $height);
		
		// calculate the position where the resized image should be placed on the canvas
		$dest_x = ($width_resized / 2) - ($width / 2);
		$dest_y = ($height_resized / 2) - ($height / 2);
		
		// place the resized image on the created canvas
		imagecopy($filled_image, $image_p, 0, 0, $dest_x, $dest_y, $width, $height);
		
		// allocate the color to fill the canvas
		$color = imagecolorallocate($filled_image, hexdec(substr($hex, 0 ,2)),
			hexdec(substr($hex, 2 ,2)), hexdec(substr($hex, 4 ,2)));
		
		// fill the canvas with the background color
		imagefill($filled_image, 0, 0, $color);
		
		// fill the canvas with the background color
		imagefill($filled_image, $width - 1, $height - 1, $color);
		
		// reassign variables
		$image_p = $filled_image;
	}
	
	// prepare save name
	$parts = explode('.', $orig_name);
	$suffix = $parts[count($parts) - 1];
	if (strtolower($suffix) == 'jpg' || $suffix == 'png' || $suffix == 'gif') {
		unset($parts[count($parts) - 1]);
	}
	//$parts[] = 'png';
	$parts[] = '.png';
	
	// $save_name = sprintf("%s_%ux%u_%s", Base_Cnc::uniqueId(), imagesx($image_p),
	// 	imagesy($image_p), implode('.', $parts));
	$save_name = sprintf("%s_%ux%u%s", $parts[0], imagesx($image_p),
		imagesy($image_p), $parts[2]);
	
	// save image as png
	imagepng($image_p, $this->getPathToThumbnail($save_name));
	
	// return thumbnail information
	return array(
		'name' => $save_name,
		'width' => imagesx($image_p),
		'height' => imagesy($image_p),
		'type' => 'image/png',
		'size' => filesize($this->getPathToThumbnail($save_name))
	);
}

/**
 * Remove thumbnail. Takes the object id as first argument.
 * Returns bool.
 *
 * @throws Media_ObjectException
 * @param int Object id
 * @return bool
 */
public function removeImageThumbnail ($object)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_ObjectException("Input for parameter object is expected to be numeric");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// get object
	$file = $this->selectObject($object);
	
	// if the object is empty, we can skip here
	if (empty($file)) {
		return false;
	}
	
	// if there's no thumbnail, we can skip here too
	if (empty($file['preview_name_on_disk'])) {
		return false;
	}
	
	// prepare path to file on disk
	$path = $this->getPathToThumbnail($file['preview_name_on_disk']);
	
	// unlink object
	if (file_exists($path)) {
		if (unlink($path)) {
			// update object in database
			$sqlData = array(
				'preview_name_on_disk' => null,
				'preview_mime_type' => null,
				'preview_height' => null,
				'preview_width' => null,
				'preview_size' => null
			);
			$this->updateObject($file['id'], $sqlData);
			
			return true;
		}
	}
	
	return false;
}

/**
 * Returns full path to media object. Takes media object name on disk
 * as first argument. Please note that the object doesn't have to exist
 * to get the path to a object.
 *
 * @param string Object name
 * @return mixed
 */
public function getPathToObject ($object_name)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['media']['store_disk'].DIRECTORY_SEPARATOR.$object_name;
}

/**
 * Takes media object id  as first argument. Returns full disk path to
 * media object.
 *
 * @param string Object id
 * @return mixed
 */
public function getPathToObjectUsingId ($object_id)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_id) || !is_scalar($object_id)) {
		throw new Media_ObjectException("Object id is supposed to be a non-empty numeric value");
	}
	
	// get object
	$object = $this->selectObject($object_id);
	
	return $this->base->_conf['media']['store_disk'].DIRECTORY_SEPARATOR.$object['file_name_on_disk'];
}

/**
 * Returns full www path to media object. Takes media object name on disk
 * as first argument. Please note that the object doesn't have to exist
 * to get the path to a object.
 *
 * @param string Object name
 * @return mixed
 */
public function getWwwPathToObject ($object_name)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['media']['store_www'].DIRECTORY_SEPARATOR.rawurlencode($object_name);
}

/**
 * Takes media object id  as first argument. Returns full www path to
 * media object.
 *
 * @param string Object id
 * @return mixed
 */
public function getWwwPathToObjectUsingId ($object_id)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_id) || !is_numeric($object_id)) {
		throw new Media_ObjectException("Object id is supposed to be a non-empty numeric value");
	}
	
	// get object
	$object = $this->selectObject($object_id);
	
	// if there's no file name on disk, return an empty url
	if (empty($object['file_name_on_disk'])) {
		return "";
	} else {
		return $this->base->_conf['media']['store_www'].DIRECTORY_SEPARATOR.rawurlencode($object['file_name_on_disk']);
	}
}

/**
 * Returns full path to media thumbnail. Takes the media object name on
 * disk as first argument. Please note that the thumbnail doesn't have to
 * exist to get the path to the thumbnail.
 *
 * @param string Thumbnail name
 * @return mixed
 */
public function getPathToThumbnail ($object_name)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['media']['store_disk'].DIRECTORY_SEPARATOR.$object_name;
}

/**
 * Tests if the image store is ready to save some files there.
 *
 * @return bool
 */
public function imageStoreIsReady ()
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// get configured path
	$path = $this->base->_conf['media']['store_disk'];
	
	// clear stat cache
	clearstatcache();
	
	// execute some checks on the path
	if (empty($path)) {
		return false;
	}
	if (!is_dir($path)) {
		return false;
	}
	if (!is_readable($path)) {
		return false;
	}
	if (!is_writeable($path)) {
		return false;
	}
	if (!file_exists($path.DIRECTORY_SEPARATOR.'.')) {
		return false;
	}
	
	return true;
}

/**
 * Tests given file for uniqueness. Takes the file name as first argument
 * and the id as second argument as an option.
 * 
 * The second argument is used to compare if the uploaded file name equals the
 * former file name that is already on disk. When the file name is identical,
 * we allow to replace this file.
 *   
 * Returns boolean true if file name is unique.
 *
 * @throws Media_ObjectException
 * @param string file name
 * @param integer Id of current file
 * @return bool
 */
public function testForUniqueFilename ($file_name, $id = null)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}	
	// input check
	if (empty($file_name)) {
		throw new Media_ObjectException("Input for parameter file_name is not expected to be empty");
	}
	if (!is_null($id) && !is_numeric($id)) {
		throw new Media_ObjectException("Input for parameter id is expected to be numeric");
	}
	
	if (!empty($id) && is_numeric($id)) {
		// get object path
		$object_path = $this->getPathToObjectUsingId($id);
	}
	
	// get file path on disk
	$target_path = $this->getPathToObject($file_name);
	
	// evaluate result
	if (file_exists($target_path)) {
		if (!empty($object_path) && $object_path == $target_path) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

/**
 * Flips keys and values in type array.
 * 
 * @throws Media_ObjectException
 * @param array
 * @return array
 */
protected function _flipTypes ($types)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($types)) {
		throw new Media_ObjectException("types is supposed to be an array");
	}
	
	$tmp_types = array();
	foreach ($types as $_key => $_value) {
		if ($_value) {
			$tmp_types[] = $_key;
		}
	}
	return $tmp_types;
}

/**
 * Returns list of generic image types.
 * 
 * @throws Media_ObjectException
 * @return array
 */
public function getGenericTypes ()
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	return array(
		'image',
		'document',
		'audio',
		'video',
		'other'
	);
}

/**
 * Returns list of mime types that belong to the given generic
 * type. Valid generic types:
 *
 * <ul>
 * <li>image</li>
 * <li>document</li>
 * <li>audio</li>
 * <li>video</li>
 * <li>other</li>
 * </ul>
 *
 * @throws Media_ObjectException
 * @param string
 * @return array
 */
public function genericTypesToMimeTypes ($generic_type)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// if generic type is other, return an empty array
	if ($generic_type == $other) {
		return array();
	}
	
	// get mime type configurations
	$mime_type_configurations = $this->getMimeTypeConfigurations();
	
	// resolve icon for mime type
	$types = array();
	foreach ($mime_type_configurations as $_mime_type => $_configuration) {
		if ($generic_type == $_configuration['generic_type']) {
			$types[] = $_mime_type;
		}
	}
	
	return $types;
}

/**
 * Tests if object with given mime type can be used for a podcast.
 * Returns bool.
 * 
 * @throws Media_ObjectException
 * @param string Object's mime type
 * @return bool
 */
public function isPodcastFormat ($mime_type)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($mime_type) || !preg_match(WCOM_REGEX_MIME_TYPE, $mime_type)) {
		throw new Media_ObjectException("Invalid mime type supplied");
	}
	
	// get mime type configurations
	$mime_type_configurations = $this->getMimeTypeConfigurations();
	
	// resolve podcastable bit for mime type
	foreach ($mime_type_configurations as $_mime_type => $_configuration) {
		if ($mime_type == $_mime_type) {
			return $_configuration['podcastable'];
		}
	}
	
	return false;
}

/**
 * Generates sql fragment to select objects that belong to the
 * different generic types. Takes the name of the mime type as
 * first argument, the list of generic types that should be
 * queried as second argument. Returns string.
 *
 * @throws Media_ObjectException
 * @param string
 * @param array
 * @return string
 */
protected function sqlForGenericTypes ($field, $types)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Media_ObjectException("Input for parameter field is expected to be a non-empty scalar value");
	}
	if (!is_array($types)) {
		throw new Media_ObjectException("Input for parameter types is not an array");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// generate list of mime types that belong to the given generic types
	$in_types = array();
	foreach ($types as $_type) {
		if ($_type != 'other') {
			$in_types = array_merge($in_types, $this->genericTypesToMimeTypes($_type));
		}
	}
	
	// generate list of mime types that match the generic "other" type
	$not_in_types = array();
	foreach ($types as $_type) {
		if ($_type == 'other') {
			foreach ($this->getGenericTypes() as $_generic_type) {
				if ($_generic_type != 'other') {
					$not_in_types = array_merge($not_in_types,
						$this->genericTypesToMimeTypes($_generic_type));
				}
			}
		}
	}
	// prepare sql fragment
	$sql = array();
	if (!empty($in_types)) {
		$sql[] = $HELPER->_sqlInFromArray($field, $in_types);
	}
	if (!empty($not_in_types)) {
		$sql[] = $HELPER->_sqlNotInFromArray($field, $not_in_types);
	}
	
	// generate and return sql fragment
	return implode(' OR ', $sql);
}

/**
 * Returns name of a fancy icon for the given mime type. Takes the
 * mime type name as first argument. If the given mime type could not
 * be found, the name of a generic icon will be returned.
 * 
 * @throws Media_ObjectExpception
 * @param string
 * @return string
 */
public function mimeTypeToIcon ($mime_type)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($mime_type) || !preg_match(WCOM_REGEX_MIME_TYPE, $mime_type)) {
		throw new Media_ObjectException("Invalid mime type supplied");
	}
	
	// get mime type configurations
	$mime_type_configurations = $this->getMimeTypeConfigurations();
	
	// resolve icon for mime type
	foreach ($mime_type_configurations as $_mime_type => $_configuration) {
		if ($mime_type == $_mime_type) {
			return $_configuration['icon'];
		}
	}
	
	// if no special icon was found, return the name of the default icon
	return $mime_type_configurations['__DEFAULT__']['icon'];
}

/**
 * Returns name of text converter callback for given mime type.
 *
 * @param string Mime type name
 * @return string
 */
public function mimeTypeToInsertCallBack ($mime_type)
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($mime_type) || !preg_match(WCOM_REGEX_MIME_TYPE, $mime_type)) {
		throw new Media_ObjectException("Invalid mime type supplied");
	}
	
	// get mime type configurations
	$mime_type_configurations = $this->getMimeTypeConfigurations();
	
	// resolve callback name for mime type
	foreach ($mime_type_configurations as $_mime_type => $_configuration) {
		if ($mime_type == $_mime_type) {
			return $_configuration['insert_callback'];
		}
	}
	
	// if no special callback was found, return the name of the default callback
	return $mime_type_configurations['__DEFAULT__']['insert_callback'];
}

/**
 * Returns list with mime type configurations.
 * 
 * @return array
 */
public function getMimeTypeConfigurations ()
{
	// access check
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	return array(
		'__DEFAULT__' => array(
			'suffix' => null,
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'other',
			'icon' => 'generic.jpg'
		),
		'application/msword' => array(
			'suffix' => 'doc',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'doc.jpg'
		),
		'application/pdf' => array(
			'suffix' => 'pdf',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'pdf.jpg'
		),
		'application/rtf' => array(
			'suffix' => 'rtf',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'rtf.jpg'
		),
		'application/vnd.ms-excel' => array(
			'suffix' => 'xls',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'xls.jpg'
		),
		'application/vnd.ms-powerpoint' => array(
			'suffix' => 'ppt',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'ppt.jpg'
		),
		'application/vnd.oasis.opendocument.text' => array(
			'suffix' => 'odt',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'odt.jpg'
		),
		'application/vnd.oasis.opendocument.spreadsheet' => array(
			'suffix' => 'ods',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'ods.jpg'
		),
		'application/vnd.oasis.opendocument.presentation' => array(
			'suffix' => 'odp',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'odp.jpg'
		),
		'application/x-shockwave-flash' => array(
			'suffix' => 'swf',
			'podcastable' => false,
			'insert_callback' => 'application-x-shockwave-flash',
			'generic_type' => 'video',
			'icon' => 'swf.jpg'
		),
		'application/zip' => array(
			'suffix' => 'zip',
			'podcastable' => false,
			'insert_callback' => 'document',
			'generic_type' => 'document',
			'icon' => 'zip.jpg'
		),
		'audio/mpeg' => array(
			'suffix' => 'mp3',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'audio',
			'icon' => 'audio.jpg'
		),
		'audio/x-m4a' => array(
			'suffix' => 'm4a',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'audio',
			'icon' => 'audio.jpg'
		),
		'image/gif' => array(
			'suffix' => 'gif',
			'podcastable' => false,
			'insert_callback' => 'image',
			'generic_type' => 'image',
			'icon' => null
		),
		'image/pjpeg' => array(
			'suffix' => 'jpg',
			'podcastable' => false,
			'insert_callback' => 'image',
			'generic_type' => 'image',
			'icon' => null
		),
		'image/jpeg' => array(
			'suffix' => 'jpg',
			'podcastable' => false,
			'insert_callback' => 'image',
			'generic_type' => 'image',
			'icon' => null
		),
		'image/png' => array(
			'suffix' => 'png',
			'podcastable' => false,
			'insert_callback' => 'image',
			'generic_type' => 'image',
			'icon' => null
		),
		'image/tif' => array(
			'suffix' => 'tif',
			'podcastable' => false,
			'insert_callback' => 'image',
			'generic_type' => 'image',
			'icon' => null
		),
		'video/mp4' => array(
			'suffix' => 'mp4',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'video',
			'icon' => 'video.jpg'
		),
		'video/x-m4v' => array(
			'suffix' => 'm4v',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'video',
			'icon' => 'video.jpg'
		),
		'video/quicktime' => array(
			'suffix' => 'mov',
			'podcastable' => true,
			'insert_callback' => 'document',
			'generic_type' => 'video',
			'icon' => 'video.jpg'
		)
	);
}

// end of class
}

class Media_ObjectException extends Exception { }

?>