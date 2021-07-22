<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use gameperks\provider\YamlProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class PlayerChatListener implements Listener {

    /**
     * @param PlayerChatEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onPlayerChatEvent(PlayerChatEvent $ev): void {
        $player = $ev->getPlayer();

        $currentColour = YamlProvider::getInstance()->getChatColour($player);

        if ($currentColour === null) {
            return;
        }

        $ev->setFormat($player->getName() . ': ' . GamePerks::colorize($currentColour, $ev->getMessage()));
    }
}