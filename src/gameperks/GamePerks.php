<?php

declare(strict_types=1);

namespace gameperks;

use gameperks\listener\PlayerChatListener;
use gameperks\listener\PlayerDeathListener;
use gameperks\listener\EntityLevelChangeListener;
use gameperks\listener\ProjectileLaunchListener;
use gameperks\provider\YamlProvider;
use gameperks\utils\Form;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use ReflectionClass;

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

        YamlProvider::getInstance()->init();

        $server = $this->getServer();

        $server->getCommandMap()->register(PerksCommand::class, new PerksCommand('perks'));

        $server->getPluginManager()->registerEvents(new ProjectileLaunchListener(), $this);
        $server->getPluginManager()->registerEvents(new EntityLevelChangeListener(), $this);
        $server->getPluginManager()->registerEvents(new PlayerChatListener(), $this);
        $server->getPluginManager()->registerEvents(new PlayerDeathListener(), $this);
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
     * @param string|null $colorName
     * @param string|null $toColorize
     *
     * @return string
     */
    public static function colorize(?string $colorName, string $toColorize = null): ?string {
        if ($colorName === null) {
            return null;
        }

        $name = mb_strtoupper($colorName);

        $constString = TextFormat::class . "::" . $name;

        if ($toColorize === null) {
            $toColorize = $colorName;
        }

        if (!defined($constString)) {
            if ($colorName !== 'Rainbow') {
                return null;
            }

            $colors = (new ReflectionClass(TextFormat::class))->getConstants();

            unset($colors['ESCAPE'], $colors['EOL'], $colors['OBFUSCATED'], $colors['BOLD'], $colors['STRIKETHROUGH'], $colors['UNDERLINE'], $colors['ITALIC'], $colors['RESET']);

            $colors = array_values($colors);

            shuffle($colors);

            $message = TextFormat::RESET;

            $strSplit = self::$instance->str_split($toColorize); //TODO second parameter for split slider

            foreach ($strSplit as $i => $letter) {
                if ($letter === ' ') {
                    $message .= $letter;
                } else {
                    $message .= $colors[$i % count($colors)] . $letter;
                }
            }

            return $message;
        }

        return constant($constString) . $toColorize;
    }

    /**
     * @param string $str
     *
     * @return array
     */
    private function str_split(string $str): array {
        $tmp = preg_split('~~u', $str, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = array_chunk($tmp, 1);

        foreach ($chunks as $i => $chunk) {
            $chunks[$i] = join('', (array) $chunk);
        }

        return $chunks;
    }
}