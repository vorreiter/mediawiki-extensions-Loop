<?php 


/**
 * Class representing a bookstructure and other metainformation
 *
 */
class LoopStructure {
	
	private $id = 0; // id of the structure
	private $properties = array(); // array of structure properties
	private $propertiesLoaded = false; // bool properties loaded from database
	public $mainPage; // article id of the main page
	public $structureItems = array(); // array of structure items

	
	public function LoopStructure() {
		$this->id = 0;
	}

	
	public function getId() {
		return $this->id;
	}
	
	public function render() {
		
		$text = '';
		
		foreach( $this->structureItems as $structureItem ) {
			
			if( intval( $structureItem->tocLevel ) === 0 ) {
				
				$text .= '<h2>'.$structureItem->tocText.'</h2>';
				
			} else {

				if( isset( $structureItem->tocLevel ) && $structureItem->tocLevel > 0 ) {
					$tabLevel = $structureItem->tocLevel;
				} else {
					$tabLevel = 1;
				}
				
				$link = Linker::link(
					Title::newFromID( $structureItem->article ),
					$structureItem->tocNumber .' '. $structureItem->tocText
				); 
				
				$text .= '<div class="loopstructure-level-'.$structureItem->tocLevel.'">' . str_repeat('	',  $tabLevel ) . $link . '</div>';
			
			}
				
		}
		
		return $text;
		
	}
	
	/**
	 * Converts the structureitems to the table of contents as wikitext.
	 */
	public function renderAsWikiText() {
		
		$wikiText = '';

		foreach( $this->structureItems as $structureItem ) {

			if( intval( $structureItem->tocLevel ) === 0 ) {
				$wikiText .= '[['.$structureItem->tocText.']]'.PHP_EOL.PHP_EOL;
			} else {
				$wikiText .= str_repeat( '=', $structureItem->tocLevel ).' '.$structureItem->tocText.' '.str_repeat( '=', $structureItem->tocLevel ).PHP_EOL;
			}
			
		}
		
		return $wikiText;
		
	}
	
	/**
	 * Converts wikitext to LoopStructureItems.
	 * @param $wikiText
	 */
	public function setStructureItemsFromWikiText( $wikiText, User $user ) {

		$regex = "/(<a )(.*?)(>)(.*?)(<\\/a>)/";
		preg_match($regex, $wikiText, $matches);
		$rootTitleText = $matches[4];

		# Title objects has to start with a letter else an error will occur.
		if( ctype_alpha( $rootTitleText[0] )) {
            $rootTitle = Title::newFromText( $rootTitleText );
            if( is_object( $rootTitle )) {
                $this->mainPage = $rootTitle->getArticleID();
            } else {
                return false;
            }
        } else {
		    return false;
        }

		# create new root page
		if( $this->mainPage == 0 ) {
			$newPage = WikiPage::factory( Title::newFromText( $rootTitleText ));
			$newContent = new WikitextContent( wfMessage( 'loopstructure-default-newpage-content' )->inContentLanguage()->text() );
			$newPage->doEditContent( $newContent, '', EDIT_NEW, false, $user );
			$newTitle = $newPage->getTitle();
			$this->mainPage = $newTitle->getArticleId();
		}

		$parent_id = array();
		$parent_id[0] = $this->mainPage;
		$max_level = 0;
		$sequence = 0;
		
		unset( $this->structureItems );
		
		$loopStructureItem = new LoopStructureItem();
		$loopStructureItem->structure = $this->id;
		$loopStructureItem->article = $this->mainPage;
		$loopStructureItem->previousArticle = 0;
		$loopStructureItem->nextArticle = 0;
		$loopStructureItem->parentArticle = 0;
		$loopStructureItem->tocLevel = 0;
		$loopStructureItem->sequence = $sequence;
		$loopStructureItem->tocNumber = '';
		$loopStructureItem->tocText = $rootTitleText;
		
		$this->structureItems[$sequence] = $loopStructureItem;
		$sequence++;
	
		$regex = "/(<li class=\"toclevel-)(\\d)( tocsection-)(.*)(<span class=\"tocnumber\">)([\\d\\.]+)(<\\/span> <span class=\"toctext\">)(.*)(<\\/span)/";
		preg_match_all( $regex, $wikiText, $matches );

		for( $i=0; $i < count( $matches[0] ); $i++ ) {
	
			$tocLevel = $matches[2][$i];
			$tocNumber = $matches[6][$i];
			$tocText = $matches[8][$i];
			$tocArticleId = 0;
		
			$itemTitle = Title::newFromText($tocText);
			$tocArticleId = $itemTitle->getArticleID();
		
			# create new page for item
			if( $tocArticleId == 0 ) {
				$newPage = WikiPage::factory( Title::newFromText( $tocText ) );
				$newContent = new WikitextContent( wfMessage( 'loopstructure-default-newpage-content' )->inContentLanguage()->text());
				$newPage->doEditContent( $newContent, '', EDIT_NEW,	false, $user );
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
			$previousItem = $this->structureItems[$sequence-1];
			$previousArticleId = $previousItem->article;
			$previousItem->nextArticle = $tocArticleId;
			
			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->structure = $this->id;
			$loopStructureItem->article = $tocArticleId;
			$loopStructureItem->previousArticle = $previousArticleId;
			$loopStructureItem->nextArticle = 0; # next article will be set when building the next structure item.
			$loopStructureItem->parentArticle = $parentArticleId;
			$loopStructureItem->tocLevel = $tocLevel;
			$loopStructureItem->sequence = $sequence;
			$loopStructureItem->tocNumber = $tocNumber;
			$loopStructureItem->tocText = $tocText;
	
			$this->structureItems[$sequence] = $loopStructureItem;
			$sequence++;
	
		}
	
		return true;
	
	}
	
	public function getStructureItems() {
		return $this->structureItems;
	}
	
	/**
	 * Load items from database
	 */
	public function loadStructureItems() {
	
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
				'lsi_structure' => $this->id
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);
		
		foreach ( $res as $row ) {
	
			if ($row->lsi_toc_level == 0) {
				$this->mainPage = $row->lsi_article;
			}
			
			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;
	
			$this->structureItems[] = $loopStructureItem;
			
		}
	}
	
