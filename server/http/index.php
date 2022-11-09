<?php

$database = '/Users/famille/Desktop/analyzeG3/data/analyzers.sqlite';
$database = __DIR__.'/../../data/analyzers.sqlite';

var_dump(file_exists($database));

$sqlite = new Sqlite3($database);
if (!file_exists($database)) {
    print "Warning : $database doesn't exist\n";
    die();
}
if (!is_writeable($database)) {
    print "Warning : $database is not writeable\n";
    die();
}
if (!is_writeable(dirname($database))) {
    print "Warning : $database folder is not writeable\n";
    die();
}

if (isset($_GET['mode']) && in_array($_GET['mode'], array('Analyzer', 'Categories'))) {
    $mode = $_GET['mode'];
} elseif (isset($_POST['mode']) && in_array($_POST['mode'], array('Analyzer', 'Categories'))) {
    $mode = $_POST['mode'];
} else {
    $mode = 'Analyzer';
}

if (isset($_GET['id']) && (int) $_GET['id']) {
    $id = (int) $_GET['id'];
} elseif (isset($_POST['id']) && (int) $_POST['id']) {
    $id = (int) $_POST['id'];
} else {
    $id = 1;
}

if (isset($_POST['Save'])) {
    if ($mode == 'Analyzer') {
        $res = $sqlite->query('SELECT c.id FROM analyzers AS a JOIN analyzers_categories AS ac ON a.id = ac.id_analyzer JOIN categories AS c ON ac.id_categories = c.id WHERE a.id ='.$id);

        $checked = array();
        while($row = $res->fetchArray()) {
            $checked[] = $row['id'];
        }
    
        // to remove
        $categories = $_POST['categories'] ?: array();

        $to_remove = array_diff($checked, $categories);
        if (!empty($to_remove)) {
            $query = 'DELETE FROM analyzers_categories WHERE id_analyzer = '.$_POST['id'].' AND id_categories IN ('.join(',', $to_remove).')';

            $res = $sqlite->query($query);
        }

        // to add
        $to_add = array_values(array_diff($categories, $checked));
    
        if (!empty($to_add)) {
            $query = "INSERT INTO 'analyzers_categories'
          SELECT '{$_POST['id']}' AS 'id_analyzer', '{$to_add[0]}' AS 'id_category'";
            unset($to_add[0]);
            foreach($to_add as $id_category) {
                $query .= "UNION SELECT '{$_POST['id']}', '$id_category'";
            }
            
            $res = $sqlite->query($query);
        }
    }

    if ($mode == 'Categories') {
        $res = $sqlite->query('SELECT a.id FROM categories AS c JOIN analyzers_categories AS ac ON c.id = ac.id_categories JOIN analyzers AS a ON ac.id_analyzer = a.id WHERE c.id = '.$id);

        $checked = array();
        while($row = $res->fetchArray()) {
            $checked[] = $row['id'];
        }
        
        // to remove
        $analyzers = isset($_POST['analyzers']) ? $_POST['analyzers'] : array();

        $to_remove = array_diff($checked, $analyzers);
        if (!empty($to_remove)) {
            $query = 'DELETE FROM analyzers_categories WHERE id_categories = '.$_POST['id'].' and id_analyzer in ('.join(',', $to_remove).')';

            $res = $sqlite->query($query);
        }

        // to add
        $to_add = array_values(array_diff($analyzers, $checked));
    
        if (!empty($to_add)) {
            $query = "INSERT INTO 'analyzers_categories'
      SELECT '{$to_add[0]}' AS 'id_analyzer', '{$_POST['id']}' AS 'id_category'\n";
            unset($to_add[0]);
            foreach($to_add as $id_analyzer) {
                $query .= "UNION SELECT '$id_analyzer', '{$_POST['id']}'\n";
            }
            
            $res = $sqlite->query($query);
        }
    }
}


if ($mode == 'Categories') {
    display_form_category($id);
} else {
    display_form_analyzer($id);
}

function display_form_category(int $id) : void {
    display_form($id, 'Categories');
}

function display_form_analyzer(int $id) : void {
    display_form($id, 'Analyzer');
}


function display_form(int $id, string $mode) : void {
    global $sqlite;
    
    if ($mode == "Analyzer") {
        $main_table = 'analyzers';
        $second_table = 'categories';
        $main_column = 'analyzer';
        $second_column = 'categories';
        
        $other_mode = 'Categories';
    } else {
        $main_table = 'categories';
        $second_table = 'analyzers';
        $other_mode = 'Analyzer';

        $main_column = 'categories';
        $second_column = 'analyzer';
    }
    
    print "<form action=\"{$_SERVER['PHP_SELF']}\" method=\"POST\">";
    print '<input type="hidden" name="mode" value="'.$mode.'"><br />';

    $res = $sqlite->query('SELECT * FROM '.$main_table.' WHERE id = '.$id);
    $analyzer = $res->fetchArray();

    $res = $sqlite->query('SELECT c.id FROM '.$main_table.' AS a JOIN analyzers_categories AS ac ON a.id = ac.id_'.$main_column.'  JOIN '.$second_table.' AS c ON ac.id_'.$second_column.' = c.id WHERE a.id = '.$id);
    
    $checked = array();
    while($row = $res->fetchArray()) {
        $checked[] = $row['id'];
    }

    if ($mode == 'Analyzer') {
        print "<p>".$analyzer['folder']."/".$analyzer['name'];
    } else {
        print "<p>".$analyzer['name'];
    }
    print '<input type="hidden" name="id" value="'.$analyzer['id'].'"></p>'."\n";
    
    
    print "</p>";
    
    $res = $sqlite->query('SELECT * FROM '.$second_table.' AS c ORDER BY name');
    while ($row = $res->fetchArray()) {
        $c = in_array($row['id'], $checked) ? " checked" : "";
        print '<input type="checkbox" name="'.$second_table.'[]" value="'.$row['id'].'"'.$c.'> <a href="'.$_SERVER['PHP_SELF'].'?mode='.$other_mode.'&id='.$row['id'].'">'.(isset($row['folder']) ? $row['folder'].'/' : '').$row["name"].'</a><br />';
    }
    
    print '<input type="submit" name="Save" value="Save" />';
    print '<input type="reset" name="Cancel" /><br />';
    print "</form>";
}

?>