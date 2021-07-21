<?php

declare(strict_types=1);

namespace gameperks;

use gameperks\listener\PlayerInteractListener;
use gameperks\listener\PlayerJoinListener;
use gameperks\utils\Form;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class GamePerks extends PluginBase {

    /** @var GamePerks */
    private static $instance;

    /**
     * @return GamePerks
     */
    public static function getInstance(): GamePerks {
        return self::$instance;
    }

    public function onEnable(): void {
        self::$instance = $this;

        $this->saveDefaultConfig();

        $this->getServer()->getPluginManager()->registerEvents(new PlayerInteractListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
    }

    /**
     * @param Player   $player
     * @param callable $callback
     * @param array    $data
     * @param array    $buttons
     */
    public static function sendForm(Player $player, callable $callback, array $data, array $buttons = []): void {
        $buttons = array_merge($data['buttons'] ?? [], $buttons);

        foreach ($buttons as $buttonId => $buttonText) {
            $data['buttons'][$buttonId] = ['text' => $buttonText];
        }

        if (empty($data['buttons'])) {
            $data['buttons'] = [];
        }

        $player->sendForm(new Form($callback, $data));
    }

    /**
     * @return Item
     */
    public static function getPerksItem(): Item {
        return Item::get(Item::EMERALD)->setCustomName(TextFormat::RESET . TextFormat::GREEN . 'Perks');
    }
}