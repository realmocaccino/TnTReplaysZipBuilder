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

	public function __construct($replayFiles, $bestOf = 3)
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
	
	public function build()
	{
		$this->orderReplaysData();
		$this->addDummyFiles();
		$this->createZipFile();
	}
	
	protected function getPlayersNames()
	{
		return $this->playersNames;
	}
	
	protected function setPlayersNames()
	{
		$xml = simplexml_load_file($this->replays[0]);
		
		foreach($xml->Players->Player as $player) {
			$this->playersNames[] = $player->Identity['Name'];
		}
	}
	
	protected function getBaseFilename()
	{
		return $this->baseFilename;
	}
	
	protected function setBaseFilename()
	{
		$this->baseFilename = implode($this->playersNamesSeparator, $this->getPlayersNames());
	}
	
	protected function getZipFilename()
	{
		return $this->zipFilename;
	}
	
	protected function getFullZipFilename()
	{
		return $this->storageLocation . $this->zipFilename;
	}
	
	protected function setZipFilename()
	{
		$this->zipFilename = $this->getBaseFilename() . '.zip';
	}
	
	protected function setReplaysData()
	{
		foreach($this->replays as $replay) {
			$xml = simplexml_load_file($replay);
		
			$data = [];
			$data['filename'] = $replay;
			$data['timestamp'] = $xml->OriginalDate;
			$data['playersNames'] = [];

			foreach($xml->Players->Player as $player) {
				$data['playersNames'][] = $player->Identity['Name'];
			}
			
			$this->replaysData[] = $data;
		}
	}
	
	protected function orderReplaysData()
	{
		usort($this->replaysData, function($a, $b) {
			return $a['timestamp'] - $b['timestamp'];
		});
	}
	
	protected function addDummyFiles()
	{
		$dummiesTotal = $this->bestOf - count($this->replaysData);
		
		for($i = 0; $i < $dummiesTotal; $i++) {
			$this->replaysData[] = [
				'filename' => $this->storageLocation . 'dummy.xml',
				'playersNames' => $this->getPlayersNames()
			];
		}
	}
	
	protected function createZipFile()
	{
		$zip = new ZipArchive;
		
		if($zip->open($this->getFullZipFilename(), ZipArchive::CREATE)) {
			foreach($this->replaysData as $data) {
				$filename = $this->getBaseFilename() . ' ' . $this->numberFile() . '.xml';
				
				$zip->addFromString($filename, file_get_contents($data['filename']));
			}
			$zip->close();
		}
	}
	
	public function downloadZipFile()
	{
		header('Content-Type: application/zip');
		header('Pragma: no-cache');
		header('Content-Disposition: attachment; filename=' . $this->getZipFilename());
		
		readfile($this->getFullZipFilename());
	}
	
	private function checkFilesType($replayFiles)
	{
		if(array_diff(array_unique($replayFiles['type']), ['text/xml'])) exit('Only XML files are accepted');
	}
	
	private function numberFile()
	{
		static $numbering = 1;
		
		return str_pad($numbering++, 2, '0', STR_PAD_LEFT);
	}
}