<?php
/********************************************************************
 * Copyright (C) 2019 MegaOptim (https://megaoptim.com)
 *
 * This file is part of MegaOptim Image Optimizer
 *
 * MegaOptim Image Optimizer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MegaOptim Image Optimizer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MegaOptim Image Optimizer. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}

class MGO_Upgrader extends MGO_BaseObject {

	/**
	 * List of database revisions
	 * @var MGO_Rev[]
	 */
	protected $revisions = array();

	/**
	 * MGO_DB_Upgrader constructor.
	 */
	public function __construct() {
		megaoptim_include_file('includes/migrations/revisions/MGO_Rev.php');
		megaoptim_include_file('includes/migrations/revisions/MGO_Rev_1000.php');
		megaoptim_include_file('includes/migrations/revisions/MGO_Rev_1001.php');
		array_push($this->revisions, new MGO_Rev_1000());
		array_push($this->revisions, new MGO_Rev_1001());
	}

	/**
	 * Run upgrade.
	 */
	public function upgrade() {
		foreach($this->revisions as $revision) {
			if($revision->is_required()) {
				$revision->run();
			}
		}
	}

	/**
	 * Maybe run upgrade?
	 * - Used for hooks.
	 */
	public function maybe_upgrade() {
		if(is_array($this->revisions) && count($this->revisions) > 0) {
			$last_revision = $this->revisions[count($this->revisions) - 1];
			if($last_revision->id > megaoptim_get_db_version()) {
				$this->upgrade();
			}
		}

	}
}