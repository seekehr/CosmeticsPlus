<?php

declare(strict_types=1);

namespace seeker;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use seeker\cosmetics\FaceMask;
use seeker\cosmetics\SkinFactory;
use seeker\cosmetics\Vehicle;
use seeker\morphs\BlazeMorph;
use seeker\morphs\ChickenMorph;
use seeker\morphs\CreeperMorph;
use seeker\morphs\EnderDragonMorph;
use seeker\morphs\EndermanMorph;
use seeker\morphs\IronGolemMorph;
use seeker\morphs\PhantomMorph;
use seeker\morphs\PigMorph;
use seeker\morphs\SkeletonMorph;
use seeker\morphs\SlimeMorph;
use seeker\morphs\SpiderMorph;
use seeker\morphs\VillagerMorph;
use seeker\morphs\WitchMorph;
use seeker\morphs\WitherMorph;
use seeker\morphs\ZombieMorph;
use seeker\tasks\Spawn2DVerticleCircleTask;
use seeker\tasks\SpawnHeadParticleTask;
use seeker\utils\BugReport;
use seeker\utils\ColourRandomizer;
use seeker\utils\ParticleManager;
use seeker\utils\PlayerSkinManager;
use xenialdan\skinapi\API;

class FormManager
{

	private $main;

	private $faceMask = [];

	public $vehicle = [];

	/**
	 * @var array<string, int>
	 */
	public $particleTasks = [];

	public function __construct(CosmeticsPlus $main)
	{
		$this->main = $main;
	}

