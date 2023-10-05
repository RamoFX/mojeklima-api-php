<?php



include_once "load.php";



// init graphql client
// login as system user (using graphql endpoint)
// call checkForNotifications mutation (using graphql endpoint)
// NOTE: You may need to change origin settings in .env file
// NOTE: As the notifications check comes from cron
//       you will need to implement logic that decide
//       whether notification should be sent or not
//       depending on update frequency set in the alert
