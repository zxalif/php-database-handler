# php-database-handler
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
git clone https://github.com/zxalif/php-database-handler.git
```

Usages
-------
###### Setup

> Set host, username, password, and database name
```bash
conn/conn.php
$host = 'localhost';
$user = 'root';
$pwd = '';
$db = 'cms';
```

###### Use
> Basic SQL Generate
```bash
include('php-database-handler/index.php');
$cms = new SQLCreate()
$sql = $cms->generate('table_name', array(), $type='view');
echo $sql;
> SELECT * FROM table_name;
$sql = $cms->generate('table_name', array('id'=>'1', 'name'=>'zxalif'), $type='insert');
echo $sql;
> INSERT INTO table_name(id, name) VALUES("1", "zxalif");
```