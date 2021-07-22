<?php

declare(strict_types=1);

namespace gameperks\listener;

use gameperks\GamePerks;
use gameperks\provider\YamlProvider;
use InvalidArgumentException;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class ProjectileLaunchListener implements Listener {

    /**
     * @param ProjectileLaunchEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onProjectileLaunchEvent(ProjectileLaunchEvent $ev): void {
        $projectile = $ev->getEntity();

        if (!$projectile instanceof Projectile) {
            return;
        }

        $target = $projectile->getOwningEntity();

        if (!$target instanceof Player) {
            return;
        }

        $particleString = YamlProvider::getInstance()->getParticle($target);

        if ($particleString == null) {
            return;
        }

        $particleString = mb_strtoupper('TYPE_' . str_replace(' ', '_', $particleString));

        $constString = Particle::class . "::" . $particleString;

        if (!defined($constString)) {
            throw new InvalidArgumentException('Invalid Particle ' . $particleString);
        }

        GamePerks::getInstance()->getScheduler()->scheduleRepeatingTask(new ParticleTask($projectile, $constString), 10);
    }
}

class ParticleTask extends Task {

    /** @var Projectile */
    private $projectile;
    /** @var string */
    private $constString;

    /**
     * ParticleTask constructor.
     *
     * @param Projectile $projectile
     * @param string     $constString
     */
    public function __construct(Projectile $projectile, string $constString) {
        $this->projectile = $projectile;

        $this->constString = $constString;
    }

    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun(int $currentTick) {
        $projectile = $this->projectile;

        if ($projectile == null || $projectile->isClosed() || $projectile->onGround) {
            if(($handler = $this->getHandler()) !== null) {
                $handler->cancel();
            }

            return;
        }

        $projectile->getLevelNonNull()->addParticle(new GenericParticle($projectile->asPosition(), constant($this->constString)));
    }
}