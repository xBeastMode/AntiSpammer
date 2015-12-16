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
    private $time;
    /** @var PlayerChatEvent */
    private $ev;
    /** @var array */
    private $x = [];
    /** @var array */
    private $spams = [];
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
    /**
     * checks message for any flooding
     *
     * @param Player $player
     */
    public function checkMessage(Player $player)
    {
        if($this->isSpamming() && !$player->isOp())
        {
            if(!isset($this->spams[$player->getName()]) && !$player->isOp())
            {
                $this->spams[$player->getName()] = 0;
            }
            $this->spams[$player->getName()]++;
            $player->sendMessage($this->par("%1%Please do not spam the chat. Warnings: " . $this->spams[$player->getName()] . ".\n%2%* If your warnings are 3 you will be kicked.", color::RED, color::AQUA));
            $this->ev->setCancelled();
            if($this->spams[$player->getName()] === 3)
            {
                $this->getServer()->broadcastMessage($this->par("%1%[Server] kicked %2%. Reason: spamming the chat.", color::RED, $player->getName()));
                $player->kick($this->par("%1%\nSpamming the chat.", color::RED));
                unset($this->spams[$player->getName()]);
            }
        }
        else
        {
            $this->time = time();
        }
    }
    /**
     * if the current time minus the time the message was sent
     * is less than 2 seconds it will be considered spam
     *
     *@return bool
     */
    public function isSpamming()
    {
        return (time() - $this->time < 2);
    }
    /**
     * @param PlayerChatEvent $e
     */
    public function onChat(PlayerChatEvent $e)
    {
        $msg = $e->getMessage();
        $this->p = $e->getPlayer();
        $this->ev = $e;
        $this->checkMessage($e->getPlayer());
    }
}
