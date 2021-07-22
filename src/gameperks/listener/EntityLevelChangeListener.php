<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use gameperks\provider\YamlProvider;
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

            $provider = YamlProvider::getInstance();

            if ($button['action'] == 'toggleFly') {
                $provider->setFlying($player, ($flying = !$provider->isFlying($player)));

                if (!in_array($player->getLevelNonNull()->getFolderName(), GamePerks::getInstance()->getConfig()->get('flyingWorldsAllowed', []), true)) {
                    return;
                }

                $player->setFlying($flying);
                $player->setAllowFlight($flying);

                $player->sendMessage('Fly ' . ($flying ? 'enabled' : 'disabled'));
            } else if ($button['action'] == 'nick') {
                $player->sendMessage(TextFormat::RED . 'MMMM');
            } else if (isset($button['type'])) {
                $button['content'] = str_replace(['$currentColour', '$currentMessage', '$currentParticle'], [GamePerks::colorize($provider->getChatColour($player)) ?? '', $provider->getKillMessage($player) ?? '', 'empty'], $button['content']);

                $action = $button['action'];

                if ($action === 'chatColour') {
                    $currentColour = $provider->getChatColour($player);

                    foreach ($button['buttons'] as $buttonId => $buttonText) {
                        $newButtonText = GamePerks::colorize($buttonText);

                        if ($currentColour !== null && strtolower($currentColour) == strtolower($buttonText)) {
                            $newButtonText .= TextFormat::GREEN . TextFormat::BOLD . ' -  SELECTED';
                        }

                        $button['buttons'][$buttonId] = $newButtonText ?? $buttonText;
                    }
                } else if ($action === 'particles') {
                    $currentParticle = $provider->getParticle($player);

                    foreach ($button['buttons'] as $buttonId => $buttonText) {
                        if ($currentParticle !== null && strtolower($currentParticle) == strtolower($buttonText)) {
                            $buttonText .= TextFormat::GREEN . TextFormat::BOLD . ' -  SELECTED';
                        }

                        $button['buttons'][$buttonId] = $buttonText;
                    }
                }

                $newButtons = $button['buttons'];

                GamePerks::sendForm($player, function (Player $player, ?int $data) use ($action, $newButtons): void {
                    if ($data === null) {
                        return;
                    }

                    $text = $newButtons[$data] ?? null;

                    if ($text == null) {
                        return;
                    }

                    if (!$player->hasPermission('perks.' . strtolower($action))) {
                        $player->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this.');

                        return;
                    }

                    $provider = YamlProvider::getInstance();

                    $text = TextFormat::clean($text);

                    if ($action === 'chatColour') {
                        $provider->setChatColour($player, GamePerks::colorize($text, $text));
                    } else if ($action === 'killMessage') {
                        $provider->setKillMessage($player, $text);
                    } else if ($action === 'particles') {
                        $provider->setParticle($player, $text);
                    }
                }, $button);
            }
        }, ['type' => 'form', 'title' => 'title', 'content' => 'content'], array_keys($buttons));

        $ev->setCancelled();
    }
}