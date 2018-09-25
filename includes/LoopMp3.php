<?php 
class LoopMp3 { 

	public static function structure2mp3(LoopStructure $loopStructure) {
		global $IP, $wgUploadDirectory;
		
		$structureItems  = $loopStructure->getStructureItems();
				
		$structureItemDir = $wgUploadDirectory.'/export/mp3/structureitems/';
		
		foreach ($structureItems as $structureItem) {
			
			wfDebug("\n".__METHOD__.':structureItem:'.print_r($structureItem,true));
			
			$last_changed = $structureItem->lastChanged();
			$last_changed_ts = wfTimestamp(TS_UNIX,$last_changed);
			
			
			wfDebug("\n".__METHOD__.':last changed:'.print_r($last_changed,true));
			wfDebug("\n".__METHOD__.':last changed ts:'.print_r($last_changed_ts,true));
			
			$generate = false;
			$structureItemFilename = $structureItemDir.strval($structureItem->getId());
			unset ($filemtime);
			if (is_file($structureItemFilename)) {
				$filemtime = filemtime($structureItemFilename);
				
				if ($filemtime == $last_changed_ts) {
					// keine Änderung seit der letzten MP3 Erzeugung
					$generate = false;
				} else {
					// Es liegt eine Änderung seit der letzten MP3 Erzeugung vor
					$generate = true;
				}
				
				
			} else {
				// Noch keine MP3 Datei für StructureItem vorhanden -> neu erzeugen
				$generate = true;
			}
			
			if ($generate == true) {
				
			}
			
			
		}
		
		
		/*
		$wiki_xml = LoopXml::structure2xml($loopStructure);
		
		var_dump($wiki_xml); exit;
		
		try {
			$xml = new DOMDocument();
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			var_dump($e);
		}
		
		try {
			$xsl = new DOMDocument;
			$xsl->load($IP.'/extensions/Loop/xsl/ssml.xsl');
		} catch (Exception $e) {
			var_dump($e);
		}
		
		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet($xsl);
			$ssml = $proc->transformToXML($xml);
		} catch (Exception $e) {
			var_dump($e);
		}
		
		
		var_dump($ssml); exit;
		*/
		
		
		
		
		/*
		 * ToDo
		 * Wiki XML holen
		 * Wiki XML in SSML transformieren
		 * SSML aufspalten auf Seitentexte
		 * 
		 * Temp Verzeichnis anlegen
		 * Jeden Teil SSML an den Toolsserver / Polly schicken
		 * Ergebnis als MP3 speichern
		--------------------------------
		
		Komplettes Audiobook als Zip
		
		
		alle Seiten der Structure in export/mp3 ablegen
		---[articleid]_[lasttouched].mp3
		[structureitemid]_[lasttouched].mp3
		
		xml erstellen
		xsl -> audiotext
		
		für alle structureitems
		überprüfen ob aktuelle mp3 vorliegt
		falls nicht mp3 neu erstellen
		
		zip erstellen
		
		
		Einzelne MP3
		
		LoopPageAudio
		
		
		beim Ändern der Structure
		exports löschen: pdf, epub, offline, mp3
		beim structureitem löschen mp3 löschen
		
		
		 */
		
		return true;
		
		
	}
	
}