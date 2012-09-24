TableBundle
===========

TODO
----

In order of importance.

1. [DONE] Sorting
2. Handling joins with PropelTableBuilder
3. [DONE] Filter (This is not directly related to TableBundle)
4. [DONE] Table types
5. [DONE] Column extensions
6. Hiding and showing columns
7. Multiselect
8. Batch actions
9. Editable


Column Types
------------
#####TextColumn


#####DateTimeColumn
   
Parameters:

  * show_date: boolean
  * show_time: boolean
   
#####EmailColumn
   
   Parameters: none
    
#####ForeignKeyColumn
   
   Parameters: 

  * foreign_table: TableMap of the foreign table

#####ActionsColumn

   Parameters:

  * actions:
 
ActionColumns example:

        array('action_name' => array(
            'icon' => 'icon.png', // default: same as 'action_name'
            'title' => 'Edit',    // default: ucfirst('action_name')
            'route' => ...,       // see LinkColumnExtension route
            'disabled' => true    // default: false
        ), 'next_action' => array(...);


Column Extensions
-----------------
#####LinkColumnExtension

   Parameters:

  * route: array("your_route_name", array('id' => PersoonPeer::ID), array('fixed_param' => 'somevalue'))
 