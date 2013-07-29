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
    * icon
    * title
    * route
    * disabled
    * enabled_if
 
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
 

ColumnHeader types
------------------

#####SortableColumnHeader

If you create multiple tables in one action that are supposed to be sorted,
you'll have to specify the sorter's session key.

```
$builder->add('Foo', 'text', 'sortable', array(
    'header/sorter_namespace' => 'key'
))
```

If you use the PropelTableBuilder, you can set the sorter key using the
```setSorterNamespace``` method.


ModelCriteriaSorter
-------------------

A very simple interface to do stuff with Propel's \ModelCriteria.
Multisort \ModelCriteria based on request and session parameters.

Let's say you have a page that displays a simple list of Foo's.

```
/**
 * @Route("foo/list", name="foo_list")
 */
public function listAction
{
    $foos = FooQuery::create();
}
```

Now you want the user to be able to sort these Foo's. 
You also want to save these sorting settings in the session so that when the
user leaves the page and returns, the same sorting settings are still intact.

This is where ModelCriteriaSorter comes in.

```
$sorter = new ModelCriteriaSorter($this->container);
$foos = $sorter->execute($foos);
```

You can specify the session key, if none given, ModelCriteriaSorter will create
a default one.

```
$sorter->execute($foos, 'sorter/special_key');
```

Now you can send POST and GET request to the listAction to sort your Foo's.
ModelCriteriaSorter will look for following parameters:

* asc ``` $generator->generate('foo_list', array('asc' => FooPeer::ID)) ```
* desc ``` $generator->generate('foo_list', array('desc' => FooPeer::ID)) ```
* unsort ``` $generator->generate('foo_list', array('unsort' => FooPeer::ID)) ```

Using multiple tables on one page
---------------------------------

Using multiple tables on one page becomes a problem when they contain sortable columns and pagers.
In this case, you can namespace your table using the TacticsTableBuilder.

```
// Assuming you are in a controller that extends from TacticsController.

$builder = $this->createTableBuilder(new BazTableType(), array(
   'namespace' => 'foo/baz'
));

$builder2 = $this->createTableBuilder(new BarTableType(), array(
   'namespace' => 'foo/bar'
));
```

Rendering a pagerfanta that makes use of the namespace is troublesome, you'll have to pass the builder
instances to your template.

```
   return $this->render('FooBundle:Foo:show.html.twig', array(
      'builder'  => $builder,
      'builder2' => $builder2
   ))
```

And render the pager using the pager_widget twig function instead of the pagerfanta twig function.

```
   {{ pager_widget(builder) }}
```


