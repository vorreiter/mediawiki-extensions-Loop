<?php 

class LoopPdf {
	
	public static function structure2pdf(LoopStructure $structure) {
		global $IP, $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken;
	
		
	
		$unique = uniqid();
	
		$wiki_xml = LoopXml::structure2xml($structure);
	
		try {
			$xml = new DOMDocument();
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			var_dump($e);
		}
	
		try {
			$xsl = new DOMDocument;
			$xsl->load($IP.'/extensions/Loop/xsl/pdf.xsl');
		} catch (Exception $e) {
			var_dump($e);
		}
	
		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet($xsl);
			$xmlfo = $proc->transformToXML($xml);
		} catch (Exception $e) {
			var_dump($e);
		}
	
		#var_dump($xmlfo);exit;
		$url = $wgXmlfo2PdfServiceUrl. '?token='.$wgXmlfo2PdfServiceToken;
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "$xmlfo");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$pdf = curl_exec($ch);
		curl_close($ch);
	
		return $pdf;
	
	}	
	
}