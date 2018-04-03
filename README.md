# Algolia Frontend

Replaces the standard algolia frontend with our own. Forces algolia to use specific settings.

Define the indexes to search as a constant. 

define('ALGOLIA_FRONTEND_INDEXES', array(
    array('index_name_key', '50', "Invånare"),
    array('index_name_key_second', '100', "Företagare")
); 

The second parameter indicate max number of resuls to return from this index. 
