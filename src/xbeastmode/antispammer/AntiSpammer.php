<?php

namespace xbeastmode\antispammer;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as color;

class AntiSpammer extends PluginBase implements Listener
{
    /** @var array */
    private $m;
    /** @var object */
    private $p;
    /** @var PlayerChatEvent */
    private $ev;
    /** @var array */
    private $x = [];
    public function onEnable()
    {
        $this->getLogger()->info($this->par("%1%Plugin loaded!", color::GREEN));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    /**
     * @param $str
     * @return int
     */
    private function par(...$str)
    {
        $f = array_shift($str);
        if(isset($this->x[$f])) $f = $this->x[$f];
        if(count($str)) {
            $p = ["%%" => "%"];
            $int = 1;
            foreach($str as $par)
            {
                $p["%$int%"] = $par;
                $int++;
            }
            $f = strtr($f, $p);
        }
        return $f;
    }
    public function checkMessage()
    {
        if($this->isSpamming())
        {
            $this->p->sendMessage($this->par("%1%Please wait 3 seconds before chatting again.", color::RED));
            $this->ev->setCancelled();
        }
        else
        {
            $this->m = time();
        }
    }
    /**
     *@return bool 
     */
    public function isSpamming()
    {
        return (time() - $this->m < 3);
    }
    /**
     * @param PlayerChatEvent $e
     */
    public function onChat(PlayerChatEvent $e)
    {
        $msg = $e->getMessage();
        $this->p = $e->getPlayer();
        $this->ev = $e;
        $this->checkMessage();
    }
}
