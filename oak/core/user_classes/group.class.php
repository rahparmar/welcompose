<?php

/**
 * Project: Oak
 * File: group.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class User_Group {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
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
 * Singleton. Returns instance of the User_Group object.
 * 
 * @return object
 */
public function instance()
{ 
	if (User_Group::$instance == null) {
		User_Group::$instance = new User_Group(); 
	}
	return User_Group::$instance;
}

/**
 * Adds group to the group table. Takes a field=>value array
 * with the group data as first argument. Returns insert id.
 * 
 * @throws User_GroupException
 * @param array Row data
 * @return int Group id
 */
public function addGroup ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new User_GroupException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new group will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_USER_GROUPS, $sqlData);
}

/**
 * Updates group. Takes the group id as first argument, a field=>value
 * array with the new row data as second argument. Returns amount of
 * affected rows.
 *
 * @throws User_GroupException
 * @param int Group id
 * @param array Row data
 * @return int Affected rows
 */
public function updateGroup ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new User_GroupException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project  AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_USER_GROUPS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes group from the user group table. Takes the user group id as
 * first argument. Returns amount of affected rows.
 * 
 * @throws User_GroupException
 * @param int Group id
 * @return int Amount of affected rows
 */
public function deleteGroup ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_USER_GROUPS, $where, $bind_params);
}

/**
 * Selects group. Takes the group id as first argument.
 * Returns array.
 * 
 * @throws User_GroupException
 * @param int Group id
 * @return array
 */
public function selectGroup ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`user_groups`.`id` AS `id`,
			`user_groups`.`project` AS `project`,
			`user_groups`.`name` AS `name`,
			`user_groups`.`description` AS `description`,
			`user_groups`.`editable` AS `editable`,
			`user_groups`.`date_modified` AS `date_modified`,
			`user_groups`.`date_added` AS `date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_groups`.`project` = `application_projects`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `user_groups`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more user groups. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>project, int, required: Project id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws User_GroupException
 * @param array Select params
 * @return array
 */
public function selectGroups ($params = array())
{
	// define some vars
	$project = null;
	$project_in = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_GroupException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'project':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'project_in':
					$$_key = (array)$_value;
				break;
			default:
				throw new User_GroupException("Unknown parameter $_key");
		}
	}
	
	// if no project is given, query all projects of the current user
	if (empty($project) || !is_numeric($project)) {
		// load project class
		$PROJECT = load('application:project');
		
		// get user's projects
		$possible_projects = $PROJECT->selectProjects(array('user' => OAK_CURRENT_USER));
		
		// prepare the sql-in-array
		$project_in = array();
		foreach ($possible_projects as $_project) {
			$project_in[] = (int)$_project['id'];
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`user_groups`.`id` AS `id`,
			`user_groups`.`project` AS `project`,
			`user_groups`.`name` AS `name`,
			`user_groups`.`description` AS `description`,
			`user_groups`.`editable` AS `editable`,
			`user_groups`.`date_modified` AS `date_modified`,
			`user_groups`.`date_added` AS `date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_groups`.`project` = `application_projects`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($project) && is_numeric($project)) {
		$sql .= " AND `application_projects`.`id` = :project ";
		$bind_params['project'] = (int)$project;
	}
	if (!is_null($project_in) && count($project_in) > 0) {
		if (Base_Cnc::testArrayForNumericKeys($project_in) && Base_Cnc::testArrayForNumericValues($project_in)) {
			$sql .= " AND `application_projects`.`id` IN ( :project_in ) ";
			$bind_params['project_in'] = implode(', ', $project_in);
		}
	}
	
	// add sorting
	$sql .= " ORDER BY `user_groups`.`name` ";
	
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
 * Tests given group name for uniqueness. Takes the group name as
 * first argument and an optional group id as second argument. If
 * the group id is given, this group won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if group
 * name is unique.
 *
 * @throws User_GroupException
 *�@param string Group name
 * @param int Group id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// input check
	if (empty($name)) {
		throw new User_GroupException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new User_GroupException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_GroupException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
		'name' => $name
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `id` != :id ";
		$bind_params['id'] = (int)$id;
	} 
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) > 0) {
		return false;
	} else {
		return true;
	}
	
}

// end of class
}

class User_GroupException extends Exception { }

?>