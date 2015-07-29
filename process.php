<?php
/**
 * Created by PhpStorm.
 * User: shane
 * Date: 15/07/26
 * Time: 21:14
 */
include("MCAuth.php");
include("config.php");

$Auth = new MCAuth();

if (isset($_POST)) {

    //get user and password from form
    $user = $_POST['user'] ? : null;
    $pass = $_POST['pass'] ? : null;

    //if we don't have anything to read, STOP!
    if ($pass == null || $user == null){
        echo("Client did not post username or password, please try again!");
        exit;
    }

    if ($Auth->authenticate($user, $pass)) {

        echo("<h1>Succession authenticating!</h1>><br>");
        $profile = $Auth->account;

        $profileIDRaw =  $profile['id'];
        $profileName = $profile['username'];

        //Thanks to https://github.com/Shadowwolf97/Minecraft-UUID-Utils/blob/master/MinecraftUUID.php#L145
        $profileID = "";
        $profileID .= substr($profileIDRaw, 0, 8)."-";
        $profileID .= substr($profileIDRaw, 8, 4)."-";
        $profileID .= substr($profileIDRaw, 12, 4)."-";
        $profileID .= substr($profileIDRaw, 16, 4)."-";
        $profileID .= substr($profileIDRaw, 20);

        $mysql = mysqli_connect($MySQLHost, $MySQLUser, $MySQLPass, $MySQLDb, $MySQLPort);

        $updateS = $mysql->prepare("INSERT INTO always_online (name,ip,uuid) VALUES(?,?,?) ON DUPLICATE KEY UPDATE ip = VALUES(ip), uuid = VALUES(uuid)");

        $updateS->bind_param("sss", $profileName, $_SERVER['REMOTE_ADDR'], $profileID);

        if ($updateS->execute()) {
            echo("Profile added/updated, feel free to join!");
        } else {

            echo("An error occured, errorno: " + $updateS->errno);
        }

        $mysql->close();

        //destroy the session, we don't required any more info in here!
        session_destroy();
    } else {
        echo "Account failed to authenticate, please verify your username/password";
    }

}
?>