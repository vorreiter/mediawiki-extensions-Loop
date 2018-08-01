<?php 

class LoopUpdater {
	
	
	/**
	 * Updates Database
	 * 
	 * @param DatabaseUpdater $du
	 * @return bool true
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		
		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_items', dirname( __FILE__ ) . '/loop_structure_items.sql', true ));
		
		return true;
	}
	
}
?>