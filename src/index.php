<?php



require_once 'bootstrap.php';



switch ($_GET["module"]) {
  case "graphql":
    include_once "graphql.php";
    break;

  case "cron":
    //    include_once "cron.php"; // TODO: Maybe this should be handled internally instead of external signal source?
    break;

  default:
    echo 'URL query parameter "module" accepts one of those values: "graphql", "cron". Got: "' . $_GET["module"] . '".';
    exit();
}
