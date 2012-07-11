<?php
	/**
	 *
	 *  Copyright 2012 Brendon Dugan
	 *
	 *     Licensed under the Apache License, Version 2.0 (the "License");
	 *     you may not use this file except in compliance with the License.
	 *     You may obtain a copy of the License at
	 *
	 *         http://www.apache.org/licenses/LICENSE-2.0
	 *
	 *     Unless required by applicable law or agreed to in writing, software
	 *     distributed under the License is distributed on an "AS IS" BASIS,
	 *     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 *     See the License for the specific language governing permissions and
	 *     limitations under the License.
	 *
	 */

	require("BouncerRole.class.php");

	/**
	 * Created with JetBrains PhpStorm.
	 * User: Brendon Dugan <wishingforayer@gmail.com>
	 * Date: 7/4/12
	 * Time: 9:39 AM
	 *
	 *
	 */
	class Bouncer{
		/**
		 * @var BouncerRole[]
		 */
		private $roles;

		/**
		 *
		 */
		public function __construct(){
			$this->roles = array();
		}

		/**
		 * @param $roleList array An array of roles to check for access.
		 * @param $url      string The URL of the page the user is trying to access.
		 *
		 * @returns $granted bool A Boolean value reflecting whether or not the user is allowed to access $url.
		 */
		public function verifyAccess($roleList, $url){
			$granted = false;
			foreach($roleList as $role){
				$obj = $this->roles[$role];
				/** @var $obj BouncerRole */
				$response = $obj->verifyAccess($url);
				if($response->getIsOverridden()){ // If access to the page is overridden return false
					return false; // because any override is sufficient to remove permission.
				}
				if($response->getIsAccessible()){ // If this particular role contains access to the page set granted to true
					$granted = true; // We don't return yet in case another role overrides.
				}
			}
			return $granted;
		}

		/**
		 * @param string $name
		 * @param array  $pages
		 * @param array  $replaces
		 */
		public function addRole($name, $pages, $replaces = null){
			$role               = new BouncerRole($name, $pages, $replaces);
			$this->roles[$name] = $role;
		}

		/**
		 * @param array  $roleList
		 * @param string $url
		 * @param string $failPage
		 */
		public function manageAccess($roleList, $url, $failPage = "index.php"){
			$granted = false;
			foreach($roleList as $role){
				$obj = $this->roles[$role];
				/** @var $obj BouncerRole */
				$response = $obj->verifyAccess("/".$url);
				if($response->getIsOverridden()){ // If access to the page is overridden forward the user to the overiding page
					$loc            = ($obj->getOverridingPage($url) !== false) ? $obj->getOverridingPage($url) : $failPage;
					$locationString = "Location: ".$loc;
					header($locationString);
				}
				if($response->getIsAccessible()){ // If this particular role contains access to the page set granted to true
					$granted = true; // We don't return yet in case another role overrides.
				}
			}
			// If we are here, we know that the page has not been overridden
			// so let's check to see if access has been granted by any of our roles.
			// If not, the user doesn't have access so we'll forward them on to the failure page.
			if(!$granted){
				$locationString = "Location: ".$failPage."?";
				header($locationString);
			}
		}

		/**
		 * @param string $query
		 * @param string $hostname
		 * @param string $username
		 * @param string $password
		 * @param string $schema
		 */
		public function readRolesFromDatabase($hostname = "", $username = "", $password = "", $schema = "", $dbtype = "mysql"){
			$dsn = NULL;
			$db  = NULL;
			switch($dbtype){
				case "mysql":
					$dsn = $dbtype.":dbname=".$schema.";host=".$hostname;
					try{
						$db = new PDO($dsn, $username, $password);
					}
					catch(PDOException $e){
						throw new Exception("Error connecting to MySQL!: ".$e->getMessage());
					}
					break;
				case "oci":
					$dsn = $dbtype.":host=".$hostname.";dbname=".$schema;
					try{
						$db = new PDO($dsn, $username, $password);
					}
					catch(PDOException $e){
						throw new Exception("Error connecting to Oracle!: ".$e->getMessage());
					}
					break;
				case "sqlsrv":
					$dsn = $dbtype.":Server=".$hostname.";Database=".$schema;
					try{
						$db = new PDO($dsn, $username, $password);
					}
					catch(PDOException $e){
						throw new Exception("Error connecting to SQL Server!: ".$e->getMessage());
					}
					break;
				default:
					throw new Exception("I don't know that database!");

			}
			return true;
		}

		/**
		 * @throws Exception
		 */
		private function throwNotImplementedException(){
			throw new Exception("This method has not been implemented yet.");
		}

	}