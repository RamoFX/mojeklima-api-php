<?php



require_once 'bootstrap.php';



switch ($_GET["module"]) {
  case "graphql":
    include_once "GraphQL/index.php";
    break;

  case "cron":
    include_once "Cron/index.php";
    break;

  default:
    echo 'URL query parameter "module" accepts one of those values: "graphql", "cron". Got: "' . $_GET["module"] . '".';
    exit();
}
