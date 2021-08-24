<?php

namespace JunKR;

use pocketmine\block\SignPost;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class SignPaint extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onClick(PlayerInteractEvent $ev){
        $player = $ev->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if($item->getId() <= 10065 or $item->getId() >= 10074){
            return;
        }

        $type = $item->getId() - 10066;
        $colors = ["1", "2", "6", "9", "b", "c", "d", "e"];

        $block = $ev->getBlock();
        if(!$block instanceof SignPost){
            return;
        }
        $tile = $block->getLevel()->getTile($block->asVector3());
        if(!$tile instanceof Sign){
            return;
        }

        $arr = [];
        foreach($tile->getText() as $key => $value){
            $cl = TextFormat::clean($value);
            if($cl !== $value){
                return;
            }
            $arr[$key] = "§" . ($colors[$type] ?? "") . $cl;
        }

        $item->setCount(1);
        $player->getInventory()->removeItem($item);
        $tile->setText($arr[0] ?? "", $arr[1] ?? "", $arr[2] ?? "", $arr[3] ?? "");
        $bpk = new BatchPacket();
        $pk = new LevelEventPacket;
        $pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | Particle::TYPE_EXPLODE;
        $pk->position = $block->add(0.5, 0.5, 0.5);
        $pk->data = 10;
        $bpk->addPacket($pk);
        $bpk->addPacket($pk);
        $bpk->addPacket($pk);
        $bpk->addPacket($pk);
        $bpk->addPacket($pk);
        $bpk->addPacket($pk);
        $bpk->setCompressionLevel(7);
        $bpk->encode();
        foreach($block->getLevel()->getPlayers() as $bplayer){
            $bplayer->sendDataPacket($bpk);
        }
    }
}