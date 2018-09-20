<?php 


abstract class LoopExport {
	
	public $structure;
	public $exportContent;
	public $exportDirectory;
	public $fileExtension;
	
	abstract protected function generateExportContent();

	
	public function getExistingExportFile() {
		global $wgUploadDirectory;
		
		$export_dir = $wgUploadDirectory.$this->exportDirectory.'/'.$this->structure->getId();
		if (!is_dir($export_dir)) {
			@mkdir($export_dir, 0777, true);
		}		
		
		$export_file = $export_dir.'/'.$this->structure->lastChanged().'.'.$this->fileExtension;
		if (is_file($export_file)) {	
			
			$fh = fopen($export_file, 'r');
			$content = fread($fh, filesize($export_file));
			$this->exportContent = $content;
			fclose($fh);			
			
			return $export_file;
		} else {
			return false;
		}
	}
	
	public function saveExportFile() {
		global $wgUploadDirectory;
		
		$export_dir = $wgUploadDirectory.$this->exportDirectory.'/'.$this->structure->getId();
		var_dump( $export_dir );
		if (!is_dir($export_dir)) {
			@mkdir($export_dir, 0777, true);
		}
		$export_file = $export_dir.'/'.$this->structure->lastChanged().'.'.$this->fileExtension;		
		
		$fh = fopen($export_file, 'w');
		fwrite($fh, $this->exportContent);
		fclose($fh);
		
		// delete old export file
		if ($handle = opendir($export_dir)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					if (is_file($export_dir.'/'.$entry)) {
						if ($entry != basename($export_file)) {
							unlink($export_dir.'/'.$entry);
						}
					}
						
				}
			}
		}		
	}
	
	public function getExportContent() {
		return $this->exportContent;
	}
	
	public function setExportContent($content) {
		$this->exportContent = $content;
	}	
	
	public function getExportFilename() {
		global $wgSitename;
		return urlencode( $wgSitename . '-' . wfTimestampNow() .'.'. $this->fileExtension );
	}
	
}
	
	
	
class LoopExportXml extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/xml';
		$this->fileExtension = 'xml';
	}

	public function generateExportContent() {
		$this->exportContent = LoopXml::structure2xml($this->structure);
	}

	public function sendExportHeader() {
		
		$filename = $this->getExportFilename();
		
		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/xml; charset=utf-8");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );

	}
	
	// for Development
	public function getExistingExportFile() {
		return false;
	}
}


class LoopExportPdf extends LoopExport {

	public function LoopExportPdf($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/pdf';
		$this->fileExtension = 'pdf';
	}

	public function generateExportContent() {
		$this->exportContent = LoopPdf::structure2pdf($this->structure);
	}

	public function sendExportHeader() {
		
		$filename = $this->getExportFilename();
		
		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/pdf");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

	}
	
	// for Development
	public function getExistingExportFile() {
		return false; 
	}
}


class LoopExportMp3 extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/mp3';
		$this->fileExtension = 'zip';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopMp3
	}

	public function sendExportHeader() {
		
		$filename = $this->getExportFilename();
		
		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		
	}
}



class LoopExportEpub extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/epub';
		$this->fileExtension = 'epub';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopEpub
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/epub+zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));		
		
	}
}


class LoopExportHtml extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/html';
		$this->fileExtension = 'zip';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopOffline
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();
		
		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		
	}

}

class LoopExportScorm extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/Scorm';
		$this->fileExtension = 'zip';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopScorm
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", $this->structure->lastChanged()));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

	}

}