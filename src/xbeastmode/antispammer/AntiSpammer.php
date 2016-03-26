<?php namespace xbeastmode\antispammer;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
class AntiSpammer extends PluginBase implements Listener{
    private $players = [];
    private $warnings = [];
    private $muted = [];
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }
    public function isPlayerMuted(Player $p){
        return isset($this->muted[spl_object_hash($p)]);
    }
    public function unMutePlayer(Player $p){
        unset($this->muted[spl_object_hash($p)]);
    }
    public function onChat(PlayerChatEvent $e){
        if($e->getPlayer()->hasPermission("spam.bypass")) return;
        if(isset($this->muted[spl_object_hash($e->getPlayer())])){
            $e->getPlayer()->sendMessage(FMT::colorMessage($this->getConfig()->getAll(){"muted_message"}));
            $e->setCancelled();
            return;
        }
        if(isset($this->players[spl_object_hash($e->getPlayer())]) and
            (time() - $this->players[spl_object_hash($e->getPlayer())] <= intval($this->getConfig()->get("time")))){
            if(!isset($this->warnings[spl_object_hash($e->getPlayer())])){
                $this->warnings[spl_object_hash($e->getPlayer())] = 0;
            }
            ++$this->warnings[spl_object_hash($e->getPlayer())];
            $e->getPlayer()->sendMessage(str_replace("%warns%", $this->warnings[spl_object_hash($e->getPlayer())],
                FMT::colorMessage($this->getConfig()->getAll(){"warning_message"})));
            $e->setCancelled();
            if($this->warnings[spl_object_hash($e->getPlayer())] >= intval($this->getConfig()->get("max_warnings"))){
                if(strtolower($this->getConfig()->getAll(){"block_type"}) === "message"){
                    $e->getPlayer()->sendMessage(str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"message"})));
                    unset($this->warnings[spl_object_hash($e->getPlayer())]);
                    $e->setCancelled();
                }
                if(strtolower($this->getConfig()->getAll(){"block_type"}) === "mute"){
                    $this->muted[spl_object_hash($e->getPlayer())] = true;
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new MuteTask($this, $e->getPlayer()), 20*intval($this->getConfig()->get("mute_time")));
                    $e->getPlayer()->sendMessage(FMT::colorMessage($this->getConfig()->getAll(){"mute_message"}));
                    unset($this->players[spl_object_hash($e->getPlayer())]);
                    unset($this->warnings[spl_object_hash($e->getPlayer())]);
                    $e->setCancelled();
                }
                if(strtolower($this->getConfig()->getAll(){"block_type"}) === "kick"){
                    $e->getPlayer()->kick(str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"kick_reason"})));
                    unset($this->players[spl_object_hash($e->getPlayer())]);
                    $e->setCancelled();
                }
                if(strtolower($this->getConfig()->getAll(){"block_type"}) === "ban"){
                    $e->getPlayer()->kick(str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"ban_reason"})));
                    $this->getServer()->getNameBans()->addBan($e->getPlayer()->getName(), str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"ban_reason"})));
                    unset($this->warnings[spl_object_hash($e->getPlayer())]);
                    unset($this->players[spl_object_hash($e->getPlayer())]);
                    $e->setCancelled();
                }
                if(strtolower($this->getConfig()->getAll(){"block_type"}) === "ban-ip"){
                    $e->getPlayer()->kick(str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"ip_ban_reason"})));
                    $this->getServer()->getIPBans()->addBan($e->getPlayer()->getAddress(), str_replace("%player%", $e->getPlayer()->getName(), FMT::colorMessage($this->getConfig()->getAll(){"ip_ban_reason"})), null, $e->getPlayer()->getName());
                    unset($this->warnings[spl_object_hash($e->getPlayer())]);
                    unset($this->players[spl_object_hash($e->getPlayer())]);
                    $e->setCancelled();
                }
            }
        } else{
            $this->players[spl_object_hash($e->getPlayer())] = time();
        }
    }
}