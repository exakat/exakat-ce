<?php declare(strict_types = 1);

namespace Exakat\Dump;

use SQLite3Exception;
use Exakat\Reports\Helpers\Results;
use const STRICT_COMPARISON;

class Dump2 extends Dump1 {
    private const VERSION = 2;

    protected function initDump(): void {
        $query = <<<'SQL'
CREATE TABLE themas (  id    INTEGER PRIMARY KEY AUTOINCREMENT,
                       thema STRING,
                       CONSTRAINT "themas" UNIQUE (thema) ON CONFLICT IGNORE
                    )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE results (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                        fullcode STRING,
                        file STRING,
                        line INTEGER,
                        namespace STRING,
                        class STRING,
                        function STRING,
                        analyzer STRING,
                        severity STRING
                     )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE resultsCounts ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                             analyzer STRING,
                             count INTEGER DEFAULT -6,
                             CONSTRAINT "analyzers" UNIQUE (analyzer) ON CONFLICT REPLACE
                           )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE hashAnalyzer ( id INTEGER PRIMARY KEY,
                            analyzer STRING,
                            key STRING UNIQUE,
                            value STRING
                          );
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE hashResults ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                            name STRING,
                            key STRING,
                            value STRING
                          );
SQL;
        $this->sqlite->query($query);

        // Name spaces
        $query = <<<'SQL'
CREATE TABLE namespaces (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                           namespace STRING
                        )
SQL;
        $this->sqlite->query($query);
        $this->sqlite->query("INSERT OR IGNORE INTO namespaces VALUES (1, '\\')");

        $query = <<<'SQL'
CREATE TABLE cit (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name STRING,
                    namespaceId INTEGER DEFAULT 1,
                    type STRING,
                    abstract INTEGER,
                    final INTEGER,
                    readonly INTEGER,
                    begin INTEGER,
                    end INTEGER,
                    file INTEGER,
                    line INTEGER,
                    extends STRING DEFAULT ""
                  )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE phpdoc (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                       type STRING,
                       type_id INTEGER,
                       phpdoc STRING
                  )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE cit_implements (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                               implementing INTEGER,
                               implements STRING,
                               type    STRING,
                               options STRING
                            )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE methods (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                        method INTEGER,
                        citId INTEGER,
                        static INTEGER,
                        final INTEGER,
                        abstract INTEGER,
                        reference INTEGER,
                        visibility STRING,
                        returntype_type STRING,
                        begin INTEGER,
                        end INTEGER
                     )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE arguments (id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name STRING,
                        citId INTEGER,
                        methodId INTEGER,
                        rank INTEGER,
                        reference INTEGER,
                        variadic INTEGER,
                        init STRING,
                        expression INTEGER,
                        hasDefault INTEGER,
                        line INTEGER,
                        typehint_type STRING
                     )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE typehints (id INTEGER PRIMARY KEY AUTOINCREMENT,
                        type STRING,
                        object INTEGER,
                        name STRING,
                        fnp STRING
                     )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE properties (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                           property INTEGER,
                           citId INTEGER,
                           visibility STRING,
                           var INTEGER,
                           static INTEGER,
                           readonly INTEGER,
                           hasDefault INTEGER,
                           value STRING,
                           expression INTEGER,
                           line INTEGER,
                           typehint_type STRING
                           )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE classconstants ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                              constant INTEGER,
                              citId INTEGER,
                              visibility STRING,
                              value STRING
                            )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE constants (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                          constant INTEGER,
                          namespaceId INTEGER,
                          file STRING,
                          value STRING,
                          type STRING,
                          expression INTEGER
                       )
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE attributes ( id INTEGER PRIMARY KEY AUTOINCREMENT,
                          type STRING,
                          type_id INTEGER,
                          attribute STRING
)
SQL;
        $this->sqlite->query($query);

        $query = <<<'SQL'
