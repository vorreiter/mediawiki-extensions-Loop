<?php


/**
 *  Special page representing the table of contents
 */
 
class SpecialLoopStructure extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'LoopStructure' );
	}

	public function execute($sub) {
		
		global $wgSecretKey;
		
		$user = $this->getUser();
		$this->setHeaders();
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'loopstructure-specialpage-title' ) );

		$tabindex = 0;

        # headline output
        $out->addHtml(
            Html::rawElement(
                'h1',
                array(
                    'id' => 'loopstructure-h1'
                ),
                $this->msg( 'loopstructure-specialpage-title' )->parse()
            )
        );

		$loopStructure = new LoopStructure();
		$loopStructure->loadItems();
		$currentStructureAsWikiText = $loopStructure->getStructureItemsAsWikiText();

        $request = $this->getRequest();
        $saltedToken = $user->getEditToken( $wgSecretKey, $request );
		$newStructureContent = $request->getText( 'loopstructure-content' );
		$requestToken = $request->getText( 't' );

		$userIsPermitted = (! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ));
		
		$error = false;
		$feedbackMessageClass = 'success';

		if( ! empty( $newStructureContent ) && ! empty( $requestToken )) {
			if( $userIsPermitted ) {
				if( $user->matchEditToken( $requestToken, $wgSecretKey, $request )) {

					# the content was changend
					# use local parser to get a default parsed result
					$localParser = new Parser();
					$tmpTitle = Title::newFromText( 'NO TITLE' );
                    $parserOutput = $localParser->parse( $newStructureContent, $tmpTitle, new ParserOptions() );

					if( is_object( $parserOutput )) {

						$parsedStructure = $parserOutput->mText;

						if( ! empty( $parsedStructure )) {

							$tmpLoopStructure = new LoopStructure();
							$parseResult = $tmpLoopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );

							if( $parseResult !== false ) {

                                $newStructureContentParsedWikiText = $tmpLoopStructure->getStructureItemsAsWikiText();

                                # if new parsed structure is different to the new one save it
                                if( $currentStructureAsWikiText != $newStructureContentParsedWikiText ) {

                                    $loopStructure->deleteItems();
                                    $loopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );
                                    $loopStructure->saveItems();
                                    $currentStructureAsWikiText = $loopStructure->getStructureItemsAsWikiText();

                                    # save success output
                                    $out->addHtml(
                                        Html::rawElement(
                                            'div',
                                            array(
                                                'name' => 'loopstructure-content',
                                                'class' => 'alert alert-'.$feedbackMessageClass
                                            ),
                                            $this->msg( 'loopstructure-save-success' )->parse()
                                        )
                                    );

                                } else {
                                    $error = $this->msg( 'loopstructure-save-equal-error' )->parse();
                                    $feedbackMessageClass = 'warning';
                                }

                            } else {
                                $error = $this->msg( 'loopstructure-save-parse-error' )->parse();
                                $feedbackMessageClass = 'danger';
                            }
								
						} else {
							$error = $this->msg( 'loopstructure-save-parsed-structure-error' )->parse();
                            $feedbackMessageClass = 'danger';
						}
					
					} else {
						$error = $this->msg( 'loopstructure-save-parse-error' )->parse();
                        $feedbackMessageClass = 'danger';
					}
					
				} else {
					$error = $this->msg( 'loop-token-error' )->parse();
                    $feedbackMessageClass = 'danger';
				}

			} else {
				$error = $this->msg( 'loop-permission-error' )->parse();
                $feedbackMessageClass = 'danger';
			}
			
		}

        # error message output (if exists)
        if( $error !== false ) {
            $out->addHTML(
                Html::rawElement(
                    'div',
                    array(
                        'class' => 'alert alert-'.$feedbackMessageClass,
                        'role' => 'alert'
                    ),
                    $error
                )
            );
        }
        
        if( $userIsPermitted ) {
        	
        	# user is permitted to edit the toc, print edit form here
        	
	        $out->addHTML(
	            Html::openElement(
	                'form',
	                array(
	                    'class' => 'mw-editform mt-3 mb-3',
	                    'id' => 'loopstructure-form',
	                    'method' => 'post',
	                    'enctype' => 'multipart/form-data'
	                )
	            )
	            . Html::rawElement(
	                'textarea',
	                array(
	                    'name' => 'loopstructure-content',
	                    'id' => 'loopstructure-textarea',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'd-block mt-3',
	                    '',
	                    'style' => 'width: 700px; height: 700px;' # TODO set size in skin, remove it from here later
	                ),
	                $currentStructureAsWikiText
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'hidden',
	                    'name' => 't',
	                    'id' => 'loopstructure-token',
	                    'value' => $saltedToken
	                )
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'submit',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'btn btn-primary mt-3',
	                    'id' => 'loopstructure-submit',
	                    'value' => $this->msg( 'submit' )->parse()
	                )
	            )
	            . Html::closeElement(
	                'form'
	            )
	        );
	        
        } else {

        	# user has no permission, just show content without textarea
        	
        	$out->addHtml(
        		Html::rawElement(
        			'div',
        			array(
        				'class' => 'alert alert-dark',
        				'role' => 'alert',
        				'style' => 'white-space: pre;'
        			),
        			$currentStructureAsWikiText
        		)
        	);

        }

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
