<?php
//this line makes PHP behave in a more strict way
declare(strict_types=1);

//we are going to use session variables, so we need to enable sessions
session_start();

function whatIsHappening()
{
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

if (!isset($_SESSION["totalValue"])) {
    $_SESSION["totalValue"] = 0;
}

// create an address template

function address_display ($street, $street_nr, $zip, $city) {
    return "$street $street_nr<br>$zip $city";
}

// display cart items

function cart_display ($cart) {
    foreach($cart as $item) {
    return implode(": ", $item) . "<br>" ;
    }
}

// define owner's mail
define("owner_mail" , "info@restaurant.com");

//display email message

function email_display($email, $street, $street_nr, $zip, $city, $cart, $sent) {
    $disp_address = address_display($street, $street_nr, $zip, $city);
    $disp_cart = cart_display($cart);
    $disp_price = cart_price($cart);
    $disp_sent =
    $message = "Dear customer, \n
        Your information is: \n
        {$email}
        Your address is: \n
        You ordered: \n
        {$disp_cart}\n
        Your total is: \n
        {$disp_price}\n
        {$sent}";

    return $message;


//    // To send HTML mail, the Content-type header must be set (from php.net)
//    $headers[] = 'MIME-Version: 1.0';
//    $headers[] = 'Content-type: text/html; charset="UTF-8"';
//
//    // Additional headers
//    $headers[] = "To:";
//    $headers[] = "From:";

}

// to actually mail (from php.net, not used here):
//mail($to, $subject, $message, implode("\r\n", $headers));

// calculate cart price

function cart_price ($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item["price"];
    }
    if (isset($_POST["express_delivery"])) {
        $total += floatval($_POST["express_delivery"]);
    }
    return $total;
}


//switch between food and drinks menu

(isset($_GET["food"]) && $_GET["food"] === "0") ? $products = $products_drinks : $products = $products_food;

$_SESSION["cart"] = [];

//alerts

function formAlert($alert)
{
    echo "<div class='alert alert-primary' role='alert'>$alert</div>";
}

function orderAlert($alert)
{
    echo "<div class=\"alert alert-warning\" role=\"alert\">$alert</div>";
}

function sentAlert($alert)
{
    echo "<div class=\"alert alert-success\" role=\"alert\">$alert</div>";
}


//input validation

function test_input($data)
{
    $data = trim($data);
    $data = htmlspecialchars($data);
    return $data;
}

$email = $street = $street_nr = $city = $zipcode = $sent = "";
$email_error = $street_error = $street_nr_error = $city_error = $zipcode_error = $order_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //email
    if (empty($_POST["email"])) {
        $email_error = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        $_SESSION["email"] = $email;
        // check if e-mail address is well-formed
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "";
        } else {
            $email_error = "Invalid email format";
        }
    }
    // street
    if (empty($_POST["street"])) {
        $street_error = "Street name is required";
    } else {
        $street = test_input($_POST["street"]);
        $_SESSION["street"] = ucfirst($street);
        // check if name only contains letters and whitespace
        if (preg_match("/^[a-zA-Z-' ]*$/", $street) && strlen($street) >= 2) {
            $street_error = "";
        } else {
            $street_error = "Only letters and white space allowed";
        }
    }
    // street nr
    if (empty($_POST["streetnumber"])) {
        $_SESSION["street_nr_error"] = "Street number is required.";
    } else {
        $street_nr = test_input($_POST["streetnumber"]);
        $_SESSION["street_nr"] = $street_nr;
        // check if name only digits
        if (preg_match("/^\d*$/", $street_nr)) {
            $street_nr_error = "";
        } else {
            $street_nr_error = "Only numbers allowed";
        }
    }

    // city
    if (empty($_POST["city"])) {
        $city_error = "City name is required";
    } else {
        $city = test_input($_POST["city"]);
        $_SESSION["city"] = ucfirst($city);
        // check if city name only contains letters and whitespace
        if (preg_match("/^[a-zA-Z-' ]*$/", $city) && strlen($city) >= 2) {
            $city_error = "";
        } else {
            $city_error = "Only letters and white space allowed";
        }
    }

    // zipcode
    if (empty($_POST["zipcode"])) {
        $zipcode_error = "Zipcode is required.";
    } else {
        $zipcode = test_input($_POST["zipcode"]);
        $_SESSION["zipcode"] = $zipcode;
        // check if zipcode contains only digits, length between 4 and 12 digits
        if (preg_match("/^\d{4,12}$/", $zipcode)) {
            $zipcode_error = "";
        } else {
            $zipcode_error = "At least 4 numbers expected";
        }
    }

    //order can be sent if all required input fields are filled and at least one item has been picked.
    $order_valid = false;
    if ($email_error === "" && $street_error === "" && $street_nr_error === "" && $city_error === "" && $zipcode_error === "") {
        if (isset($_POST["products"])) {
            $order_valid = true;
            $order_error = "";
        } else {
            $order_error = "No products selected. Please select at least one product before ordering.";
            $_POST["sent"] = "";
        }
    }

    if ($order_valid) {
        // add up prices
        foreach ($_POST["products"] as $product => $amount) {
            if (isset($product)) {
                $_SESSION["cart"][] = $products[$product];
                $total = cart_price($_SESSION["cart"]);
                $total_all = floatval($_SESSION["totalValue"]) + $total;
                $_SESSION["totalValue"] = $total_all;
            }
        }

       // cookie, timed for 1 day
        if (isset($_COOKIE["totalValue"])) {
            $_SESSION["totalValue"] = floatval($_COOKIE["totalValue"]) + $_SESSION["totalValue"];
        }
        //setcookie("totalValue", strval($_SESSION["totalValue"]), time() + (86400 * 30));

        //displaying order sent message
        if (isset($_POST["express_delivery"])) {
            $_POST["sent"] = "Your order has been sent. Delivery time is 45 minutes.";
        } else {
            $_POST["sent"] = "Your order has been sent. Delivery time is 2 hours.";
        }

        // display email to customer on page:


    } else {
        $_POST["sent"] = "";
    }
}

whatIsHappening();

//$session_errors = array($_SESSION["zipcode_error"], $_SESSION["city_error"], $_SESSION["street_error"], $_SESSION["email_error"]);
//print_r($session_errors);

require 'form-view.php';
// ($email, $street, $street_nr, $zip, $city, $order, $totalValue, $sent)
//formAlert(mailMessage($_SESSION["email"], $_SESSION["street"], $_SESSION["street_nr"], $_SESSION["zipcode"], $_SESSION["city"], "", $_SESSION["totalValue"], $sent));
//cart_display($_SESSION["cart"]);
//cart_price($_SESSION["cart"]);
//formAlert(address_display($_SESSION["street"], $_SESSION["street_nr"], $_SESSION["zipcode"], $_SESSION["city"]));

formAlert(email_display($_SESSION["email"], $_SESSION["street"], $_SESSION["street_nr"], $_SESSION["zipcode"], $_SESSION["city"], $_SESSION["city"], $_POST["sent"]));
