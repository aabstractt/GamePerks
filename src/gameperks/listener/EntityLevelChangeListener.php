<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EntityLevelChangeListener implements Listener {

    /**
     * @param EntityLevelChangeEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onEntityLevelChangeEvent(EntityLevelChangeEvent $ev): void {
        $entity = $ev->getEntity();

        if (!$entity instanceof Player) {
            return;
        }

        if (!$entity->getAllowFlight()) {
            return;
        }

        if (!in_array($ev->getTarget()->getFolderName(), GamePerks::getInstance()->getConfig()->get('flyingWorldsAllowed', []), true)) {
            return;
        }

        $entity->setFlying(false);
        $entity->setAllowFlight(false);
    }
}