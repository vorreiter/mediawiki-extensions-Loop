<?php


/**
 *  Special page representing the table of contents
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
			
			$out->addHTML(
				Xml::openElement( 'div', array( 'class' => 'alert alert-success mt-3', 'role' => 'alert' ) ) .
				$this->msg( 'loopstructure-save-success' ) .
				Xml::closeElement( 'div' )
			);
	
		}
		
		$out->addHTML(
			Xml::OpenElement( 'h1' ).$this->msg( 'loopstructure-specialpage-title' ).Xml::closeElement( 'h1' ).
			Xml::openElement( 'div', array( 'id' => 'loopstructure-form-wrapper' ) ) .
			Xml::openElement( 'form', array( 'id' => 'loopstructure-form', 'method' => 'POST', 'class' => 'mt-3 mb-3' ) ) .
			Xml::openElement( 'div', array( 'id' => 'loopstructure-content-wrapper', 'class' => 'form-group' ) ) .
			Html::rawElement( 'textarea', array( 'name' => 'loopstructure-content', 'style' => 'width: 700px; height: 700px;' ), $loopStructure->getStructureItemsAsWikiText() ) .
			Xml::closeElement( 'div' ) .
			Xml::element( 'input', array( 'type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'loopstructure-submit', 'value' => $this->msg( 'submit' ) ) ) .
			Xml::closeElement( 'form' ) .
			Xml::closeElement( 'div' )
		);
		
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



