<?php

declare(strict_types=1);

namespace seeker\CosmeticsPlus;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use seeker\CosmeticsPlus\commands\CostumesCommand;
use seeker\CosmeticsPlus\commands\DebugCommand;
use seeker\CosmeticsPlus\commands\FaceMaskCommand;
use seeker\CosmeticsPlus\commands\GravesCommand;
use seeker\CosmeticsPlus\commands\MorphsCommand;
use seeker\CosmeticsPlus\commands\ParticlesCommand;
use seeker\CosmeticsPlus\commands\TrailsCommand;
use seeker\CosmeticsPlus\commands\VehiclesCommand;
use seeker\CosmeticsPlus\cosmetics\SkinFactory;
use seeker\CosmeticsPlus\data\DataProvider;
use seeker\CosmeticsPlus\data\JSONDataProvider;
use seeker\CosmeticsPlus\morphs\BlazeMorph;
use seeker\CosmeticsPlus\morphs\ChickenMorph;
use seeker\CosmeticsPlus\morphs\CreeperMorph;
use seeker\CosmeticsPlus\morphs\EnderDragonMorph;
use seeker\CosmeticsPlus\morphs\EndermanMorph;
use seeker\CosmeticsPlus\morphs\IronGolemMorph;
use seeker\CosmeticsPlus\morphs\PhantomMorph;
use seeker\CosmeticsPlus\morphs\PigMorph;
use seeker\CosmeticsPlus\morphs\SkeletonMorph;
use seeker\CosmeticsPlus\morphs\SlimeMorph;
use seeker\CosmeticsPlus\morphs\SpiderMorph;
use seeker\CosmeticsPlus\morphs\VillagerMorph;
use seeker\CosmeticsPlus\morphs\WitchMorph;
use seeker\CosmeticsPlus\morphs\WitherMorph;
use seeker\CosmeticsPlus\morphs\ZombieMorph;
use seeker\CosmeticsPlus\utils\BugReport;
use seeker\CosmeticsPlus\utils\ParticleManager;
use seeker\CosmeticsPlus\utils\PlayerSkinManager;
use xenialdan\skinapi\API;

class CosmeticsPlus extends PluginBase
{

	/**
	 * @var Config
	 */
	private $settings;

	/**
	 * @var ?Config
	 */
	private $performanceSettings;

	/**
	 * @var DataProvider
	 */
	private $dataProvider;

	/**
	 * @var FormManager
	 */
	private $formManager;

	/**
	 * @var Config
	 */
	private $forms;

	/**
	 * @var Config
	 */
	private $skins;

	/**
	 * @var Config
	 */
	private $particles;

	/**
	 * @var array<string, int>
	 */
	public $morphs = [];

