<?php 


/**
 * Class representing a bookstructure and other metainformation
 *
 */
class LoopStructure {
	
	private $mId = 0;					// id of the structure
	private $mMainPage;					// article id of the main page
	private $mStructureItems = array();	// array of structure items
	private $mProperties = array();		// array of structure properties
	private $mPropertiesLoaded = false;	// bool properties loaded from database
	
	function __construct() {
		$this->loadItems();
	}
	
	public function setId( $id ) {
		$this->mId = $id;
	}
	
	public function setMainPage( $mainPageId ) {
		$this->mMainPage = $mainPageId;
	}
	
	public function setProperties( $properties ) {
		$this->properties = $properties;
	}
	
	public function setPropertiesLoaded( Boolean $propertiesLoaded ) {
		$this->mPropertiesLoaded = $propertiesLoaded;
	}
	
	public function getId() {
		return $this->mId;
	}
	
	public function getPropertiesLoaded() {
		return $this->mPropertiesLoaded;
	}
	
	public function getProperties() {
		return $this->mProperties;
	}
	
	public function getMainPage() {
		return $this->mMainPage;
	}
	
	public function getStructureId() {
		return $this->mId;
	}
	
	public function getStructureItems() {
		return $this->mStructureItems;
	}
	 
	/**
	 * Converts the structureitems to the table of contents as wikitext.
	 */
	public function getStructureItemsAsWikiText() {
		
		$wikiText = '';

		foreach( $this->getStructureItems() as $structureItem ) {

			if( intval( $structureItem->getTocLevel() ) === 0 ) {
				$wikiText .= '[['.$structureItem->getTocText().']]'.PHP_EOL.PHP_EOL;
			} else {
				
				$wikiText .= str_repeat( '=', $structureItem->getTocLevel() ).' '.$structureItem->getTocText().' '.str_repeat( '=', $structureItem->getTocLevel() ).PHP_EOL;
			}
		}
		
		return $wikiText;
		
	}
	
