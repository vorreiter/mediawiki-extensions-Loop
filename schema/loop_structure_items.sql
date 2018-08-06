-- Add table for loop structure items
CREATE TABLE IF NOT EXISTS /*_*/loop_structure_items (
	lsi_id int(10) unsigned NOT NULL AUTO_INCREMENT,
	lsi_structure int(10) unsigned NOT NULL,
	lsi_article int(10) unsigned NOT NULL,
	lsi_previous_article int(10) unsigned NOT NULL,
	lsi_next_article int(10) unsigned NOT NULL,
	lsi_parent_article int(10) unsigned NOT NULL,
	lsi_toc_level int(10) unsigned NOT NULL,
	lsi_sequence int(10) unsigned NOT NULL,
	lsi_toc_number varbinary(255) NOT NULL,
	lsi_toc_text varbinary(255) NOT NULL,
	PRIMARY KEY (lsi_id),
	UNIQUE KEY structure_article (lsi_structure,lsi_article)
)/*$wgDBTableOptions*/;
