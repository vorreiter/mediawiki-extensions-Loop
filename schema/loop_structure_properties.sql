CREATE TABLE IF NOT EXISTS /*_*/loop_structure_properties (
  `lsp_structure` int(11) NOT NULL,
  `lsp_property` varbinary(255) NOT NULL,
  `lsp_value` blob
) /*$wgDBTableOptions*/;