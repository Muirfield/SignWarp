<?php
/**
 ** CONFIG:config.yml
 **/
namespace aliuly\signwarp;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use mf\common\mc;
use mf\common\Cmd;
use mf\common\PluginCallbackTask;
use mf\common\TPU;
use mf\common\Warps;
use mf\common\Perms;

//use pocketmine\command\PluginCommand;
//use pocketmine\command\Command;
//use pocketmine\command\CommandSender;
//use pocketmine\Player;
//use pocketmine\block\Block;
//use pocketmine\item\Item;
//use pocketmine\tile\Sign;

//use pocketmine\event\block\SignChangeEvent;
//use pocketmine\event\block\BlockPlaceEvent;
//use pocketmine\event\player\PlayerInteractEvent;
//use pocketmine\event\player\PlayerQuitEvent;

//use pocketmine\level\Position;
//use pocketmine\math\Vector3;


//use aliuly\signwarp\common\MPMU;

class Main extends PluginBase implements Listener {
  const MAX_HEIGHT = 128;
  const MIN_HEIGHT = 0;

  protected $teleporters;
  protected $wp;
  protected $warps;

  public function onEnable(){
    mc::init($this,$this->getFile());
    
    $this->wp = $this->getServer()->getPluginManager()->getPlugin("WorldProtect");
    if ($this->wp !== NULL) {
      if (version_compare($this->wp->getDescription()->getVersion(),"2.1.0") < 0) {
	$this->getLogger()->warning(TextFormat::RED.mc::_("This version of SignWarp requires"));
	$this->getLogger()->warning(TextFormat::RED.mc::_("at least version 2.1.0 of WorldProtect"));
	$this->getLogger()->warning(TextFormat::RED.mc::_("Only version %1% available",$wp->getDescription()->getVersion()));
	$this->wp = NULL;
      }
    }
    $defaults =
       [
	 "version" => $this->getDescription()->getVersion(),
	 "# settings" => "configurable variables",
	 "settings" => [
	   "# dynamic updates" => "Signs will be udpated with the number of players in a world",
	   "dynamic-updates" => TRUE,
	   "# xyz.cmd" => "If true, the **xyz** command will be available",
	   "xyz.cmd" => FALSE,
	 ],
       ];

    $cfg = (new Config($this->getDataFolder()."config.yml",Config::YAML,$defaults))->getAll();

    if ($cfg["settings"]["xyz.cmd"]) {
      Cmd::add($this, $this, "xyz", [
	    "description" => mc::_("Returns x,y,z coordinates"),
	    "usage" => mc::_("/xyz"),
	    "permission" => "signwarp.cmd.xyz" ]);
      $this->getLogger()->info(TextFormat::GREEN.mc::_("enabled /xyz command"));
    }
    if ($cfg["settings"]["dynamic-updates"]) {
      $this->getLogger()->info(TextFormat::GREEN.mc::_("dynamic-updates: ON"));
      $tt = new PluginCallbackTask($this,[$this,"updateSigns"],[]);
      $this->getServer()->getScheduler()->scheduleRepeatingTask($tt,40);
    } else {
      $this->getLogger()->info(TextFormat::YELLOW.mc::_("dynamic-updates: OFF"));
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->warps = Warps::init($this);
    $this->warps->load();
  }

  //////////////////////////////////////////////////////////////////////
  //
  // Support functions
  //
  //////////////////////////////////////////////////////////////////////
  private function checkSign(Player $pl,array $sign) {
    if ($sign[0] == mc::_("[POS]")) {
      // Local teleport...
      if (empty($sign[1])) {
	$pl->sendMessage(mc::_("[SignWarp] No coordinates specified"));
	return NULL;
      }
      $mv = [];
      if ($this->check_coords($sign[1],$mv) !== TRUE) {
	$pl->sendMessage(mc::_("[SignWarp] Invalid coordinates %1%",$sign[1]));
	return NULL;
      }
      return new Position($mv[0],$mv[1],$mv[2],$pl->getLevel());
    }
    // Long teleport!
    if ($sign[0] == mc::_("[WORLD]")) {
      if (empty($sign[1])) {
	$pl->sendMessage(mc::_("[SignWarp] No World specified"));
	return NULL;
      }
      // Check level...
      list ($l,$err) = TPU::getLevelByName($this->getServer(), $sign[1]);
      if ($l === NULL) {
	$pl->sendMessage(mc::_("[SignWarp] %1", $err));
	return NULL;
      }
      $mv = [];
      if ($this->check_coords($sign[2],$mv)) {
	$mv = new Vector3($mv[0],$mv[1],$mv[2]);
      } else {
	$mv = NULL;
      }
      return $pos = $l->getSafeSpawn($mv);
    }
    // Long teleport!
    if ($sign[0] == mc::_("[WARP]")) {
      if (empty($sign[1])) {
	$pl->sendMessage(mc::_("[SignWarp] No Warp specified"));
	return NULL;
      }
      // Check warp...
      list($place,$perm) = $this->warps->get($sign[1]);
      if ($place == NULL) {
	$pl->sendMessage(mc::_("[SignWarp] Warp \"%1%\" not found!", $sign[1]));
	return NULL;
      }
      if ($perm != NULL && !Perms::access($pl, $perm)) return NULL;
      
      list ($x,$y,$z,$world) = $place;
      // Check level...
      list ($l,$err) = TPU::getLevelByName($this->getServer(), $world);
      if ($l === NULL) {
	$pl->sendMessage(mc::_("[SignWarp] %1", $err));
	return NULL;
      }
      return new Position($x,$y,$z,$l);
    }
    $pl->sendMessage(mc::_("[SignWarp] INTERNAL ERROR"));
    return null;
  }
  public function doBreakSign($tile) {
    $l = $tile->getLevel();
    $l->setBlockIdAt($tile->getX(),$tile->getY(),$tile->getZ(),Block::AIR);
    $l->setBlockDataAt($tile->getX(),$tile->getY(),$tile->getZ(),0);
    $tile->close();
  }
  public function breakSign(Player $pl,Sign $tile,$msg = "") {
    if ($msg != "") $pl->sendMessage($msg);
    $this->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this,[$this,"doBreakSign"],[$tile]),10
    );
  }
  private function check_coords($line,array &$vec) {
    $mv = array();
    if (!preg_match('/^\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s*$/',$line,$mv)) {
      return FALSE;
    }
    list($line,$x,$y,$z) = $mv;
    if ($y <= self::MIN_HEIGHT || $y >= self::MAX_HEIGHT) return FALSE;
    $vec = [$x,$y,$z];
    return TRUE;
  }
  private function matchCounter($txt) {
    $t = mc::_("Players:");
    if (substr($txt,0,strlen($t)) == $t) return $t;
    return FALSE;
  }
  private function isTpSign($txt) {
    return ($txt == mc::_("[POS]") || $txt == mc::_("[WORLD]") || $txt == mc::_("[WARP]"));
  }

  //////////////////////////////////////////////////////////////////////
  //
  // Internal command
  //
  //////////////////////////////////////////////////////////////////////
  public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
    switch ($cmd->getName()) {
      case "xyz":
	if (!Perms::inGame($sender)) return TRUE;
	$pos = $sender->getPosition();
	$sender->sendMessage(mc::_("You are at %1%,%2%,%3%",
				      intval($pos->getX()),
				      intval($pos->getY()),
				      intval($pos->getZ())));
	return TRUE;
    }
    return FALSE;
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Event Handlers
  //
  //////////////////////////////////////////////////////////////////////
  public function onQuit(PlayerQuitEvent $event) {
    $name = $event->getPlayer()->getName();
    if (isset($this->teleporters[$name])) unset($this->teleporters[$name]);
  }

  public function onBlockPlace(BlockPlaceEvent $event){
    $name = $event->getPlayer()->getName();
    if (isset($this->teleporters[$name])) {
      if (time() - $this->teleporters[$name] < 2)
	$event->setCancelled();
      else
	unset($this->teleporters[$name]);
    }
  }
  public function signChanged(SignChangeEvent $event){
    if($event->getBlock()->getId() != Block::SIGN_POST &&
	    $event->getBlock()->getId() != Block::WALL_SIGN) return;
    $pl = $event->getPlayer();
    $tile = $pl->getLevel()->getTile($event->getBlock());
    if(!($tile instanceof Sign))return;
    $sign = $event->getLines();

    if (!$this->isTpSign($sign[0])) return;

    if(!$pl->hasPermission("signwarp.place.sign")) {
      $this->breakSign($pl,$tile,mc::_("You are not allowed to make Warp signs"));
      return;
    }

    $pos = $this->checkSign($pl,$sign);
    if ($pos === null) {
      $this->breakSign($pl,$tile);
      return;
    }
    if ($pos instanceof Position) {
      if ($sign[0] == mc::_("[WORLD]")) {
	$this->getServer()->broadcastMessage(
	      mc::_("[SignWarp] World Portal to %1% created by %2%",
		    $pos->getLevel()->getName(),$pl->getName()));
      } elseif ($sign[0] == mc::_("[POS]")) {
	$this->getServer()->broadcastMessage(
	      mc::_("[SignWarp] Portal to %1%,%2%,%3% created by %4%",
		      $pos->getX(),$pos->getY(),$pos->getZ(),
		      $pl->getName()));
      } elseif ($sign[0] == mc::_("[WARP]")) {
	$this->getServer()->broadcastMessage(
	      mc::_("[SignWarp] Warp Portal to %1% created by %2%",
		      $sign[1],$pl->getName()));
      }
    }
  }
  public function playerTouchIt(PlayerInteractEvent $event){
    if($event->getBlock()->getId() != Block::SIGN_POST &&
	    $event->getBlock()->getId() != Block::WALL_SIGN) return;
    $pl = $event->getPlayer();
    $sign = $pl->getLevel()->getTile($event->getBlock());
    if(!($sign instanceof Sign)) return;
    $sign = $sign->getText();
    if (!$this->isTpSign($sign[0])) return;

    if(!$pl->hasPermission("signwarp.touch.sign")) {
      $pl->sendMessage(mc::_("Nothing happens..."));
      return;
    }
    if ($event->getItem()->getId() == Item::SIGN) {
      // Check if the user is holding a sign this stops teleports
      $pl->sendMessage(mc::_("Can not teleport while holding sign!"));
      return;
    }
    $pos = $this->checkSign($pl,$sign);
    if ($pos === NULL) return;

    if ($pos instanceof Position) {
      $this->teleporters[$pl->getName()] = time();

      $pl->sendMessage(mc::_("Teleporting..."));
      $pl->teleport($pos);
      return;
    }
  }
  //////////////////////////////////////////////////////////////////////
  //
  // Timed events
  //
  //////////////////////////////////////////////////////////////////////
  public function updateSigns() {
    foreach ($this->getServer()->getLevels() as $lv) {
      foreach ($lv->getTiles() as $tile) {
	if (!($tile instanceof Sign)) continue;
	$sign = $tile->getText();
	if($sign[0] != mc::_("[WORLD]")) continue;
	if (!($t = $this->matchCounter($sign[3]))) continue;
	if (($lv = $this->getServer()->getLevelByName($sign[1])) !== NULL) {
	  $cnt = count($lv->getPlayers());
	  $max = NULL;
	  if ($this->wp !== null) $max = $this->wp->getMaxPlayers($lv->getName());
	  if ($max == null)
	    $upd = $t. TextFormat::BLUE . $cnt;
	  else
	    $upd = $t . ($cnt>=$max ? TextFormat::RED : TextFormat::GREEN). $cnt . "/" . $max;
	} else {
	  $upd = $t.mc::_("N/A");
	}
	if ($upd == $sign[3]) continue;
	$tile->setText($sign[0],$sign[1],$sign[2],$upd);
      }
    }
  }
}
