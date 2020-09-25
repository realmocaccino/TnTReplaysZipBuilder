<?php
class ReplaysZipBuilder
{
	protected $baseFilename;
	protected $bestOf;
	protected $playersNames = [];
	protected $playersNamesSeparator;
	protected $replays;
	protected $replaysData = [];
	protected $storageLocation;
	protected $zipFilename;

	public function __construct(array $replayFiles, int $bestOf = 3)
	{
		$this->checkFilesType($replayFiles);
		
		$this->replays = $replayFiles['tmp_name'];
		$this->bestOf = $bestOf;
		
		$this->playersNamesSeparator = ' vs ';
		$this->storageLocation = '../storage/';

		$this->setPlayersNames();
		$this->setBaseFilename();
		$this->setZipFilename();
		$this->setReplaysData();
	}
	
	public function build(): void
	{
		$this->orderReplaysData();
		$this->addDummyFiles();
		$this->createZipFile();
	}
	
	protected function getPlayersNames(): array
	{
		return $this->playersNames;
	}
	
	protected function setPlayersNames(): void
	{
		$xml = simplexml_load_file($this->replays[0]);
		
		foreach($xml->Players->Player as $player) {
			$this->playersNames[] = $player->Identity['Name'];
		}
	}
	
	protected function getBaseFilename(): string
	{
		return $this->baseFilename;
	}
	
	protected function setBaseFilename(): void
	{
		$this->baseFilename = implode($this->playersNamesSeparator, $this->getPlayersNames());
	}
	
	protected function getZipFilename(): string
	{
		return $this->zipFilename;
	}
	
	protected function getFullZipFilename(): string
	{
		return $this->storageLocation . $this->zipFilename;
	}
	
	protected function setZipFilename(): void
	{
		$this->zipFilename = $this->getBaseFilename() . '.zip';
	}
	
	protected function setReplaysData(): void
	{
		foreach($this->replays as $replay) {
			$xml = simplexml_load_file($replay);
			
			$data = [];
			$data['content'] = file_get_contents($replay);
			$data['timestamp'] = $xml->OriginalDate;
			$data['playersNames'] = [];
			
			foreach($xml->Players->Player as $player) {
				$data['playersNames'][] = $player->Identity['Name'];
			}
			
			$this->replaysData[] = $data;
		}
	}
	
	protected function orderReplaysData(): void
	{
		usort($this->replaysData, function($a, $b) {
			return $a['timestamp'] - $b['timestamp'];
		});
	}
	
	protected function addDummyFiles(): void
	{
		$dummiesTotal = $this->bestOf - count($this->replaysData);
		
		for($i = 0; $i < $dummiesTotal; $i++) {
			$this->replaysData[] = [
				'content' => $this->createDummyContent(),
				'playersNames' => $this->getPlayersNames()
			];
		}
	}
	
	protected function createDummyContent(): string
	{
		$xml = simplexml_load_file($this->storageLocation . 'dummy.xml');
		
		$totalGameEvents = rand(0, 20000);

		for($i = 0; $i < $totalGameEvents; $i++) {
			$gameEvent = $xml->GameEvents->addChild('g');
			$gameEvent->addAttribute('e', 'Build');
			$gameEvent->addAttribute('t', '1');
			$gameEvent->addAttribute('d', 'structure_farm');
		}

		return $xml->asXML();
	}
	
	protected function createZipFile(): void
	{
		$zip = new ZipArchive;
		
		if($zip->open($this->getFullZipFilename(), ZipArchive::CREATE)) {
			foreach($this->replaysData as $data) {
				$filename = $this->getBaseFilename() . ' ' . $this->numberFile() . '.xml';
				
				$zip->addFromString($filename, $data['content']);
			}
			$zip->close();
		}
	}
	
	public function downloadZipFile(): void
	{
		header('Content-Type: application/zip');
		header('Pragma: no-cache');
		header('Content-Disposition: attachment; filename=' . $this->getZipFilename());
		
		readfile($this->getFullZipFilename());
	}
	
	private function checkFilesType(array $replayFiles): void
	{
		if(array_diff(array_unique($replayFiles['type']), ['text/xml'])) exit('Only XML files are accepted');
	}
	
	private function numberFile(): string
	{
		static $numbering = 1;
		
		return str_pad($numbering++, 2, '0', STR_PAD_LEFT);
	}
}