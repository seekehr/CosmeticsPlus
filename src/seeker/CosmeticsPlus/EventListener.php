<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\InkParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use seeker\CosmeticsPlus\cosmetics\FaceMask;
use seeker\CosmeticsPlus\cosmetics\SkinFactory;
use seeker\CosmeticsPlus\cosmetics\Vehicle;
use seeker\CosmeticsPlus\entities\Grave;
use seeker\CosmeticsPlus\morphs\BlazeMorph;
use seeker\CosmeticsPlus\morphs\ChickenMorph;
use seeker\CosmeticsPlus\tasks\SpawnHeadParticleTask;
use seeker\CosmeticsPlus\utils\ParticleManager;
use seeker\CosmeticsPlus\utils\PlayerSkinManager;

class EventListener implements Listener
{

	/**
	 * @var CosmeticsPlus
	 */
	private $main;

	/**
	 * @var array<string>
	 */
	public static $sameSkin = [];

	public function __construct(CosmeticsPlus $main)
	{
		$this->main = $main;
	}

	public function onJoin(PlayerJoinEvent $event) : void
	{
		$player = $event->getPlayer();
		$this->main->getDataProvider()->registerPlayer($player);
		$player->sendMessage($this->main->getSettings()->get('registeration-message'));
		$this->initializePlayerByDatabase($player);
		if(isset(PlayerSKinManager::$skins[$player->getName()])){
			if(PlayerSkinManager::$skins[strtolower($player->getName())][0] === $player->getSkin()->getSkinData()) self::$sameSkin[] = strtolower($player->getName());
		}
	}

	private function initializePlayerByDatabase(Player $player) : void
	{
		if($this->main->getDataProvider()->getPlayerFaceMask($player)){
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-face-mask-worlds'))) return;
			$skin = SkinFactory::get('facemask', $this->main->getDataProvider()->getPlayerFaceMask($player));
			if($skin instanceof Skin){
				$nbt = Entity::createBaseNBT($player->asVector3());
				$nbt->setTag(new CompoundTag("Skin", [
					"Name" => new StringTag("Name", $skin->getSkinId()),
					"Data" => new ByteArrayTag("Data", $skin->getSkinData()),
					"CapeData" => new ByteArrayTag("CapeData", $skin->getCapeData()),
					"GeometryName" => new StringTag("GeometryName", $skin->getGeometryName()),
					"GeometryData" => new ByteArrayTag("GeometryData", $skin->getGeometryData())
				]));
				$facemask = new FaceMask($this->main, $player, $player->getLevel(), $nbt);
				$facemask->spawnToAll();
			}
		}
		if($this->main->getDataProvider()->getPlayerParticle($player)[1] !== '' && $this->main->getDataProvider()->getPlayerParticle($player)[1] !== null){
			$name = $this->main->getDataProvider()->getPlayerParticle($player)[1];
			$shape = $this->main->getDataProvider()->getPlayerParticle($player)[0];
			$particle = ParticleManager::get($shape, $name)[0];
			$height = ParticleManager::get($shape, $name)[1];
			if(($task = ParticleManager::getTaskByShape($shape)) !== null){
				$eligible_viewers = [];
				foreach(Server::getInstance()->getOnlinePlayers() as $viewer){
					if($this->main->getDataProvider()->canSeeParticles($viewer)) $eligible_viewers[] = $viewer;
				}
				$this->main->getDataProvider()->setPlayerParticle($player, $shape, $name);
				if(isset($this->particleTasks[$player->getName()])){
					$this->main->getScheduler()->cancelTask((int) $this->main->getFormManager()->particleTasks[$player->getName()]);
					unset($this->main->getFormManager()->particleTasks[$player->getName()]);
				}
				if($task === SpawnHeadParticleTask::class){
					/** @var Task $task */
					$this->main->getScheduler()->scheduleRepeatingTask(($task = new $task($player, $particle, $eligible_viewers, $height)), 20);
				} else {
					/** @var Task $task */
					$this->main->getScheduler()->scheduleRepeatingTask(($task = new $task($player, $particle, $eligible_viewers, $height)), 2);
				}
				$this->main->getFormManager()->particleTasks[$player->getName()] = $task->getTaskId();
			} else {
				$player->sendMessage(TextFormat::RED . "e.e... Invalid particle shape!");
				$player->sendMessage(TextFormat::YELLOW . "Shape => $shape\nName => $name\nParticle => $particle\nHeight =>  $height");
				$player->sendMessage(TextFormat::GRAY . TextFormat::BOLD . "Please send a screenshot of this message to the owner!");
			}
		}
	}


