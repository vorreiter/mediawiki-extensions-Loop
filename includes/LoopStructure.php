<?php 


/**
 * Class representing a bookstructure and other metainformation
 *
 */
class LoopStructure {
	
	private $mId = 0;					// id of the structure
	private $mMainpage;					// article id of the main page
	private $mStructureItems = array();	// array of structure items
	private $mProperties = array();		// array of structure properties
	private $mPropertiesLoaded = false;	// bool properties loaded from database
	
	function __construct() {
		
	}
	
}	

/**
 *  Class representing a single page of a LoopStructure
 *
 */
class LoopStructureItem {
	
	private $mId;					// id of the structure item
	private $mStructure = 0;		// id of the corresponding structure
	private $mArticle;				// article id of the page
	
	private $mPreviousArticle;		// article id from the previous page
	private $mNextArticle;			// article id from the next page
	private $mParentArticle;		// article id from the parent page 
	
	private $mTocLevel;				// Level within the corresponding structure
	private $mSequence;				// Sequential number within the corresponding structure
	private $mTocNumber;			// string rrepresentation of the chapter number
	private $mTocText;				// page title

	function __construct() {
	
	}	
	
}


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

		$out->addHtml ('<h1>');
		$out->addWikiMsg( 'loopstructure-specialpage-title' );
		$out->addHtml ('</h1>');

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



