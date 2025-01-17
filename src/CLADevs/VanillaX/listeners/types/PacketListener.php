<?php

namespace CLADevs\VanillaX\listeners\types;

use CLADevs\VanillaX\inventories\FakeBlockInventory;
use CLADevs\VanillaX\VanillaX;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class PacketListener implements Listener{

    public function onInventoryTransaction(InventoryTransactionEvent $event): void{
        VanillaX::getInstance()->getEnchantmentManager()->handleInventoryTransaction($event);
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void{
        if(!$event->isCancelled()){
            foreach($event->getPackets() as $packet){
                switch($packet::NETWORK_ID){
                    case ProtocolInfo::AVAILABLE_COMMANDS_PACKET:
                        if($packet instanceof AvailableCommandsPacket) $this->handleCommandEnum($packet);
                        break;
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        if(!$event->isCancelled() && ($player = $event->getOrigin()->getPlayer()) !== null){
            $packet = $event->getPacket();
            $window = $player->getCurrentWindow();

            if($window instanceof FakeBlockInventory && !$window->handlePacket($player, $packet)){
                $event->cancel();
            }
        }
    }

    /**
     * @param AvailableCommandsPacket $packet
     * Modifies enums for commands, arguments you see once you type /weather
     * are modified through this packet. '<clear: rain: thunder> [duration: int]'
     */
    private function handleCommandEnum(AvailableCommandsPacket $packet): void{
        foreach(VanillaX::getInstance()->getCommandManager()->getCommands() as $key => $command){
            if(($arg = $command->getCommandArg()) !== null && ($command = $packet->commandData[strtolower($key)] ?? null) !== null){
                $command->flags = $arg->getFlags();
                $command->permission = $arg->getPermission();
                $command->overloads = $arg->getOverload();
            }
        }
    }
}