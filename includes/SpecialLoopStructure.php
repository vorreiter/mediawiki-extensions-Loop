<?php


/**
 *  Special page representing the table of contents
 *
 *
 */
 
class SpecialLoopStructure extends SpecialPage {
	
	
	
	public function __construct() {
		parent::__construct( 'LoopStructure' );
	}

	public function execute($sub) {
		
		$this->setHeaders();
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'loopstructure-specialpage-title' ) );
		
		$loopStructure = new LoopStructure();

		$request = $this->getRequest();
		$newStructureContent = $request->getText( 'loopstructure-content' );
		
		if( ! empty( $newStructureContent )) {
			
			# the content was changend
			# use local parser to get a default parsed result
			$localParser = new Parser();
			$tmpTitle = Title::newFromText( 'NO TITLE' );
			$parserOutput = $localParser->parse( $newStructureContent, $tmpTitle, new ParserOptions() );
			$parsedStructure = $parserOutput->mText;
		
			$loopStructure = new LoopStructure();
			$loopStructure->deleteItems();
			$loopStructure->setStructureItemsFromWikiText( $parsedStructure );
			$loopStructure->saveItems();
			
			$html = '<div class="alert alert-success" role="alert">'.$this->msg( 'loopstructure-save-success' ).'</div>';
			$out->addHtml($html);
	
		}
		
		$wikiText = $loopStructure->getStructureItemsAsWikiText();
		
		$html = '<h1>'.$this->msg( 'loopstructure-specialpage-title' ).'</h1>';
		$html .= '<form method="POST" class="mt-3 mb-3">';
		$html .= '<div class="form-group">';
		$html .= '<textarea class="form-control" name="loopstructure-content" style="width: 700px; height: 700px;" id="loopstructure-content">'.$wikiText.'</textarea>';
		$html .= '</div>';
		$html .= '<input type="submit" class="btn btn-primary" value="'.$this->msg( 'submit' ).'"/>';
		$html .= '</form>';
		$out->addHtml($html);

	}
	

	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
	}
}