	/**
	 * Save items to database
	 */
	public function saveItems() {
		foreach( $this->structureItems as $structureItem ) {
			$structureItem->addToDatabase();
		}
	}
	
	/**
	 * Delete all items from database
	 */
	public function deleteItems() {
	
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'loop_structure_items',
			'*',
			__METHOD__
		);
	
		if( isset( $this->structureItems )) {
			unset( $this->structureItems );
		}
		
		return true;

	}
	
	
	public function lastChanged() {
		$dbr = wfGetDB( DB_SLAVE );
		$last_touched  =  $dbr->selectField(
				array(
						'loop_structure_items',
						'page'
				),
				'max( page_touched )',
				array(
						0 => "page_id = lsi_article",
						1 => "lsi_structure = '".$this->getId()."'"
				),
				__METHOD__
				);
		if ($last_touched) {
			return $last_touched;
		} else {
			return false;
		}
	
	
	}
	
	
	
}	

/**
 *  Class representing a single page of a LoopStructure
 */
class LoopStructureItem {
	
	public $id; // id of the structure item
	public $structure = 0; // id of the corresponding structure
	public $article; // article id of the page
	public $previousArticle; // article id from the previous page
	public $nextArticle; // article id from the next page
	public $parentArticle; // article id from the parent page 
	public $tocLevel; // Level within the corresponding structure
	public $sequence; // Sequential number within the corresponding structure
	public $tocNumber; // string rrepresentation of the chapter number
	public $tocText; // page title

	/**
	 * Add structure item to the database
	 * @return bool true
	 */
	function addToDatabase() {
		
		if ($this->article!=0) {
			
			$dbw = wfGetDB( DB_MASTER );
			$this->id = $dbw->nextSequenceValue( 'LoopStructureItem_id_seq' );
			
			$dbw->insert(
				'loop_structure_items',
				array(
					'lsi_id' => $this->id,
					'lsi_article' => $this->article,
					'lsi_previous_article' => $this->previousArticle,
					'lsi_next_article' => $this->nextArticle,
					'lsi_parent_article' => $this->parentArticle,
					'lsi_toc_level' => $this->tocLevel,
					'lsi_sequence' => $this->sequence,
					'lsi_toc_number' => $this->tocNumber,
					'lsi_toc_text' => $this->tocText
				),
				__METHOD__
			);
			$this->id = $dbw->insertId();
		}
		
		return true;
		
	}