	public function sendTrailsForm(Player $player, int $page = 1) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null) use ($page){
			if($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-trail-worlds')) && $this->main->getSettings()->get('all-trail-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$provider = $this->main->getDataProvider();
			switch($data){
				case 0:
					if($page === 1){
						$provider->setPlayerMovingTrail($player, 'AVT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					elseif($page === 2){
						$provider->setPlayerMovingTrail($player, 'ST');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 1:
					if($page === 1) {
						$provider->setPlayerMovingTrail($player, 'CT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					elseif($page === 2){
						$provider->setPlayerMovingTrail($player, 'SBT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 2:
					if($page === 1){
						$provider->setPlayerMovingTrail($player, 'ET');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					elseif($page === 2){
						$provider->setPlayerMovingTrail($player, 'WT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 3:
					if($page === 1){
						$provider->setPlayerMovingTrail($player, 'BT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					elseif($page === 2){
						$provider->setPlayerMovingTrail($player, 'LT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 4:
					if($page === 1){
						$provider->setPlayerMovingTrail($player, 'HT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					elseif($page === 2){
						$provider->setPlayerMovingTrail($player, 'IT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 5:
					if($page === 1){
						$provider->setPlayerMovingTrail($player, 'RDT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					if($page === 2){
						$provider->setPlayerMovingTrail($player, 'PT');
						$player->sendMessage(str_replace("{trail}", $this->main->getDataProvider()->getPlayerMovingTrail($player), $this->main->getSettings()->get('set-trail-message')));
					}
					break;
				case 6:
					if($page === 1) $this->sendTrailsForm($player, 2);
					elseif($page === 2) $this->sendTrailsForm($player);
					break;
				default:
					break;
			}
		});
		$data = $this->main->getFormSettings()->getNested('trails');
		if($page === 1){
			$form->setTitle($data[0]);
			if($data[1] !== ''){
				$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
				$finalizedString = str_replace('{trail}', $this->main->getDataProvider()->getPlayerMovingTrail($player), $finalizedString);
				$form->setContent($finalizedString);
			}
			$form->addButton(TextFormat::RED . "Angry Village Trail");
			$form->addButton(TextFormat::GOLD . "Critical Trail");
			$form->addButton(TextFormat::DARK_PURPLE . "Enchantment Trail");
			$form->addButton(TextFormat::GRAY . "Block Trail");
			$form->addButton(TextFormat::LIGHT_PURPLE . "Heart Trail");
			$form->addButton(TextFormat::RED . "Redstone Dust Trail");
			$form->addButton("Next");
		} elseif($page === 2){
			$form->setTitle($data[0]);
			if($data[2] !== ''){
				$finalizedString = str_replace('{player}', $player->getName(), $data[2]);
				$finalizedString = str_replace('{trail}', $this->main->getDataProvider()->getPlayerMovingTrail($player) ?? 'no trail', $finalizedString);
				$form->setContent($finalizedString);
			}
			$form->addButton(TextFormat::DARK_GRAY . "Smoke Trail");
			$form->addButton(TextFormat::WHITE . "Snowball Trail");
			$form->addButton(TextFormat::DARK_BLUE . "Water Trail");
			$form->addButton(TextFormat::YELLOW . "Lava Trail");
			$form->addButton(TextFormat::BLACK . "Ink Trail");
			$form->addButton(TextFormat::DARK_PURPLE . "Portal Trail");
			$form->addButton("Back");
		} else $this->sendTrailsForm($player);
		$player->sendForm($form);
	}

	public function sendMorphsForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-morph-worlds')) && $this->main->getSettings()->get('all-morph-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach($this->main->getSettings()->getNested('available-morph-entities') as $availableEntity){
				$order[$count] = $availableEntity;
				++$count;
			}
			if(isset($order[$data])){
				switch(strtolower($order[$data])){
					case 'villager':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$villager = new VillagerMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$villager->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $villager->getId();
						break;
					case 'chicken':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$chicken = new ChickenMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$chicken->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $chicken->getId();
						break;
					case 'pig':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$pig = new PigMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$pig->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $pig->getId();
						break;
					case 'irongolem':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$iron_golem = new IronGolemMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$iron_golem->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $iron_golem->getId();
						break;
					case 'zombie':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$zombie = new ZombieMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$zombie->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $zombie->getId();
						break;
					case 'skeleton':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$skeleton = new SkeletonMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$skeleton->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $skeleton->getId();
						break;
					case 'creeper':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$creeper = new CreeperMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$creeper->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $creeper->getId();
						break;
					case 'spider':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$spider = new SpiderMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$spider->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $spider->getId();
						break;
					case 'slime':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$slime = new SlimeMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$slime->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $slime->getId();
						break;
					case 'blaze':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$blaze = new BlazeMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$blaze->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $blaze->getId();
						break;
					case 'witch':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$witch = new WitchMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$witch->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $witch->getId();
						break;
					case 'phantom':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$phantom = new PhantomMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$phantom->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $phantom->getId();
						break;
					case 'enderman':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$enderman = new EndermanMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$enderman->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $enderman->getId();
						break;
					case 'wither':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$wither = new WitherMorph($player->getLevel(), $nbt, $this->main, $player);
						$wither->setGenericFlag(Entity::DATA_FLAG_POWERED, false);
						$player->setInvisible();
						$wither->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $wither->getId();
						break;
					case 'enderdragon':
						if(isset($this->main->morphs[strtolower($player->getName())])){
							$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
							$entity->flagForDespawn();
							unset($this->main->morphs[strtolower($player->getName())]);
							$player->setInvisible(false);
						}
						$nbt = Entity::createBaseNBT($player->asVector3());
						$ender_dragon = new EnderDragonMorph($player->getLevel(), $nbt, $this->main, $player);
						$player->setInvisible();
						$ender_dragon->spawnToAll();
						$player->sendMessage(str_replace("{morph}", $order[$data], $this->main->getSettings()->get('turned-into-morph-message')));
						$this->main->morphs[strtolower($player->getName())] = $ender_dragon->getId();
						break;
					default:
						$player->sendMessage(TextFormat::RED . "Morph not found! Immediately report this to the owners with the name of the button you clicked on!");
						break;
				}
			}
			if($data === $count){
				if(isset($this->main->morphs[strtolower($player->getName())])){
					$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
					$entity->flagForDespawn();
					unset($this->main->morphs[strtolower($player->getName())]);
					$player->setInvisible(false);
					$player->sendMessage($this->main->getSettings()->get('turned-out-of-morph-message'));
				} else {
					$player->sendMessage($this->main->getSettings()->get('morph-not-found-message'));
				}
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('morphs');
		$form->setTitle($data[0]);
		$data[1] === '' ? : $form->setContent(str_replace('{player}', $player->getName(), $data[1]));
		foreach($this->main->getSettings()->getNested('available-morph-entities') as $availableEntity){

			$form->addButton($randomizer->randomize($availableEntity));
		}
		$form->addButton("Turn off Morph.");
		$player->sendForm($form);
	}

	public function sendGravesForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-grave-worlds')) && $this->main->getSettings()->get('all-grave-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach($this->main->getSettings()->getNested('available-graves') as $availableGraves){
				$order[$count] = $availableGraves;
				++$count;
			}
			if(isset($order[$data])){
				switch(strtolower($order[$data])){
					case 'default':
						$this->main->getDataProvider()->setPlayerGrave($player, 'default');
						$player->sendMessage(str_replace("{grave}", $order[$data], $this->main->getSettings()->get('set-grave-message')));
						break;
					case 'steve':
						$this->main->getDataProvider()->setPlayerGrave($player, 'steve');
						$player->sendMessage(str_replace("{grave}", $order[$data], $this->main->getSettings()->get('set-grave-message')));
						break;
					default:
						break;
				}
			}
			if($data > $count){
				$this->main->getDataProvider()->setPlayerGrave($player, '');
				$player->sendMessage($this->main->getSettings()->get('turned-off-grave-message'));
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('graves');
		$form->setTitle($data[0]);
		if($data[1] !== ''){
			$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
			$finalizedString = str_replace('{grave}', $this->main->getDataProvider()->getPlayerGrave($player) ?? 'no grave', $finalizedString);
			$form->setContent($finalizedString);
		}
		foreach($this->main->getSettings()->getNested('available-graves') as $availableGrave){
			$form->addButton($randomizer->randomize($availableGrave));
		}
		$form->addButton('Turn off grave');
		$player->sendForm($form);
	}

	public function sendFaceMasksForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-face-mask-worlds')) && $this->main->getSettings()->get('all-face-mask-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach($this->main->getSettings()->getNested('available-face-masks') as $availableFaceMasks){
				$order[$count] = $availableFaceMasks;
				++$count;
			}
			if(isset($order[$data])){
				if(($skin = SkinFactory::get('facemask', strtolower($order[$data]))) instanceof Skin){
					if(isset($this->faceMask[$player->getName()])){
						$facemask = $player->getLevel()->getEntity((int) $this->faceMask[$player->getName()]);
						$facemask->flagForDespawn();
					}
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
					$this->faceMask[$player->getName()] = $facemask->getId();
					$this->main->getDataProvider()->setFaceMask($player, $skin->getSkinId());
					$player->sendMessage(str_replace('{facemask}', $this->main->getDataProvider()->getPlayerFaceMask($player) ?? 'no FaceMask', $this->main->getSettings()->get('set-face-mask-message')));
				} else {
					$player->sendMessage(TextFormat::RED . "Invalid skin " . TextFormat::YELLOW . $order[$data] . TextFormat::RED . "! Kindly report this to the owner!");
				}
			}
			if($data > $count){
				$this->main->getDataProvider()->setFaceMask($player, '');
				if(isset($this->faceMask[$player->getName()])){
					$facemask = $player->getLevel()->getEntity((int) $this->faceMask[$player->getName()]);
					$facemask->flagForDespawn();
				}
				$player->sendMessage($this->main->getSettings()->get('turned-off-face-mask-message'));
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('face-masks');
		$form->setTitle($data[0]);
		if($data[1] !== ''){
			$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
			$finalizedString = str_replace('{facemask}', $this->main->getDataProvider()->getPlayerFaceMask($player) ?? 'no FaceMask', $finalizedString);
			$form->setContent($finalizedString);
		}
		foreach($this->main->getSettings()->getNested('available-face-masks') as $availableFaceMask){
			$form->addButton($randomizer->randomize($availableFaceMask));
		}
		$form->addButton('Turn off FaceMask');
		$player->sendForm($form);
	}

	public function sendCostumesForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null) {
			if ($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-costume-worlds')) || $this->main->getSettings()->get('all-costume-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach($this->main->getSettings()->getNested('available-costumes') as $availableCostumes){
				$order[$count] = $availableCostumes;
				++$count;
			}
			if(isset($order[$data])) {
				if(($skin = SkinFactory::get('costume', strtolower($order[$data]))) instanceof Skin) {
					if(!in_array($player->getName(), EventListener::$sameSkin)){
						PlayerSkinManager::savePlayerSkin($player, $player->getSkin());
					}
					if(isset($this->main->morphs[strtolower($player->getName())])){
						$entity = $player->getLevel()->getEntity((int) $this->main->morphs[strtolower($player->getName())]);
						$entity->flagForDespawn();
						unset($this->main->morphs[strtolower($player->getName())]);
						$player->setInvisible(false);
					}
					$this->main->getDataProvider()->setPlayerCostume($player, strtolower($order[$data]));
					$player->setSkin($skin);
					$player->sendSkin(Server::getInstance()->getOnlinePlayers());
					$player->sendMessage(str_replace('{costume}', $this->main->getDataProvider()->getPlayerCostume($player), $this->main->getSettings()->get('set-costume-message')));
				}
			}
			if($data === $count){
				if(($skin = PlayerSkinManager::getPlayerSkin($player)) instanceof Skin){
					$player->setSkin($skin);
					$player->sendSkin(Server::getInstance()->getOnlinePlayers());
					$player->sendMessage($this->main->getSettings()->get('turned-off-costume-message'));
				}
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('costumes');
		$form->setTitle($data[0]);
		if($data[1] !== ''){
			$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
			$finalizedString = str_replace('{costume}', $this->main->getDataProvider()->getPlayerCostume($player) ?? 'no Costume', $finalizedString);
			$form->setContent($finalizedString);
		}
		foreach($this->main->getSettings()->getNested('available-costumes') as $availableCostumes){
			$form->addButton($randomizer->randomize($availableCostumes));
		}
		$form->addButton('Turn off Costumes');
		$player->sendForm($form);
	}

	public function sendParticlesForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if ($data === null) return;
			if(empty(ParticleManager::getAllParticleNames())) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-particle-worlds')) && $this->main->getSettings()->get('all-particle-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach(ParticleManager::getAllParticleNames(true) as $availableParticles){
				$order[$count] = [$availableParticles[0], $availableParticles[1], $availableParticles[2], $availableParticles[3]];
				++$count;
			}
			if(isset($order[$data])){
				$shape = $order[$data][0];
				$name = $order[$data][1];
				$particle = $order[$data][2];
				$height = $order[$data][3];
				if(($task = ParticleManager::getTaskByShape($shape)) !== null){
					if(isset($this->particleTasks[$player->getName()])){
						$this->main->getScheduler()->cancelTask((int) $this->particleTasks[$player->getName()]);
						unset($this->particleTasks[$player->getName()]);
						$this->main->getDataProvider()->setPlayerParticle($player, '', '');
					}
					$eligible_viewers = [];
					foreach(Server::getInstance()->getOnlinePlayers() as $viewer){
						if($this->main->getDataProvider()->canSeeParticles($viewer)) $eligible_viewers[] = $viewer;
					}
					$this->main->getDataProvider()->setPlayerParticle($player, $shape, $name);
					if(isset($this->particleTasks[$player->getName()])){
						$this->main->getScheduler()->cancelTask((int) $this->particleTasks[$player->getName()]);
						unset($this->particleTasks[$player->getName()]);
					}
					if($task === SpawnHeadParticleTask::class || $task === Spawn2DVerticleCircleTask::class){
						if($task === Spawn2DVerticleCircleTask::class) $height = 0;
						/** @var Task $task */
						$this->main->getScheduler()->scheduleRepeatingTask(($task = new $task($player, $particle, $eligible_viewers, $height)), 20);
					} else {
						/** @var Task $task */
						$this->main->getScheduler()->scheduleRepeatingTask(($task = new $task($player, $particle, $eligible_viewers, $height)), 2);
					}
					$this->particleTasks[$player->getName()] = $task->getTaskId();
				} else {
					$player->sendMessage(TextFormat::RED . "e.e... Invalid particle shape!");
					$player->sendMessage(TextFormat::YELLOW . "Shape => $shape\nName => $name\nParticle => $particle\nHeight =>  $height");
					$player->sendMessage(TextFormat::GRAY . TextFormat::BOLD . "Please send a screenshot of this message to the owner!");
				}
			}
			if($data > $count){
				if(isset($this->particleTasks[$player->getName()])){
					$this->main->getScheduler()->cancelTask((int) $this->particleTasks[$player->getName()]);
					unset($this->particleTasks[$player->getName()]);
					$this->main->getDataProvider()->setPlayerParticle($player, '', '');
					$player->sendMessage($this->main->getSettings()->get('turned-off-particle-message'));
				}
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('particles');
		$form->setTitle($data[0]);
		if($data[1] !== ''){
			$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
			$finalizedString = str_replace('{particle}', $this->main->getDataProvider()->getPlayerParticle($player)[1] ?? 'no Particle', $finalizedString);
			$form->setContent($finalizedString);
		}
		if(!empty(($particles = ParticleManager::getAllParticleNames()))){
			foreach($particles as $availableParticles){
				$form->addButton($randomizer->randomize($availableParticles));
			}
		}
		$form->addButton('Turn off Particles');
		$player->sendForm($form);
	}

	public function sendVehiclesForm(Player $player) : void
	{
		$form = new SimpleForm(function (Player $player, ?int $data = null){
			if($data === null) return;
			if(!in_array($player->getLevel()->getName(), $this->main->getSettings()->getNested('allowed-vehicle-worlds')) && $this->main->getSettings()->get('all-vehicle-worlds') === 'false'){
				$player->sendMessage($this->main->getSettings()->get('cannot-do-such-action-in-this-world'));
				return;
			}
			$count = 0;
			$order = [];
			foreach($this->main->getSettings()->getNested('available-vehicles') as $availableFaceMasks){
				$order[$count] = $availableFaceMasks;
				++$count;
			}
			if(isset($order[$data])){
				if(($skin = SkinFactory::get('vehicle', strtolower($order[$data]))) instanceof Skin){
					$nbt = Entity::createBaseNBT($player->asVector3());
					$nbt->setTag(new CompoundTag("Skin", [
						"Name" => new StringTag("Name", $skin->getSkinId()),
						"Data" => new ByteArrayTag("Data", $skin->getSkinData()),
						"CapeData" => new ByteArrayTag("CapeData", $skin->getCapeData()),
						"GeometryName" => new StringTag("GeometryName", $skin->getGeometryName()),
						"GeometryData" => new ByteArrayTag("GeometryData", $skin->getGeometryData())
					]));
					$vehicle = new Vehicle($this->main, $player, $player->getLevel(), $nbt);
					$vehicle->spawnToAll();
					$this->vehicle[$player->getName()] = $vehicle->getId();
					$player->sendMessage($this->main->getSettings()->get('set-vehicle-message'));
				} else {
					$player->sendMessage(TextFormat::RED . "Invalid skin " . TextFormat::YELLOW . $order[$data] . TextFormat::RED . "! Kindly report this to the owner!");
				}
			}
			if($data === $count){
				if(isset($this->vehicle[$player->getName()])){
					$vehicle = $player->getLevel()->getEntity((int) $this->vehicle[$player->getName()]);
					$vehicle->flagForDespawn();
				}
				$player->sendMessage($this->main->getSettings()->get('turned-off-vehicle-message'));
			}
		});
		$randomizer = new ColourRandomizer();
		$data = $this->main->getFormSettings()->getNested('vehicles');
		$form->setTitle($data[0]);
		if($data[1] !== ''){
			$finalizedString = str_replace('{player}', $player->getName(), $data[1]);
			$form->setContent($finalizedString);
		}
		foreach($this->main->getSettings()->getNested('available-vehicles') as $availableFaceMask){
			$form->addButton($randomizer->randomize($availableFaceMask));
		}
		$form->addButton('Turn off Vehicle');
		$player->sendForm($form);
	}
}