	public function onEnable() : void
	{
		$start = microtime(true);
		//A small note: Everything is customizable so we're making multiple calls to our configuration file, which is written in YAML, thus making the process even slower. Don't blame me for lag, if there is any!
		$this->initResources();
		$this->settings = new Config($this->getDataFolder() . 'settings.yml', Config::YAML);
		$this->forms = new Config($this->getDataFolder() . 'forms.yml', Config::YAML);
		$this->skins = new Config($this->getDataFolder() . 'skins.yml', Config::YAML);
		$this->particles = new Config($this->getDataFolder() . 'particles.yml', Config::YAML);
	//	$this->initConfig();
		$this->formManager = new FormManager($this);
		if($this->settings->get('data') == 'json'){
			$this->dataProvider = new JSONDataProvider($this);
		}
		$this->performanceSettings = null;
		if($this->getSettings()->get('performance') == true){
			$this->saveResource('performance.yml');
			$this->performanceSettings = new Config($this->getDataFolder() . 'performance.yml', Config::YAML);
		}
		$this->registerAllEntities();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('trails-command-name'), new TrailsCommand($this->getSettings()->get('trails-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('morphs-command-name'), new MorphsCommand($this->getSettings()->get('morphs-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('graves-command-name'), new GravesCommand($this->getSettings()->get('graves-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('face-masks-command-name'), new FaceMaskCommand($this->getSettings()->get('face-masks-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('costumes-command-name'), new CostumesCommand($this->getSettings()->get('costumes-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('particles-command-name'), new ParticlesCommand($this->getSettings()->get('particles-command-name'), $this));
		$this->getServer()->getCommandMap()->register($this->getSettings()->get('vehicle-command-name'), new VehiclesCommand($this->getSettings()->get('vehicle-command-name'), $this));
		$this->getServer()->getCommandMap()->register("debug", new DebugCommand("debug", $this));
		$this->initializeSkins();
		$this->initializeParticles();
		PlayerSkinManager::loadAllSkins();
		$end = microtime(true);
		$timeTaken = ($end - $start) * 1000;
		$this->getServer()->getLogger()->info(TextFormat::YELLOW . "Initializing plugin took around $timeTaken" . "ms. If this is higher than 60ms, a report will be sent to Seeker.");
	}

	private function initResources() : void
	{
		$resources = "plugins" . DIRECTORY_SEPARATOR . "CosmeticsPlus" . DIRECTORY_SEPARATOR . "resources";
		foreach(scandir($resources) as $file){
			if($file === '.' || $file ==='..' || is_dir($file) || $file === 'skins') continue;
			$this->saveResource($file);
		}
		if(!is_dir($this->getDataFolder() . "skins")) mkdir($this->getDataFolder() . "skins");
		foreach(scandir($resources . DIRECTORY_SEPARATOR . "skins") as $skin){
			if($skin === '.' || $skin ==='..' || is_dir($skin)) continue;
			if(!copy($resources . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . $skin, $this->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $skin)){
				$this->getServer()->getLogger()->error("Could not move $skin to plugin_data/CosmeticsPlus/skins. Please restart.");
				/*$report = new BugReport("Could not move $skin to plugin_data/CosmeticsPlus/skins.", '
				1. Turn on server.
				2. Call initResources.
				');
				$report->send();*/
			}
		}
	}

	private function registerAllEntities() : void
	{
		Entity::registerEntity(BlazeMorph::class, true);
		Entity::registerEntity(ChickenMorph::class, true);
		Entity::registerEntity(CreeperMorph::class, true);
		Entity::registerEntity(EnderDragonMorph::class, true);
		Entity::registerEntity(EndermanMorph::class, true);
		Entity::registerEntity(IronGolemMorph::class, true);
		Entity::registerEntity(PhantomMorph::class, true);
		Entity::registerEntity(PigMorph::class, true);
		Entity::registerEntity(SkeletonMorph::class, true);
		Entity::registerEntity(SlimeMorph::class, true);
		Entity::registerEntity(SpiderMorph::class, true);
		Entity::registerEntity(BlazeMorph::class, true);
		Entity::registerEntity(VillagerMorph::class, true);
		Entity::registerEntity(WitchMorph::class, true);
		Entity::registerEntity(WitherMorph::class, true);
		Entity::registerEntity(ZombieMorph::class, true);
	}

	private function initializeSkins() : void
	{
		foreach($this->skins->getAll() as $nest){
			if(isset($nest['name']) && isset($nest['type']) && isset($nest['skin']) && isset($nest['geometryName']) && isset($nest['geometry'])){
				//This will cost startup time but can help reduce memory usage
				if($nest['type'] == 'graves') if(!in_array($nest['name'], $this->getSettings()->getNested('available-graves'))) return;
				if($nest['type'] == 'facemask') if(!in_array($nest['name'], $this->getSettings()->getNested('available-face-masks'))) return;
				if($nest['type'] == 'vehicles') if(!in_array($nest['name'], $this->getSettings()->getNested('available-vehicles'))) return;
				if(is_file($this->getDataFolder() . $nest['skin']) && is_file($this->getDataFolder() . $nest['geometry'])){
					$skin = new Skin($nest['name'], API::fromImage(imagecreatefrompng($this->getDataFolder() . $nest['skin'])), "", $nest['geometryName'], file_get_contents($this->getDataFolder() . $nest['geometry']));
					if(!SkinFactory::register($nest['type'], $nest['name'], $skin)) $this->getServer()->getLogger()->error("Skin " . $nest['name'] . " could not be registered. Please check if the fields in skin.yml for " . $nest['name'] . " are valid and it is not duplicated.");
				} else $this->getServer()->getLogger()->error("Geometry/skin (or both) for " . $nest['name'] . " are not a valid file.");
			} else $this->getServer()->getLogger()->error("Skin " . $nest["name"] . " has some unfilled fields.");
		}
	}

	private function initializeParticles() : void
	{
		foreach($this->particles->getAll() as $nest){
			if(isset($nest['name']) && isset($nest['shape']) && isset($nest['particle'])){
				$height = null;
				if(isset($nest['height'])) $height = (int) $nest['height'];
				if(!ParticleManager::register($nest['shape'], $nest['name'], $nest['particle'], $height)) $this->getServer()->getLogger()->error("Particle " . $nest['name'] . " could not be registered. Please check if the fields in skin.yml for " . $nest['name'] . " are valid and it is not duplicated.");
			} else $this->getServer()->getLogger()->error("Skin " . $nest["name"] . " has some unfilled fields.");
		}
	}

	public function getSettings() : Config
	{
		return $this->settings;
	}

	public function getPerformanceSettings() : ?Config
	{
		return $this->performanceSettings;
	}

	public function getDataProvider() : DataProvider
	{
		return $this->dataProvider;
	}

	public function getFormManager() : FormManager
	{
		return $this->formManager;
	}

	public function getFormSettings() : Config
	{
		return $this->forms;
	}
}
