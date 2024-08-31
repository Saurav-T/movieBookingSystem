<?php
session_start();
include('functions.php');
?>
<button type="button" id="button" onclick="window.location.href='add.php?tablename=users';">Add User</button>
<?php
generatetable('users');
?>