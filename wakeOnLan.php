<!doctype html>
<html>
<head>
  ...
  <meta http-equiv="expires" content="0">
  <meta http-equiv="Pragma" content="no-cache">
  ...
  <title>Testseite für WOL über NAS</title>
</head>
<body text="#000000" bgcolor="#CCFFFF" link="#FF0000" vlink="#800080" alink="#0000FF">
  ... blendet einen Button ein mit "Start Server" Aufschrift.
  ... nach dem Klicken wird im aktuellen WEB-Serververzeichnis das Script "StarteServer.php" ausgeführt. 
  <form method="POST" action="StartServer.php" name="WOL_form.php">
     <input type="submit" name="submit" value="Start Server"></input>
  </form>
  ...
</body>
</html>