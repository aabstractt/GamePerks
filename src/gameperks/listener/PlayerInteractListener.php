<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PlayerInteractListener implements Listener {

    /**
     * @param PlayerInteractEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $ev): void {
        $player = $ev->getPlayer();

        $item = $ev->getItem();
        $perkItem = GamePerks::getPerksItem();

        if ($item->getId() !== $perkItem->getId()) {
            return;
        }

        if ($item->getDamage() !== $perkItem->getDamage()) {
            return;
        }

        if ($item->getCustomName() !== $perkItem->getCustomName()) {
            return;
        }

        $buttons = GamePerks::getInstance()->getConfig()->get('perks');

        GamePerks::sendForm($player, function (Player $player, ?int $data) use($buttons): void {
            if ($data === null) {
                return;
            }

            $button = array_values($buttons)[$data] ?? [];

            if (empty($button)) {
                return;
            }

            if ($button['action'] == 'toggleFly') {
                $player->setFlying(!$player->isFlying());

                $player->setAllowFlight(!$player->getAllowFlight());

                $player->sendMessage('Fly ' . ($player->getAllowFlight() ? 'enabled' : 'disabled'));
            } else if ($button['action'] == 'nick') {
                $player->sendMessage(TextFormat::RED . 'MMMM');
            } else if ($button['action'] == 'form') {
                GamePerks::sendForm($player, function (Player $player, ?int $data): void {

                }, $button);
            }
        }, ['type' => 'form', 'title' => 'title', 'content' => 'content'], array_keys($buttons));

        $ev->setCancelled();
    }
}