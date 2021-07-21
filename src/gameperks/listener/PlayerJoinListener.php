<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener {

    /**
     * @param PlayerJoinEvent $ev
     *
     * @priority MONITOR
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $ev): void {
        $player = $ev->getPlayer();

        $player->getInventory()->setItem(4, GamePerks::getPerksItem());
    }
}