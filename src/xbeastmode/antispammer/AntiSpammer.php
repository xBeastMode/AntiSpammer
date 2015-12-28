<?php
namespace antispam;
    use pocketmine\Player;
    use pocketmine\plugin\PluginBase;
    use pocketmine\event\player\PlayerChatEvent;
    use pocketmine\utils\TextFormat;
    use pocketmine\event\Listener;
    class AntiSpam extends PluginBase implements Listener{
        private $players = [];
        public function onEnable(){
            $this->getLogger()->info(TextFormat::GREEN."[AntiSpam] Plugin loaded!");
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $this->saveDefaultConfig();
        }
        public function sendMessage(Player $p){
            if($p->isOp() || $p->hasPermission("spam.bypass")) return false;
            if($this->timeCheck($p)){
                $p->sendMessage(str_replace("%player%", $p->getName(),
                    $this->getConfig()->get("message")));
                return true;
            }
            else{
                $this->players[$p->getName()] = time();
                return false;
            }
        }
        public function timeCheck(Player $p){
            if(isset($this->players[$p->getName()])) {
                if (time() - $this->players[$p->getName()] <= intval($this->getConfig()->get("time"))) {
                    return true;
                }
            }
            return false;
        }
        public function onChat(PlayerChatEvent $e){
            if($this->sendMessage($e->getPlayer())){
                $e->setCancelled();
            }
        }
    }
