<?php

declare(strict_types=1);

namespace gameperks\provider;

use gameperks\GamePerks;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class YamlProvider {

    /** @var YamlProvider */
    private static $instance;
    /** @var Config */
    private $config;

    /**
     * @return YamlProvider
     */
    public static function getInstance(): YamlProvider {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void {
        $this->config = new Config(GamePerks::getInstance()->getDataFolder() . 'players.yml');
    }

    /**
     * @param Player $player
     * @param bool   $flying
     */
    public function setFlying(Player $player, bool $flying): void {
        $players = $this->config->get('playersFlying', []);

        $uuid = $player->getUniqueId()->toString();

        if (in_array($uuid, $players, true) && !$flying) {
            $players = array_diff($players, [$uuid]);
        } else if ($flying) {
            $players[] = $uuid;
        }

        $this->config->set('playersFlying', $players);
        $this->config->save();

        unset($players);
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function isFlying(Player $player): bool {
        return in_array($player->getUniqueId()->toString(), $this->config->get('playersFlying', []), true);
    }

    /**
     * @param Player      $player
     * @param string|null $colorName
     */
    public function setChatColour(Player $player, ?string $colorName): void {
        $players = $this->config->get('playersColour', []);

        $uuid = $player->getUniqueId()->toString();

        if ($colorName === null && isset($players[$uuid])) {
            unset($players[$uuid]);
        } else if ($colorName !== null) {
            $players[$uuid] = TextFormat::clean($colorName);
        }

        $this->config->set('playersColour', $players);
        $this->config->save();
    }

    /**
     * @param Player $player
     *
     * @return string|null
     */
    public function getChatColour(Player $player): ?string {
        return $this->config->get('playersColour', [])[$player->getUniqueId()->toString()] ?? null;
    }

    /**
     * @param Player      $player
     * @param string|null $killMessage
     */
    public function setKillMessage(Player $player, ?string $killMessage): void {
        $players = $this->config->get('playersKill', []);

        $uuid = $player->getUniqueId()->toString();

        if ($killMessage === null && isset($players[$uuid])) {
            unset($players[$uuid]);
        } else if ($killMessage !== null) {
            $players[$uuid] = $killMessage;
        }

        $this->config->set('playersKill', $players);
        $this->config->save();
    }

    /**
     * @param Player $player
     *
     * @return string|null
     */
    public function getKillMessage(Player $player): ?string {
        return $this->config->get('playersKill', [])[$player->getUniqueId()->toString()] ?? null;
    }

    /**
     * @param Player      $player
     * @param string|null $particleName
     */
    public function setParticle(Player $player, ?string $particleName): void {
        $players = $this->config->get('playersParticle', []);

        $uuid = $player->getUniqueId()->toString();

        if ($particleName === null && isset($players[$uuid])) {
            unset($players[$uuid]);
        } else if ($particleName !== null) {
            $players[$uuid] = $particleName;
        }

        $this->config->set('playersParticle', $players);
        $this->config->save();
    }

    /**
     * @param Player $player
     *
     * @return string|null
     */
    public function getParticle(Player $player): ?string {
        return $this->config->get('playersParticle', [])[$player->getUniqueId()->toString()] ?? null;
    }
}