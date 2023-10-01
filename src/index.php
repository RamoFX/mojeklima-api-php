<?php



if (isset($_GET["module"])) {
  switch ($_GET["module"]) {
    case "graphql":
      include_once "graphql.php";
      break;

    case "cron":
      include_once "cron.php";
      break;

    default:
      echo 'URL parameter "module" accepts "graphql" or "cron" value, provided "' . $_GET["module"] . '"';
      exit();
  }
} else {
  echo 'URL parameter named "module" should be set';
}
