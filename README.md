TableBundle
===========

Early, untested alpha version.

    // Create a table for Persoon objects.
    // All database columns are automatically added.
    $table = new ObjectTable(PersoonQuery::create());

Let's try something less default, take a look at the Persoon schema.

    <behavior name="auto_add_pk" />
    <behavior name="timestampable" />

    <table name="persoon">
        <column name="voornaam" type="varchar" size="100" />
        <column name="achternaam" type="varchar" size="100" />
    </table>

"| Voornaam | Achternaam | Id | Created at | Updated at" is a bit weird of a
setup. Let's say we want "Id" to be the first column, oh and, rename it to "Nr.",
also, "Created at" and "Updated at" are of little use to our users, let's hide
those.

    // ObjectTable uses BasePeer::TYPE_RAW_COLNAME to identify columns.
    $columns = array(
        array('colname' => 'ID', 'order' => 0, 'displayname' => 'Nr.'),
        array('colname' => 'CREATED_AT', 'visible' => false),
        array('colname' => 'UPDATED_AT', 'visible' => false)
    );

    $table = new ObjectTable(PersoonQuery::create(), $columns);

Coo'coo'cool, but, I want to display last and first name in one column.
TODO: it seems I forgot to implement this

       
    // ObjectTable uses BasePeer::TYPE_RAW_COLNAME to identify columns.
    $columns = array(
        array('colname' => 'ID', 'order' => 0, 'displayname' => 'Nr.'),
        array('colname' => 'VOORNAAM', 'visible' => false),
        array('colname' => 'ACHTERNAAM', 'visible' => false),
        array('colname' => 'CREATED_AT', 'visible' => false),
        array('colname' => 'UPDATED_AT', 'visible' => false),
        array('displayname' => 'Naam', 'method' => 'getNaam')
    );

    $table = new ObjectTable(PersoonQuery::create(), $columns);

And there you have it.

Note: Try at risk of own sanity, not a single test was performed :P
Note: No, I did test parse errors.
