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
		$loopStructure->loadItems();
		$currentStructureAsWikiText = $loopStructure->getStructureItemsAsWikiText();
		
		$request = $this->getRequest();
		$newStructureContent = $request->getText( 'loopstructure-content' );
		
		$error = false;
		
		if( ! empty( $newStructureContent )) {

			# the content was changend
			# use local parser to get a default parsed result
			$localParser = new Parser();
			$tmpTitle = Title::newFromText( 'NO TITLE' );
			$parserOutput = $localParser->parse( $newStructureContent, $tmpTitle, new ParserOptions() );
			
			if( is_object( $parserOutput )) {
				
				$parsedStructure = $parserOutput->mText;
				
				if( ! empty( $parsedStructure )) {
			
					$tmpLoopStructure = new LoopStructure();
					$tmpLoopStructure->setStructureItemsFromWikiText( $parsedStructure );
					$newStructureContentParsedWikiText = $tmpLoopStructure->getStructureItemsAsWikiText();
					
					# if new parsed structure is different to the new one save it
					if( $currentStructureAsWikiText != $newStructureContentParsedWikiText ) {
							
						$loopStructure->deleteItems();
						$loopStructure->setStructureItemsFromWikiText( $parsedStructure );
						$loopStructure->saveItems();
						$currentStructureAsWikiText = $loopStructure->getStructureItemsAsWikiText();
						
						$out->addHTML(
							Xml::openElement( 'div', array( 'class' => 'alert alert-success mt-3', 'role' => 'alert' ) ) .
							$this->msg( 'loopstructure-save-success' )->parse() .
							Xml::closeElement( 'div' )
						);
							
					} else {
						$error = $this->msg( 'loopstructure-save-equal-error' )->parse();
					}
					
				} else {
					$error = $this->msg( 'loopstructure-save-parsed-structure-error' )->parse();
				}
						
			} else {
				$error = $this->msg( 'loopstructure-save-parse-error' )->parse();
			}
			
		}

		# print error message if exists
		if( $error !== false ) {
			$out->addHTML(
				Xml::openElement( 'div', array( 'class' => 'alert alert-danger mt-3', 'role' => 'alert' ) ) .
				$error .
				Xml::closeElement( 'div' )
			);
		}
		
		# generate structure form
		$out->addHTML(
			Xml::OpenElement( 'h1' ).$this->msg( 'loopstructure-specialpage-title' )->parse().Xml::closeElement( 'h1' ).
			Xml::openElement( 'div', array( 'id' => 'loopstructure-form-wrapper' ) ) .
			Xml::openElement( 'form', array( 'id' => 'loopstructure-form', 'method' => 'POST', 'class' => 'mt-3 mb-3' ) ) .
			Xml::openElement( 'div', array( 'id' => 'loopstructure-content-wrapper', 'class' => 'form-group' ) ) .
			Html::rawElement( 'textarea', array( 'name' => 'loopstructure-content', 'style' => 'width: 700px; height: 700px;' ), $currentStructureAsWikiText ) .
			Xml::closeElement( 'div' ) .
			Xml::element( 'input', array( 'type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'loopstructure-submit', 'value' => $this->msg( 'submit' )->parse() ) ) .
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



