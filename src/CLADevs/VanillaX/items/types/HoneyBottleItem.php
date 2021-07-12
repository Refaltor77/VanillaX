<?php

namespace CLADevs\VanillaX\items\types;

use CLADevs\VanillaX\items\ItemIdentifiers;
use pocketmine\entity\Effect;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\item\Consumable;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class HoneyBottleItem extends Item implements Consumable{

    public function __construct(int $meta = 0){
        parent::__construct(ItemIdentifiers::HONEY_BOTTLE, $meta, "Honey Bottle");
    }

    public function onConsume(Living $consumer): void{
        $consumer->removeEffect(Effect::POISON);

        if($consumer instanceof Human){
            $consumer->addFood(3);
            $consumer->addSaturation(1.2);
        }
    }

    public function getMaxStackSize(): int{
        return 16;
    }

    public function getResidue(): Item{
        return ItemFactory::getInstance()->get(ItemIds::GLASS_BOTTLE);
    }

    public function getAdditionalEffects(): array{
        return [];
    }
}