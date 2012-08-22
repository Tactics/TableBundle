TableBundle
===========

TODO
----

In order of importance.

1. Sorting
2. Handling joins with PropelTableBuilder
3. Filter (This is not directly related to TableBundle)
4. Table types
5. Column extensions
6. Hiding and showing columns
7. Multiselect
8. Batch actions
9. Editable


Column Types
------------
 * TextColumn

 * ActionsColumn

 * DateTimeColumn
   
   Parameters:
    * show_date: boolean
    * show_time: boolean
   
 * EmailColumn
   
   Parameters: none
    
 * ForeignKeyColumn
   
   Parameters: 
    * foreign_table: TableMap of the foreign table



Column Extensions
-----------------
 * LinkColumnExtension

   Parameters:
    * route: array("your_route_name", array('id' => PersoonPeer::ID), array('fixed_param' => 'somevalue'))
 