	/**
	 * Converts wikitext to LoopStructureItems.
	 * @param $wikiText
	 */
	public function setStructureItemsFromWikiText( $wikiText ) {
	
		global $wgUser;# TODO is this the correct user?

		$regex = "/(<a )(.*?)(>)(.*?)(<\\/a>)/";
		preg_match($regex, $wikiText, $matches);
		$rootTitleText = $matches[4];
		$rootTitle = Title::newFromText($rootTitleText);

		$this->setMainPage( $rootTitle->getArticleID() );
		
		# create new root page
		if( $this->getMainPage() == 0 ) {
			$newPage = WikiPage::factory( Title::newFromText( $rootTitleText ));
			$newContent = new WikitextContent(wfMessage( 'loopstructure-default-newpage-content')->inContentLanguage()->text());
			$newPage->doEditContent( $newContent, '', EDIT_NEW, false, $wgUser );
			$newTitle = $newPage->getTitle();
			$this->setMainPage( $newTitle->getArticleId() );
		}
		
		$parent_id = array();
		$parent_id[0] = $this->getMainPage();
		$max_level = 0;
		$sequence = 0;
		
		$loopStructureItem = new LoopStructureItem();
		$loopStructureItem->setStructure( $this->getId() );
		$loopStructureItem->setArticle( $this->getMainPage() );
		$loopStructureItem->setPreviousArticle( 0 );
		$loopStructureItem->setNextArticle( 0 );
		$loopStructureItem->setParentArticle( 0 );
		$loopStructureItem->setTocLevel( 0 );
		$loopStructureItem->setSequence( $sequence );
		$loopStructureItem->setTocNumber( '' );
		$loopStructureItem->setTocText( $rootTitleText );
		
		$this->mStructureItems[$sequence] = $loopStructureItem;
		$sequence++;
	
		$regex = "/(<li class=\"toclevel-)(\\d)( tocsection-)(.*)(<span class=\"tocnumber\">)([\\d\\.]+)(<\\/span> <span class=\"toctext\">)(.*)(<\\/span)/";
		preg_match_all($regex, $wikiText, $matches);

		for( $i=0; $i < count( $matches[0] ); $i++ ) {
	
			$tocLevel = $matches[2][$i];
			$tocNumber = $matches[6][$i];
			$tocText = $matches[8][$i];
			$tocArticleId = 0;
		
			$itemTitle = Title::newFromText($tocText);
			$tocArticleId  = $itemTitle->getArticleID();
		
			# create new page for item
			if( $tocArticleId == 0 ) {
				$newPage = WikiPage::factory( Title::newFromText($tocText) );
				$newContent = new WikitextContent( wfMessage( 'loopstructure-default-newpage-content')->inContentLanguage()->text());
				$newPage->doEditContent( $newContent, '', EDIT_NEW,	false, $wgUser );
				$newTitle = $newPage->getTitle();
				$tocArticleId = $newTitle->getArticleId();
			}
	
			# get parent article
			$parent_id[$tocLevel] = $tocArticleId;
	
			if($tocLevel > $max_level) {
				$max_level = $tocLevel;
			}
	
			for( $j = $tocLevel + 1; $j <= $max_level; $j++ ) {
				$parent_id[$j] = 0;  # clear lower levels to prevent using an old value in case some intermediary levels are omitted
			}
	
			$parentArticleId = $parent_id[$tocLevel - 1];
			$parentArticleId = intval($parentArticleId);

			# set next item from the last structure item.
			$previousItem = $this->mStructureItems[$sequence-1];
			$previousArticleId = $previousItem->getArticle();
			$previousItem->setNextArticle( $tocArticleId );

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->setStructure( $this->getId() );
			$loopStructureItem->setArticle( $tocArticleId );
			$loopStructureItem->setPreviousArticle( $previousArticleId );
			$loopStructureItem->setNextArticle( 0 ); # next article will be set when building the next structure item.
			$loopStructureItem->setParentArticle( $parentArticleId );
			$loopStructureItem->setTocLevel( $tocLevel );
			$loopStructureItem->setSequence( $sequence );
			$loopStructureItem->setTocNumber( $tocNumber );
			$loopStructureItem->setTocText( $tocText );
	
			$this->mStructureItems[$sequence] = $loopStructureItem;
			$sequence++;
	
		}
	
		return true;
	
	}
	
	/**
	 * Load items from database
	 */
	public function loadItems() {
	
		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->select(
			'loop_structure_items',
			array(
				'lsi_id',
				'lsi_article',
				'lsi_previous_article',
				'lsi_next_article',
				'lsi_parent_article',
				'lsi_toc_level',
				'lsi_sequence',
				'lsi_toc_number',
				'lsi_toc_text'
			),
			array(),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);
	
		foreach ( $res as $row ) {
	
			if ($row->lsi_toc_level == 0) {
				$this->setMainPage( $row->lsi_article );
			}
	
			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->setId( $row->lsi_id );
			$loopStructureItem->setArticle( $row->lsi_article );
			$loopStructureItem->setPreviousArticle( $row->lsi_previous_article );
			$loopStructureItem->setNextArticle( $row->lsi_next_article );
			$loopStructureItem->setParentArticle( $row->lsi_parent_article );
			$loopStructureItem->setTocLevel( $row->lsi_toc_level );
			$loopStructureItem->setSequence( $row->lsi_sequence );
			$loopStructureItem->setTocNumber( $row->lsi_toc_number );
			$loopStructureItem->setTocText( $row->lsi_toc_text );
	
			$this->mStructureItems[] = $loopStructureItem;
			
		}
	}
	
	/**
	 * Save items to database
	 */
	public function saveItems() {
		foreach( $this->getStructureItems() as $structureItem ) {
			$structureItem->addToDatabase();
		}
	}
	
	/**
	 * Delete all items from database
	 */
	public function deleteItems() {
	
		# check if 
		$dbr = wfGetDB( DB_SLAVE );
		
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'loop_structure_items',
			'*',
			__METHOD__
		);
	
		if(isset($this->mStructureItems)) {
			unset($this->mStructureItems);
		}
		
		return true;
		
	}
	
}	

/**
 *  Class representing a single page of a LoopStructure
 *
 */
