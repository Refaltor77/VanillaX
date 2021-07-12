<?php

namespace CLADevs\VanillaX\weather;

use CLADevs\VanillaX\VanillaX;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\World;

class Weather{

    const TAG_WEATHER = "WeatherData";
    const TAG_DURATION = "duration";
    const TAG_DELAY_DURATION = "DelayDuration";
    const TAG_THUNDERING = "Thundering";

    private World $world;

    private bool $raining = false;
    private bool $thundering = false;

    public int $duration = 0;
    public int $delayDuration = 0;

    public function __construct(World $world){
        $this->world = $world;
        $provider = $world->getProvider();

        $this->recalculateDelayDuration();

        if($provider instanceof WritableWorldProvider){
            $nbt = $provider->getLevelData();

            if($nbt->hasTag(self::TAG_WEATHER)){
                /** @var CompoundTag $tag */
                $tag = $nbt->getTag(self::TAG_WEATHER);

                if($tag->hasTag(self::TAG_DURATION)){
                    $this->duration = $tag->getInt(self::TAG_DURATION);
                }
                if($tag->hasTag(self::TAG_DELAY_DURATION)){
                    $this->delayDuration = $tag->getInt(self::TAG_DELAY_DURATION);
                }
                if($tag->hasTag(self::TAG_THUNDERING)){
                    $this->thundering = boolval($tag->getByte(self::TAG_THUNDERING));
                }
                if($this->duration >= 1){
                    $this->startStorm($this->thundering, $this->duration);
                }
            }
        }
    }

    public function getWorld(): World{
        return $this->world;
    }

    public function isRaining(): bool{
        return $this->raining;
    }

    public function isThundering(): bool{
        return $this->thundering;
    }

    public function saveData(): void{
        if($this->world->isClosed()){
            return;
        }
        $provider = $this->world->getProvider();

        if($provider instanceof WritableWorldProvider){
            $nbt = $provider->getLevelData();
            $nbt->setTag(new CompoundTag(self::TAG_WEATHER, [
                new IntTag(self::TAG_DURATION, $this->duration),
                new IntTag(self::TAG_DELAY_DURATION, $this->delayDuration),
                new ByteTag(self::TAG_THUNDERING, $this->thundering),
            ]));
            $provider->saveLevelData();
        }
    }

    public function startStorm(bool $thunder = false, int $duration = null): void{
        VanillaX::getInstance()->getWeatherManager()->sendWeather(null, $thunder);
        $this->duration = $duration == null ? mt_rand(600, 1200) : $duration;
        $this->raining = true;
        $this->thundering = $thunder;
        $this->saveData();
    }

    public function stopStorm(): void{
        VanillaX::getInstance()->getWeatherManager()->sendClear(null, $this->thundering);
        $this->recalculateDelayDuration();
        $this->duration = 0;
        $this->raining = false;
        $this->thundering = false;
        $this->saveData();
    }

    public function recalculateDelayDuration(): void{
        $this->delayDuration = mt_rand(600, 9000);
    }
}