	public function onMove(PlayerMoveEvent $event) : void
	{
		$player = $event->getPlayer();
		$trail = $this->main->getDataProvider()->getPlayerMovingTrail($player);
		if($trail === null) return;
		if($this->main->getPerformanceSettings()->get('no-sneak-trail') == true){
			if($player->isSneaking()) return;
		}
		switch($trail){
			case 'AVT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new AngryVillagerParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'ST':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new SmokeParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'CT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new CriticalParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'SBT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new SnowballPoofParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'ET':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new EnchantmentTableParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'WT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new SplashParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'BT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$blocks = [Block::BEACON, Block::STONE, Block::HAY_BALE, Block::BEDROCK];
				$player->getLevel()->addParticle(new DestroyBlockParticle($player->getLocation()->asVector3(), Block::get($blocks[array_rand($blocks)])), $players);
				break;
			case 'LT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new LavaParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'HT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new HeartParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'IT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new InkParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'RDT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new RedstoneParticle($player->getLocation()->asVector3()), $players);
				break;
			case 'PT':
				/** @var Player[] $players */
				$players = [];
				foreach($player->getViewers() as $viewer){
					if($this->main->getDataProvider()->canSeeTrails($viewer)) $players[] = $player;
				}
				$players[] = $player;
				$player->getLevel()->addParticle(new PortalParticle($player->getLocation()->asVector3()), $players);
				break;
			default:
				break;
		}
	}

	public function onDamage(EntityDamageEvent $event) : void
	{
		if(isset(class_uses($event->getEntity())['seeker\CosmeticsPlus\morphs\Morph']) || isset(class_uses($event->getEntity())['seeker\CosmeticsPlus\entities\EntityTrait'])) {
			$event->setCancelled();
		}
	}

	public function onSneak(PlayerToggleSneakEvent $event) : void
	{
		if($this->main->getDataProvider()->isRegisteredPlayer(($player = $event->getPlayer())->getName())){
			if(isset($this->main->morphs[strtolower($player->getName())]) && $this->main->getDataProvider()->canSeeMorphs($player)){
				$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
				if(isset(class_uses($entity)['seeker\CosmeticsPlus\morphs\Morph'])){
					if($entity instanceof BlazeMorph){
						$player->getLevel()->addSound(new BlazeShootSound($player->asVector3()), $entity->getViewers());
					}
					if($entity instanceof ChickenMorph){
						$player->getLevel()->dropItem($entity->asVector3(), Item::get(Item::EGG));
					}
					if($entity instanceof BlazeMorph){
						$player->getLevel()->addSound(new BlazeShootSound($player->asVector3()), $entity->getViewers());
					}
				}
			}
		}
	}

	public function onDataPacket(DataPacketReceiveEvent $event) : void
	{
		if(!isset($this->main->getFormManager()->vehicle[$event->getPlayer()->getName()])) return;
		if($event->getPacket() instanceof InteractPacket){
			/** @var InteractPacket $packet */
			$packet = $event->getPacket();
			if($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE){
				$player = $event->getPlayer();
				$entity = $player->getLevel()->getEntity($this->main->getFormManager()->vehicle[$player->getName()]);
				$entity->flagForDespawn();
			}
		}
		if($event->getPacket() instanceof PlayerInputPacket){
			/** @var PlayerInputPacket $packet */
			$packet = $event->getPacket();
			$event->setCancelled();
			if($packet->motionX === 0.0 and $packet->motionY === 0.0) {
				return;
			}
			$player = $event->getPlayer();
			$entity = $player->getLevel()->getEntity($this->main->getFormManager()->vehicle[$player->getName()]);
			if($entity instanceof Vehicle){
				if(strtolower($player->getName()) === strtolower($entity->getOwner()->getName())){
					if($player->hasPermission('vehicle.fly')) $entity->updateMotion($packet->motionX, $packet->motionY);
					else $entity->updateMotion($packet->motionX, 0);
				}
			}
		}
	}
	public function onDeath(PlayerDeathEvent $event) : void
	{
		$player = $event->getPlayer();
		if($this->main->getSettings()->get("all-grave-worlds") == 'true' || in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-grave-worlds'))){
			if(in_array($this->main->getDataProvider()->getPlayerGrave($player), $this->main->getSettings()->getNested('available-graves'))){
				if(($skin = SkinFactory::get('grave', $this->main->getDataProvider()->getPlayerGrave($player))) instanceof Skin) {
					$nbt = Entity::createBaseNBT($player->asVector3());
					$nbt->setTag(new CompoundTag("Skin", [
						"Name" => new StringTag("Name", $skin->getSkinId()),
						"Data" => new ByteArrayTag("Data", $skin->getSkinData()),
						"CapeData" => new ByteArrayTag("CapeData", $skin->getCapeData()),
						"GeometryName" => new StringTag("GeometryName", $skin->getGeometryName()),
						"GeometryData" => new ByteArrayTag("GeometryData", $skin->getGeometryData())
					]));
					$grave = new Grave($this->main, $player, $player->getLevel(), $nbt);
					$grave->spawnToAll();
				}
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @priority LOW
	 */
	public function onQuit(PlayerQuitEvent $event) : void
	{
		$player = $event->getPlayer();
		$this->main->getDataProvider()->saveData($player->getName());
		if(isset($this->main->morphs[strtolower($player->getName())])){
			$player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())])->flagForDespawn();
			unset($this->main->morphs[strtolower($player->getName())]);
		}
		if(isset($this->main->getFormManager()->particleTasks[$player->getName()])){
			$this->main->getScheduler()->cancelTask((int) $this->main->getFormManager()->particleTasks[$player->getName()]);
			unset($this->main->getFormManager()->particleTasks[$player->getName()]);
		}
	}
}