class LoopStructureItem {
	
	private $mId;					// id of the structure item
	private $mStructure;			// id of the corresponding structure
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
	
	public function setId( $id ) {
		$this->mId = $id;
	}
	
	public function setStructure( $loopStructureId ) {
		$this->mStructure = $loopStructureId;
	}
	
	public function setArticle( $articleId ) {
		$this->mArticle = $articleId;
	}
	
	public function setParentArticle( $articleId ) {
		$this->mParentArticle = $articleId;
	}
	
	public function setPreviousArticle( $articleId ) {
		$this->mPreviousArticle = $articleId;
	}
	
	public function setNextArticle( $articleId ) {
		$this->mNextArticle = $articleId;
	}
	
	public function setTocLevel( $tocLevel ) {
		$this->mTocLevel = $tocLevel;
	}
	
	public function setSequence( $sequence ) {
		$this->mSequence = $sequence;
	}
	
	public function setTocNumber( $tocNumber ) {
		$this->mTocNumber = $tocNumber;
	}
	
	public function setTocText( $tocText ) {
		$this->mTocText = $tocText;
	}
	
	public function getId() {
		return $this->mId;
	}
	
	public function getStructure() {
		return $this->mStructure;
	}
	
	public function getArticle() {
		return $this->mArticle;
	}
	
	public function getParentArticle() {
		return $this->mParentArticle;
	}
	
	public function getPreviousArticle() {
		return $this->mPreviousArticle;
	}
	
	public function getNextArticle() {
		return $this->mNextArticle;
	}
	
	public function getTocLevel() {
		return $this->mTocLevel;
	}
	
	public function getSequence() {
		return $this->mSequence;
	}
	
	public function getTocNumber() {
		return $this->mTocNumber;
	}
	
	public function getTocText() {
		return $this->mTocText;
	}
	
	/**
	 * Add structure item to the database
	 * @return bool true
	 */
	function addToDatabase() {
		
		if ($this->mArticle!=0) {
			
			$dbw = wfGetDB( DB_MASTER );
			$this->setId( $dbw->nextSequenceValue( 'LoopStructureItem_id_seq' ));
			
			$dbw->insert(
				'loop_structure_items',
				array(
					'lsi_id' => $this->getId(),
					'lsi_article' => $this->getArticle(),
					'lsi_previous_article' => $this->getPreviousArticle(),
					'lsi_next_article' => $this->getNextArticle(),
					'lsi_parent_article' => $this->getParentArticle(),
					'lsi_toc_level' => $this->getTocLevel(),
					'lsi_sequence' => $this->getSequence(),
					'lsi_toc_number' => $this->getTocNumber(),
					'lsi_toc_text' => $this->getTocText()
				),
				__METHOD__
			);
			
			$this->mId = $dbw->insertId();
			
		}
		
		return true;
		
	}
	
	
	/**
	 * Get item for given article and structure from database
	 *
	 * @param int $articleId
	 * @param int $structure
	 */
	public static function newFromIds($article) {
	
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'loop_structure_items',
			array(
				'lsi_id',
				'lsi_article',
				'lsi_previous_article',
				'lsi_next_article',
				'lsi_parent_article',
				'lsi_toc_level',
				'lsi_sequence',
				'lsi_toc_number',
				'lsi_toc_text'
			),
			array(
				'lsi_article' => $article
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);
	
		if ($row = $res->fetchObject()) {
	
			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->setId( $row->lsi_id );
			$loopStructureItem->setArticle( $row->lsi_article );
			$loopStructureItem->setPreviousArticle( $row->lsi_previous_article );
			$loopStructureItem->setNextArticle( $row->lsi_next_article );
			$loopStructureItem->setParentArticle( $row->lsi_parent_article );
			$loopStructureItem->setTocLevel( $row->lsi_toc_level );
			$loopStructureItem->setSequence( $row->lsi_sequence );
			$loopStructureItem->setTocNumber( $row->lsi_toc_number );
			$loopStructureItem->setTocText( $row->lsi_toc_text );
				
			return $loopStructureItem;
				
		} else {
				
			return false;
				
		}
	
	}
}