	/**
	 * Get item for given article and structure from database
	 *
	 * @param int $articleId
	 * @param int $structure
	 */
	public static function newFromIds( $article ) {
	
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
	
		if( $row = $res->fetchObject() ) {
	
			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;
				
			return $loopStructureItem;
				
		} else {
				
			return false;
				
		}
	
	}
	
	public function getPreviousChapterItem () {
		
		$dbr = wfGetDB( DB_SLAVE );
		$prev_id =  $dbr->selectField(
			'loop_structure_items',
			'lsi_article',
			array(
				0 => "lsi_sequence < '".$this->sequence."'",
				1 => "lsi_structure = '".$this->structure."'",
				2 => "lsi_toc_level = 1"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence DESC'
			)
		);
		
		if( ! empty( $prev_id )) {
			return LoopStructureItem::newFromIds( $prev_id );
		} else {
			return false;
		}
		
	}
	
	public function getNextChapterItem () {
		
		$dbr = wfGetDB( DB_SLAVE );
		$next_id = $dbr->selectField(
			'loop_structure_items',
			'lsi_article',
			array(
				0 => "lsi_sequence > '".$this->sequence."'",
				1 => "lsi_toc_level = 1"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);
		
		if( ! empty( $next_id ) ) {
			return LoopStructureItem::newFromIds( $next_id );
		} else {
			return false;
		}
		
	}
	
	public function getPreviousItem () {
		
		if( isset( $this->previousArticle ) ) {
			return LoopStructureItem::newFromIds( $this->previousArticle );
		} else {
			return false;
		}
		
	}
	
	public function getNextItem () {
		
		if( isset( $this->nextArticle ) ) {
			return LoopStructureItem::newFromIds( $this->nextArticle );
		} else {
			return false;
		}
		
	}
	
	public function getParentItem () {
		
		if( isset( $this->parentArticle ) ) {
			return LoopStructureItem::newFromIds( $this->parentArticle );
		} else {
			return false;
		}
		
	}
	
	public function getBreadcrumb ( $max_len = 100 ) {
	
		$breadcrumb = '<li class="active">' . $this->tocNumber . ' ' . $this->tocText .'</li>';
		$len = strlen( $this->tocNumber ) + strlen( $this->tocText ) + 1;
		$level = $this->tocLevel;
	
		$items = array();
		$item = $this->getParentItem();
		$toc_number_len = 0;
		
		while( $item !== false ) {
			$items[] = $item;
			$toc_number_len = $toc_number_len + strlen ( $item->tocNumber ) + 1;
			$item = $item->getParentItem();
		}
	
		$max_text_len = $max_len - $len - $toc_number_len;
		
		if ( $level == 0 ) {	
			$level = 1;
		}
		
		$max_item_text_len = floor( $max_text_len / $level );
	
		foreach( $items as $item ) {
				
			if( strlen( $item->tocText ) > $max_item_text_len ) {
				$link_text = mb_substr( $item->tocText, 0, ( $max_item_text_len - 2 ) ) . '..';
			} else {
				$link_text = $item->tocText;
			}
			
			$title = Title::newFromID( $item->article );
			$link = Linker::link( $title, $item->tocNumber .' '. $link_text );
			$breadcrumb = '<li>' . $link .'</li>' . $breadcrumb;
			
		}
	
		$breadcrumb = '<ol class="breadcrumb" id="breadcrumb">' . $breadcrumb . '</ol>';
		return $breadcrumb;
		
	}
	
	public function lastChanged() {
		
		$dbr = wfGetDB( DB_SLAVE );
		$last_touched  =  $dbr->selectField(
			array(
				'loop_structure_items',
				'page'
			),
			'max( page_touched )',
			array(
				0 => "page_id = lsi_article",
				1 => "lsi_article = '".$this->article."'"
			),
			__METHOD__
		);
		
		if( ! empty( $last_touched )) {
			return $last_touched;
		} else {
			return false;
		}
	
	}
	
}