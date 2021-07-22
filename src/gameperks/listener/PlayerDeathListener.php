<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use gameperks\provider\YamlProvider;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

class PlayerDeathListener implements Listener {

    /**
     * @param PlayerDeathEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerDeathEvent(PlayerDeathEvent $ev): void {
        $player = $ev->getPlayer();

        $lastCause = $player->getLastDamageCause();

        if ($lastCause instanceof EntityDamageByEntityEvent) {
            $target = $lastCause->getDamager();

            if (!$target instanceof Player) {
                return;
            }

            if (!in_array($player->getLevelNonNull()->getFolderName(), GamePerks::getInstance()->getConfig()->get('deathWorldsAllowed', []), true)) {
                return;
            }

            $killMessage = YamlProvider::getInstance()->getKillMessage($target);

            if ($killMessage === null) {
                return;
            }

            $formatDeathMessage = GamePerks::getInstance()->getConfig()->getNested('death-format.' . $killMessage);

            if ($formatDeathMessage === null) {
                return;
            }

            $formatDeathMessage = str_replace(['$deathPlayer', '$deathHealth', '$killerPlayer', '$killerHealth'], [$player->getNameTag(), (string)$player->getHealth(), $target->getNameTag(), (string)$target->getHealth()], $formatDeathMessage);

            $ev->setDeathMessage($formatDeathMessage);
        }
    }
}