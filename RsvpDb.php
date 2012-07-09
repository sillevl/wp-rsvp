<?php

require_once('RsvpItem.php');

class RsvpDb{

	private $table;

	function RsvpDb(){
		global $wpdb;
		$this->table['request'] = $wpdb->prefix."rsvp_sille_request";
		$this->table['groups'] = $wpdb->prefix."rsvp_sille_groups";

		//var_dump($this->table);

	}

	function saveRequests($list){
		global $wpdb;
		$wpdb->show_errors();
		foreach ($list as $item) {
			$wpdb->insert(
					$this->table["request"],
					array(
						'name' => $item->name,
						'prename' => $item->prename,
						'attending' => $item->isAttending(),
						'vegi' => $item->isVegitarian(),
						'response' => 1,
						'comment' => $item->comment,
						'time' => date("Y-m-d H:i:s")
						)
				);
		}
	}


	function find(){
		global $wpdb;
		
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $rsvpdb WHERE id='1';"));

		$this->name = $result["name"];
		$this->prename = $result["prename"];
		$this->attending = $result["attending"];
		$this->vegitarian = $result["vegitarian"];
	}


	function install(){
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = "CREATE TABLE ".$this->table['request']." (
			id mediumint(11) NOT NULL AUTO_INCREMENT,
			prename varchar(64) DEFAULT '' NOT NULL,
			name varchar(64) DEFAULT '' NOT NULL,
			response tinyint(4) DEFAULT 0 NOT NULL,
			attending tinyint(4) DEFAULT 0 NOT NULL,
			vegi tinyint(1) DEFAULT 0 NOT NULL, 
			comment text DEFAULT '' NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		);";
		dbDelta($sql);

		$sql = "CREATE TABLE ".$this->table['groups']." (
			id mediumint(11) NOT NULL AUTO_INCREMENT,
			requestid mediumint(11) DEFAULT 0 NOT NULL,
			groupid mediumint(11) DEFAULT 0 NOT NULL,
			UNIQUE KEY id (id)
		);";
		dbDelta($sql);
	}

}


?>