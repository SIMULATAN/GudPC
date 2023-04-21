<?php
include_once "../scaffolding/heading.php";

if (!isset($_SESSION["user"])) {
    echo '<script>window.location.href = "/GudPC/account/login.php";</script>';
}

$user = $_SESSION["user"];

if (isset($_POST["email"]) && isset($_POST["username"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    $result = createNewUser($email, $username, $password);
    if ($result instanceof User) {
        $_SESSION["user"] = $result;
        echo '<script>window.location.href = "/GudPC/";</script>';
    } else {
        $error = $result;
    }
}

?>


include_once "../scaffolding/footer.php";
