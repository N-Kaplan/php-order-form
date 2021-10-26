<?php
//this line makes PHP behave in a more strict way
declare(strict_types=1);

//we are going to use session variables, so we need to enable sessions
session_start();

//cookie, timed for 1 day
if (!isset($_COOKIE["allOrders"])) {
    setcookie("user", "name", time() + (86400 * 30));
    $_COOKIE["allOrders"] = 0;
}


function whatIsHappening() {
    echo '<h2>$_GET</h2>';
    var_dump($_GET);
    echo '<h2>$_POST</h2>';
    var_dump($_POST);
    echo '<h2>$_COOKIE</h2>';
    var_dump($_COOKIE);
    echo '<h2>$_SESSION</h2>';
    var_dump($_SESSION);
}

//your products with their price.
$products_food = [
    ['name' => 'Club Ham', 'price' => 3.20],
    ['name' => 'Club Cheese', 'price' => 3],
    ['name' => 'Club Cheese & Ham', 'price' => 4],
    ['name' => 'Club Chicken', 'price' => 4],
    ['name' => 'Club Salmon', 'price' => 5]
];

$products_drinks = [
    ['name' => 'Cola', 'price' => 2],
    ['name' => 'Fanta', 'price' => 2],
    ['name' => 'Sprite', 'price' => 2],
    ['name' => 'Ice-tea', 'price' => 3],
];

$_SESSION["totalValue"] = 0;

var_dump(isset($_COOKIE["allOrders"]));
var_dump(($_COOKIE["expires"]));

//switch between food and drinks menu

(isset($_GET["food"]) && $_GET["food"] === "0") ? $products = $products_drinks : $products = $products_food;

// add up prices

if (isset($_POST["products"])) {
    foreach ($_POST["products"] as $index => $prod) {
        if (isset($prod)) {
            $_SESSION["totalValue"] += $products[$index]['price'];
        }
    };
    if (isset($_POST["express_delivery"])) {
        $_SESSION["totalValue"] += floatval($_POST["express_delivery"]);

    }
    $_COOKIE["allOrders"] += $_SESSION["totalValue"];
}

//alert

function formAlert($alert) {
    echo "<div class='alert alert-primary' role='alert'>$alert</div>";
}

function sentAlert($alert) {
    echo "<div class=\"alert alert-success\" role=\"alert\">$alert</div>";
}


//input validation

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$email = $street = $street_nr = $city = $zipcode = $sent = "";
$email_error = $street_error = $street_nr_error = $city_error = $zipcode_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //email
    if (empty($_POST["email"])) {
        $_SESSION["email_error"] = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        $_SESSION["email"] =  $email;
        // check if e-mail address is well-formed
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["email_error"] = "";
        } else {
            $email_error = "Invalid email format";
            $_SESSION["email_error"] =  $email_error;
        }
    }
    // street
    if (empty($_POST["street"])) {
        $_SESSION["street_error"] = "Street name is required";
    } else {
        $street = test_input($_POST["street"]);
        $_SESSION["street"] =  ucfirst($street);
        // check if name only contains letters and whitespace
        if (preg_match("/^[a-zA-Z-' ]*$/",$street) && strlen($street) >= 2) {
            $_SESSION["street_error"] = "";
        } else {
            $_SESSION["street_error"] = "Only letters and white space allowed";
        }
    }
    // street nr
    if (empty($_POST["streetnumber"])) {
        $_SESSION["street_nr_error"] = "Street number is required.";
    } else {
        $street_nr = test_input($_POST["streetnumber"]);
        $_SESSION["street_nr"] =  $street_nr;
        // check if name only digits
        if (preg_match("/^\d*$/",$street_nr)) {
            $_SESSION["street_nr_error"] = "";
        } else {
            $_SESSION["street_nr_error"] = "Only numbers allowed";
        }
    }

    // city
    if (empty($_POST["city"])) {
        $_SESSION["city_error"] = "City name is required";
    } else {
        $city = test_input($_POST["city"]);
        $_SESSION["city"] = ucfirst($city);
        // check if city name only contains letters and whitespace
        if (preg_match("/^[a-zA-Z-' ]*$/",$city) && strlen($city) >= 2) {
            $_SESSION["city_error"] = "";
        } else {
            $_SESSION["city_error"] = "Only letters and white space allowed";
        }
    }

    // zipcode
    if (empty($_POST["zipcode"])) {
        $_SESSION["zipcode_error"] = "Zipcode is required.";
    } else {
        $zipcode = test_input($_POST["zipcode"]);
        $_SESSION["zipcode"] = $zipcode;
        // check if zipcode contains only digits, length between 4 and 12 digits
        if (preg_match("/^\d{4,12}$/",$zipcode)) {
            $_SESSION["zipcode_error"] = "";
        } else {
            $_SESSION["zipcode_error"] = "At least 4 numbers expected";
        }
    }
    //displaying order sent message
    if ($_SESSION["email_error"] === "" && $_SESSION["street_error"] === "" && $_SESSION["street_nr_error"] === "" && $_SESSION["city_error"] === "" && $_SESSION["zipcode_error"] === "") {
        if (isset($_POST["express_delivery"])){
            $_POST["sent"] = "Your order has been sent. Delivery time is 45 minutes.";
        } else {
            $_POST["sent"] = "Your order has been sent. Delivery time is 2 hours.";
        }
    } else {
        $_POST["sent"] = "";
    }
}


whatIsHappening();

//$session_errors = array($_SESSION["zipcode_error"], $_SESSION["city_error"], $_SESSION["street_error"], $_SESSION["email_error"]);
//print_r($session_errors);

require 'form-view.php';
