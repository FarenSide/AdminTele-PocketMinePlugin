<?php

/*
__PocketMine Plugin__
name=AdminTele
version=0.1
author=Junyi00
class=AdminTele
apiversion=10
*/

class AdminTele implements Plugin{
	private $api, $path, $config;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("adCheck", "As an admin or owner, teleport to a player, allowing u to return with /adreturn", array($this, "Teleport"));
		$this->api->console->register("adReturn", "As an admin or owner, teleport back to your original location after using /adcheck", array($this, "Teleport"));
		$this->path = $this->api->plugin->configPath($this);
		$this->config = new Config($this->path."config.yml", CONFIG_YAML, array());
		$this->owner = $this->config->get("Owner");
	}
	
	public function __destruct(){
	
	}
	
	private function PermissionCheck($name) {
		if ($this->api->ban->isOP($name)) {
						return True;
		}
		else {
			return False;
		}
	}
	
	private function SaveLocation($name) {
		$player = $this->api->player->get($name);
		$x = $player->entity->x;	
		$y = $player->entity->y;
 		$z = $player->entity->z;
 		
 		$playerLoc = array(
 			"$name" => array (
 				"x" => $x, 
 				"y" => $y,
 				"z" => $z
 		));
 		
 		$this->overwriteConfig($playerLoc);
	}
	
	private function ResetData($name) {
		$cfg = $this->api->plugin->readYAML($this->path . "config.yml");	
		$array = array (
			"$name" => array(
			));
		$this->overwriteConfig($array);
	}
	
	public function Teleport($cmd, $arg, $issuer) {
		$ms = "";
		switch($cmd) {
			case "adCheck":
				$name = $issuer->username;
				$target = $arg[0];
				$allowed = False;
				
				$allowed = $this->PermissionCheck($name);
				
				if ($allowed == False) {
					$ms = "You do not have permission to use this command!\n";
					return $ms;
				}
				else {
					if (!$this->api->player->get($target)) {
						$ms = "$target is not online!\n";
						return $ms;
					}
					if ($name == $target) {
						$ms = "Invalid player name\n";
						return $ms;
					}		
 					$this->SaveLocation($name);
 					
 					$player = $this->api->player->get($target);
 					$x = $player->entity->x;
 					$y = $player->entity->y;
 					$z = $player->entity->z;
 					$issuer->teleport(new Vector3($x, $y, $z));
 					$ms = "Teleported to $target\n";
			 	}
			 	break;
			 
			 case "adReturn":
			 	$cfg = $this->api->plugin->readYAML($this->path . "config.yml");
			 	$name = $issuer->username;
			 	if (!$cfg[$name]["x"]) {
			 		$ms = "You did not use /adcheck b4 this!\n";
			 		break;
			 	}
			 	else {
			 		$player = $this->api->player->get($name);
			 		$x = $cfg[$name]["x"];
			 		$y = $cfg[$name]["y"];
			 		$z = $cfg[$name]["z"];
			 		$player->teleport(new Vector3($x, $y, $z));
			 		
			 		$this->ResetData($name);
			 		
			 		$ms = "Teleported back to your original location\n";
			 	}
			 	break;
				
		}
		
		return $ms;
	}
	
	private function overwriteConfig($dat){
		$cfg = array();
		$cfg = $this->api->plugin->readYAML($this->path . "config.yml");
		$result = array_merge($cfg, $dat);
		$this->api->plugin->writeYAML($this->path."config.yml", $result);
	}
	
}