CREATE TABLE functions (  id INTEGER PRIMARY KEY AUTOINCREMENT,
                          function STRING,
                          type STRING,
                          namespaceId INTEGER,
                          returntype_type STRING,
                          reference INTEGER,
                          file STRING,
                          begin INTEGER,
                          end INTEGER,
                          CONSTRAINT "unique" UNIQUE (function, begin)
)
SQL;
        $this->sqlite->query($query);

        $this->collectDatastore();
        $this->initTablesList();

        $time   = time();
        try {
            $id     = random_int(0, PHP_INT_MAX);
        } catch (\Throwable $e) {
            die("Couldn't generate an id for the current dump file. Aborting");
        }

        if (file_exists($this->sqliteFilePrevious)) {
            $sqliteOld = new \Sqlite3($this->sqliteFilePrevious);
            $sqliteOld->busyTimeout(\SQLITE3_BUSY_TIMEOUT);

            $presence = $sqliteOld->querySingle('SELECT count(*) FROM sqlite_master WHERE type="table" AND name="hash"');
            if ($presence == 1) {
                $serial = $sqliteOld->querySingle('SELECT value FROM hash WHERE key="dump_serial"') + 1;
            } else {
                $serial = 0;
            }
        } else {
            $serial = 1;
        }

        $toDump = array(array('', 'dump_time',   $time),
                        array('', 'dump_id',     $id),
                        array('', 'dump_serial', $serial),
                        array('', 'dump_version', self::VERSION)
                        );

        $this->storeInTable('hash', $toDump);
        display('Inited tables');
    }

    public function fetchAnalysersCounts(array $analysers): Results {
        $query = 'SELECT analyzer, count FROM resultsCounts WHERE analyzer IN (' . makeList($analysers) . ')';
		try {
	        $res = $this->sqlite->query($query);
		} catch (SQLite3Exception $e) {
			$res = false;
		}

        return new Results($res);
    }

    public function fetchTable(string $table, array $cols = array()): Results {
        if (empty($cols)) {
            $cols = '*';
        } else {
            $list = array();
            foreach ($cols as $k => $col) {
                if (is_int($k)) {
                    $list[] = $col;
                } else {
                    $list[] = "$col as $k";
                }
            }
            $cols = implode(', ', $list);
        }

        if (!in_array($table, $this->tablesList, STRICT_COMPARISON)) {
            return new Results();
        }

        $query = "SELECT $cols FROM $table";
        $res = $this->sqlite->query($query);

        return new Results($res);
    }

    public function getExtensionList(): Results {
        $query = <<<'SQL'
SELECT analyzer, count(*) AS count FROM results 
    WHERE analyzer LIKE "Extensions/Ext%"
    GROUP BY analyzer
    ORDER BY count(*) DESC
SQL;

        return $this->query($query);
    }

    public function fetchHash(string $key): Results {
        $query = <<<SQL
SELECT value FROM hash WHERE key = "$key"
SQL;

        return $this->query($query);
    }

    public function fetchHashResults(string $key): Results {
        $query = <<<SQL
SELECT key, value FROM hashResults
WHERE name = "$key"
ORDER BY key + 0
SQL;

        return $this->query($query);
    }

    public function fetchHashAnalyzer(string $analyzer): Results {
        $query = <<<SQL
SELECT key, value FROM hashAnalyzer
WHERE analyzer = "$analyzer"
SQL;

        return $this->query($query);
    }

    public function getCit(string $type = 'class'): Results {
        assert(in_array($type, array('class', 'trait', 'interface'), STRICT_COMPARISON));

        $query = "SELECT name FROM cit WHERE type='$type' ORDER BY name";

        return $this->query($query);
    }

    private function query(string $query): Results {
        $res = $this->sqlite->query($query);

        return new Results($res);
    }

    public function fetchTableFunctions(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT functions.*, 
GROUP_CONCAT((CASE typehints.type WHEN ' ' THEN '' ELSE typehints.type || ' ' END ) || 
              CASE arguments.reference WHEN 0 THEN '' ELSE '&' END || 
              CASE arguments.variadic WHEN 0 THEN '' ELSE '...' END  || arguments.name || 
              (CASE arguments.init WHEN ' ' THEN '' ELSE ' = ' || arguments.init END),
             ', ' ) AS signature

FROM functions

LEFT JOIN arguments
    ON functions.id = arguments.methodId AND
       arguments.citId = 0
LEFT JOIN typehints
     ON arguments.id = typehints.object AND
        typehints.type = 'argument'
GROUP BY functions.id

SQL
        );

        return new Results($res);
    }

    public function fetchTableProperties(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT properties.*, 
       properties.property AS signature,
       cit.type AS type,
       lower(namespaces.namespace) || lower(cit.name) || '::' || lower(properties.property) AS fullnspath,
       cit.name AS class,
       cit.file AS file,
       group_concat(typehints.name) AS typehint

    FROM properties
    JOIN cit
        ON properties.citId = cit.id
    JOIN namespaces 
        ON cit.namespaceId = namespaces.id
    LEFT JOIN typehints
        ON properties.id = typehints.object AND
          typehints.type = 'property'
    GROUP BY properties.id
SQL
        );

        return new Results($res);
    }

    public function fetchTableMethods(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT methods.*, 
       GROUP_CONCAT((CASE typehints.name WHEN ' ' THEN '' ELSE typehints.name || ' ' END ) || 
                     CASE arguments.reference WHEN 0 THEN '' ELSE '&' END || 
                     CASE arguments.variadic WHEN 0 THEN '' ELSE '...' END  || arguments.name || 
                     (CASE arguments.init WHEN ' ' THEN '' ELSE ' = ' || arguments.init END),
                    ', ' ) AS signature,
       cit.type AS type,
       lower(namespaces.namespace) || lower(cit.name) || '::' || lower(methods.method) AS fullnspath,
       cit.name AS class,
       cit.file AS file,
       methods.begin AS line,
       group_concat(typehints.name) AS returntype

    FROM methods
    LEFT JOIN typehints
        ON methods.id = typehints.object AND
          typehints.type = 'method'
    LEFT JOIN arguments
        ON methods.id = arguments.methodId
    JOIN cit
        ON methods.citId = cit.id
    JOIN namespaces 
        ON cit.namespaceId = namespaces.id
	WHERE methods.method = 'isError'

    GROUP BY methods.id
SQL
        );

        return new Results($res);
    }

    public function fetchTableFunctionsByArgument(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT lower(namespaces.namespace) || lower(functions.function) AS fullnspath,
       functions.function,
       functions.type,
       arguments.name AS argument,
       init,
       group_concat(typehints.fnp) as typehint, 
       rank,
       arguments.line,
       files.file AS file,
       functions.begin AS line,
       functions.type as type
FROM functions
JOIN arguments 
    ON functions.id = arguments.methodId
LEFT JOIN namespaces 
    ON functions.namespaceId = namespaces.id
LEFT JOIN typehints 
    ON arguments.id = typehints.object AND
    	typehints.type = 'argument'
JOIN files
    ON functions.file = files.id
GROUP BY functions.id
SQL
        );

        return new Results($res);
    }

    public function fetchTableFunctionsByReturnType(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT namespaces.namespace || lower(functions.function) AS fullnspath,
       group_concat(typehints.fnp) as returntype, 
       functions.type,
       functions.function,
       files.file AS file,
       functions.begin AS line
FROM functions
LEFT JOIN namespaces 
    ON functions.namespaceId = namespaces.id
LEFT JOIN typehints 
    ON functions.id = typehints.object AND
    	typehints.type = 'function'
JOIN files
    ON functions.file = files.id
GROUP BY functions.id
SQL
        );

        return new Results($res);
    }

    public function fetchTableMethodsByArgument(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT cit.type || ' ' || cit.name AS theClass, 
       cit.type AS citType,
       cit.name AS citName, 
       lower(namespaces.namespace) || lower(cit.name) || '::' || lower(methods.method) AS fullnspath,
       methods.method,
       arguments.name AS argument,
       init,
       group_concat(typehints.name) as typehint,
       group_concat(typehints.fnp) as fnp,
       rank,
       arguments.line,
       cit.file
FROM cit
JOIN methods 
    ON methods.citId = cit.id
LEFT JOIN typehints 
    ON methods.id = typehints.object AND
    	typehints.type = 'method'
LEFT JOIN arguments 
    ON methods.id = arguments.methodId AND
       arguments.citId != 0
LEFT JOIN namespaces 
    ON cit.namespaceId = namespaces.id
WHERE cit.type in ("class", "trait", "interface", "enum")
GROUP BY methods.id
ORDER BY fullnspath
SQL
        );

        return new Results($res);
    }

    public function fetchTableMethodsByReturnType(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT cit.type || ' ' || cit.name AS theClass, 
       namespaces.namespace || "\\" || lower(cit.name) AS fullnspath,
       group_concat(typehints.name) as returntype, 
       methods.method
FROM cit
JOIN methods 
    ON methods.citId = cit.id
JOIN typehints 
    ON methods.id = typehints.object
JOIN namespaces 
    ON cit.namespaceId = namespaces.id
WHERE cit.type in ("class", "trait", "interface")
ORDER BY fullnspath
SQL
        );

        return new Results($res);
    }

    public function fetchTableClassConstants(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT cit.name AS class, 
       classconstants.constant AS constant, 
       value, 
       namespaces.namespace || lower(cit.name) AS fullnspath,
       visibility,
       constant,
       cit.type AS type

FROM classconstants 
JOIN cit 
    ON cit.id = classconstants.citId
JOIN namespaces 
    ON cit.namespaceId = namespaces.id

    ORDER BY cit.name, classconstants.constant, value

SQL
        );

        return new Results($res);
    }

    public function fetchTableConstants(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT constants.constant AS constant, 
       value, 
       namespaces.namespace AS namespace,
       constant

FROM constants 
JOIN namespaces 
    ON constants.namespaceId = namespaces.id

    ORDER BY namespaces.namespace, constants.constant, value

SQL
        );

        return new Results($res);
    }

    public function fetchTableProperty(): Results {
        $res = $this->sqlite->query(<<<'SQL'
SELECT cit.name AS class, 
       namespaces.namespace || lower(cit.name) AS fullnspath,
       visibility, 
       property, 
       value,
       cit.type AS type

    FROM cit
    JOIN properties 
        ON properties.citId = cit.id
    JOIN namespaces 
        ON cit.namespaceId = namespaces.id

SQL
        );

        return new Results($res);
    }

    public function getFilesResultsCounts(array $list): Results {
        $listSQL = makeList($list);

        $query = <<<SQL
SELECT file AS file, 
       line AS loc, 
       count(*) AS issues, 
       COUNT(DISTINCT analyzer) AS analyzers 
    FROM results
    WHERE line != -1 AND
          analyzer IN ($listSQL)
    GROUP BY file
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getAnalyzersResultsCounts(array $list): Results {
        $listSQL = makeList($list);

        $query = <<<SQL
SELECT analyzer, count(*) AS issues, COUNT(DISTINCT file) AS files, 
       severity AS severity 
    FROM results
    WHERE analyzer IN ($listSQL)
    GROUP BY analyzer
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getCountFileByAnalyzers(array $list): Results {
        $listSQL = makeList($list);

        $query = <<<SQL
SELECT count(*)  AS number
    FROM (SELECT DISTINCT file FROM results WHERE analyzer in ($listSQL))
SQL;
        $result = $this->sqlite->querySingle($query) ?? '';

        return new Results($result);
    }

    public function getFunctionsFromAnalyzer(string $analyzer): array {
        $query = <<<SQL
SELECT GROUP_CONCAT(DISTINCT REPLACE(SUBSTR(fullcode, 0, instr(fullcode, '(')), '@', ''))  AS functions FROM results 
    WHERE analyzer = "$analyzer";
SQL;
        $res = $this->sqlite->querySingle($query) ?? '';

        return explode(',', $res);
    }

    public function getCitBySize(string $type = 'class'): Results {
        $query = <<<SQL
SELECT namespaces.namespace || name AS name, 
       name AS shortName, 
       (cit.end - cit.begin + 1) AS size 
    FROM cit 
    JOIN namespaces 
        ON namespaces.id = cit.namespaceId
    WHERE
       cit.type = '$type'
    ORDER BY (cit.end - cit.begin) DESC
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getMethodsBySize(): Results {
        $query = <<<SQL
SELECT namespaces.namespace || CASE namespaces.namespace WHEN '\' THEN '' ELSE '\' END || name || '::' || method AS name, 
       method AS shortName, 
       files.file, 
       (methods.end - methods.begin + 1) AS size
    FROM methods 
    JOIN cit
        on methods.citId = cit.id AND
           cit.type = 'class'
    LEFT JOIN files 
        ON files.id = cit.file
    LEFT JOIN namespaces 
        ON namespaces.id = cit.namespaceId
    ORDER BY (methods.end - methods.begin) DESC
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getConcentratedIssues(array $list = array(), int $count = 5): Results {
        $sqlList = makeList($list);

        $query = <<<SQL
SELECT file, 
       line, 
       COUNT(*) AS count, 
       GROUP_CONCAT(DISTINCT analyzer) AS list 
    FROM results
    WHERE analyzer IN ($sqlList)
    GROUP BY file, line
    HAVING count(DISTINCT analyzer) > $count
    ORDER BY count(*) DESC
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getIdenticalFiles(): Results {
        $query = <<<'SQL'
SELECT GROUP_CONCAT(file) AS list, 
       count(*) AS count 
    FROM files 
    GROUP BY fnv132 
    HAVING COUNT(*) > 1 
    ORDER BY COUNT(*), file
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getCitTree(string $type = 'class'): Results {
        if ($type === 'trait') {
            // Missing when raw FQN is used
            $query = <<<'SQL'
    SELECT ns.namespace || cit.name AS child, 
           ttu.implements AS parent
        FROM cit 
        JOIN
          cit_implements AS ttu 
          ON ttu.implementing = cit.id AND
             ttu.type = 'use' 
        JOIN namespaces ns
            ON cit.namespaceId = ns.id
        WHERE cit.type="trait" AND
             ttu.implements + 0 = 0
             
UNION

    SELECT ns.namespace || cit.name AS child, 
           ns2.namespace || cit2.name AS parent 
        FROM cit 
        JOIN
          cit_implements AS ttu 
          ON ttu.implementing = cit.id AND
             ttu.type = 'use'
        JOIN cit cit2 
            ON ttu.implementing = cit.id
        JOIN namespaces ns
            ON cit.namespaceId = ns.id
        JOIN namespaces ns2
            ON cit2.namespaceId = ns2.id
        WHERE cit.type="trait" AND
              cit2.type="trait"
SQL;
        } else {
            $query = <<<SQL
SELECT ns.namespace || cit.name AS child, 
       ns2.namespace || cit2.name AS parent 
    FROM cit 
    LEFT JOIN cit cit2 
        ON cit.extends = cit2.id
    JOIN namespaces ns
        ON cit.namespaceId = ns.id
    JOIN namespaces ns2
        ON cit2.namespaceId = ns2.id
    WHERE cit.type="$type" AND
          cit2.type="$type"
SQL;
        }
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getTraitConflicts(): Results {
        $query = <<<'SQL'
   SELECT
   t1.name AS t1,
   m1.method AS method,
   t2.name AS t2
FROM
   cit AS t1 
   JOIN
      methods AS m1 
      ON m1.citId = t1.id 
   JOIN
      cit AS t2 
      ON m2.citId = t2.id 

   JOIN
      methods AS m2 
      ON m1.id != m2.id 
         AND LOWER(m1.method) == LOWER(m2.method)

WHERE
   t1.type = 'trait' 
   AND t2.type = 'trait'

SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getTraitUsage(): Results {
        $query = <<<'SQL'
SELECT
   t1.name AS t1,
   t2.name AS t2
FROM
   cit AS t1 
   JOIN
      cit_implements AS ttu 
      ON ttu.implementing = t1.id AND
         ttu.type = 'use'
   JOIN
      cit AS t2 
      ON ttu.implements = t2.id 
WHERE
   t1.type = 'trait' 
   AND t2.type = 'trait'
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getClassCounts(): Results {
        $query = <<<'SQL'
SELECT
   cit.name AS cit,
   cit.id AS id,
   cit.extends AS extends,
   ns.namespace AS namespace,
   count(distinct m.id) AS methods,
   count(distinct p.id) AS properties,
   count(distinct c.id) AS constants
   
FROM
   cit 
   LEFT JOIN
      cit_implements AS ttu 
      ON ttu.implementing = cit.id AND
         ttu.type = 'use'
   JOIN namespaces ns
        ON cit.namespaceId = ns.id
   LEFT JOIN
      methods AS m 
      ON cit.id = m.citId
   LEFT JOIN
      properties AS p 
      ON cit.id = p.citId
   LEFT JOIN
      classConstants AS c
      ON cit.id = c.citId
WHERE
   cit.type = 'class' 
GROUP BY cit.id

SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getFilesWithResults(array $list = array()): Results {
        $listSQL = makeList($list);

        $query = <<<SQL
SELECT
    DISTINCT file
FROM
   results 
WHERE analyzer IN ($listSQL)
SQL;
        $result = $this->sqlite->query($query);

        return new Results($result);
    }

    public function getExtendedDependencies(): Results {
        $query = <<<'SQL'
SELECT extending,  namespaces.namespace || cit.name AS origin
    FROM dependenciesExtensions
    LEFT JOIN cit
    LEFT JOIN namespaces 
        on cit.namespaceId = namespaces.id 
    WHERE LOWER(namespaces.namespace || cit.name ) = origin

SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }

    public function getClassesDependencies(): Results {
        $query = <<<'SQL'
SELECT *, count(*) AS count
    FROM classesDependencies
    GROUP BY including, included, type
    ORDER BY COUNT(*), including, included, type
SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }

    public function getClassInjectionCounts(): Results {
        $query = <<<'SQL'
SELECT *, COUNT(DISTINCT included) AS count
    FROM classesDependencies
    GROUP BY including
    ORDER BY COUNT(DISTINCT included) DESC
SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }

    public function getClassesDependenciesIncludingCount(): Results {
        $query = <<<'SQL'
SELECT *, COUNT(DISTINCT included) AS count
    FROM classesDependencies
    GROUP BY including
    ORDER BY COUNT(DISTINCT included) DESC
SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }

    public function getClassesDependenciesIncludedCount(): Results {
        $query = <<<'SQL'
SELECT included_name AS including_name, including AS including, COUNT(DISTINCT including) AS count
    FROM classesDependencies
    GROUP BY included
    ORDER BY COUNT(DISTINCT including) DESC
SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }


    public function getConstructorDependencies(): Results {
        $query = <<<'SQL'
SELECT cit.name AS destination, 
       lower(namespaces.namespace || cit.name) as destinationId,  
       typehints.name AS origin, 
       lower(typehints.fnp)  AS originId,
       rank,
       arguments.name
    FROM methods 
    JOIN cit 
        ON methods.citId = cit.id
    JOIN namespaces 
        ON cit.namespaceId = namespaces.id
    JOIN arguments 
        ON arguments.methodId = methods.id
    JOIN typehints
        ON arguments.id = typehints.object AND 
           typehints.type='argument'       AND
           typehints.fnp NOT IN ('\string', '\array', '\bool', '\int', '\stdclass', '\null')
    WHERE
        method='__construct';

SQL;
        $result = $this->sqlite->query($query);
        return new Results($result);
    }
}

?>