# PHP MYSQL DATABASE HANDLER
A PHP file for handle database

**Overview**
- Generate SQL
- View table data from database
- Insert data into table
- Update table
- Delete data from Table
- Trigger
- Index management

Download
---------
Clone a copy of the main repo
```bash
https://github.com/zxalif/php-mysql-handler.git
```

Usages
-------
###### Setup

> Set host, username, password, and database name
- conn/conn.php
```bash
$host = 'localhost';
$user = 'root';
$pwd = '';
$db = 'cms';
```

###### Use
**Basic SQL Generate**
----------------------
View data from table
----------------------
```bash
include('php-database-handler/index.php');
$cms = new SQLCreate();
$sql = $cms->generate('table_name', null, $type='view');
echo $sql;
> SELECT * FROM table_name
```

View data from table with limit
-------------------------------
```bash
include('php-database-handler/index.php');
$cms = new SQLCreate();
$sql = $cms->generate('table_name', null, $type='view', array('limit'=>10));
echo $sql;
> SELECT * FROM table_name LIMIT 10
```

Using limit for for spliting rows
---------------------------------
```bash
include('php-database-handler/index.php');
$cms = new SQLCreate();
$sql = $cms->generate('table_name', null, $type='view', array('limit'=>array(10, 20)));
echo $sql;
> SELECT * FROM table_name LIMIT 10, 20
```

Insert data into table
-----------------------
```bash
include('php-database-handler/index.php');
$cms = new SQLCreate();
$sql = $cms->generate('table_name', array('id'=>'1', 'name'=>'zxalif'), $type='insert');
echo $sql;
> INSERT INTO table_name(id, name) VALUES("1", "zxalif")
```
:+1: