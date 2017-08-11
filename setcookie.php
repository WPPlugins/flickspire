<?php

//http://www.lifesecretsonline.com/blog/wp-content/plugins/flickspire/setcookie.php?custcode=lsblog

$custcode = strtolower($_GET["custcode"]);

setcookie($custcode."Subscriber", "True", time()+60*60*24*365, "/");
setcookie("IsSubscriber", "True", time()+60*60*24*365, "/");

?>
