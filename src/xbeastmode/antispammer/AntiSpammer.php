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
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $this->saveDefaultConfig();
        }
        public function onChat(PlayerChatEvent $e){
            if($p->isOp() || $p->hasPermission("spam.bypass")) return;
            if(isset($this->players[spl_object_hash($p)]) and (time() - $this->players[$p->getName()] <= intval($this->getConfig()->get("time")))){
                $p->sendMessage(str_replace("%player%", $p->getName(),
                    $this->getConfig()->get("message")));
                $e->setCancelled();
            }
            else{
                $this->players[spl_object_hash($p)] = time();
            }
        }
    }
