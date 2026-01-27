<?php
setcookie(session_name(), '', time() - 3600, '/');
session_destroy();
header("Location: area-login.html");
exit;
?>